<?php

namespace commands;

class IndexNowFlushAllCommand
{
    public $signature = 'indexnow:flush-all';
    public $description = '一次性推送数据库中所有发布状态的文章URL';

    protected $indexnowHost;
    protected $indexnowKey;
    protected $indexnowKeyLocation;
    protected $maxBatchSize = 10000; // IndexNow API 最大支持10000个URL
    protected $delayBetweenBatches = 1; // 批次间延迟（秒）
    protected $dbChunkSize = 1000; // 数据库分批查询大小

    public function handle($argv)
    {
        if (!empty($argv)){
            $chunkSize = (int)$argv;
            if ($chunkSize > 0 ){
                $this->dbChunkSize = $chunkSize;
            }
        }
        $this->initConfig();

        try {
            $totalCount = $this->getTotalPublishedCount();
            echo "数据库中共有 {$totalCount} 篇已发布文章" . PHP_EOL;

            if ($totalCount === 0) {
                echo json_encode(['ok' => true, 'message' => '没有已发布的文章'], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                return;
            }

            $results = $this->processAllArticlesInChunks();
            echo json_encode($results, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        } catch (\Throwable $exception) {
            echo json_encode([
                'ok' => false,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    }

    protected function initConfig(): void
    {
        $this->indexnowKey = (string) setting('index_now_key');
        $this->indexnowHost = (string) setting('index_now_host');

        if ($this->indexnowHost && $this->indexnowKey) {
            $this->indexnowKeyLocation = 'https://' . $this->indexnowHost . '/' . $this->indexnowKey . '.txt';
        } else {
            $this->indexnowKeyLocation = '';
        }
    }

    /** 从根目录的 key 文件读取 key */
    private function loadKey()
    {
        $path = APP_PATH . 'public/www/' . $this->indexnowKey . '.txt';

        if (!is_file($path)) {
            throw new \RuntimeException("IndexNow key file not found at: {$path}");
        }

        return trim(file_get_contents($path));
    }

    /** 获取已发布文章总数 */
    protected function getTotalPublishedCount(): int
    {
        return \ContentsModel::where('status', \ContentsModel::STATUS_PUBLISH)
            ->where('type', \ContentsModel::TYPE_POST)
            ->count();
    }

    /** 分批查询已发布的文章 */
    protected function getPublishedArticlesChunk(int $offset, int $limit): array
    {
        return \ContentsModel::where('status', \ContentsModel::STATUS_PUBLISH)
            ->where('type', \ContentsModel::TYPE_POST)
            ->select(['cid', 'slug', 'type'])
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /** 分批处理所有文章 */
    protected function processAllArticlesInChunks(): array
    {
        if (!$this->indexnowKey || !$this->indexnowHost) {
            return ['ok' => false, 'error' => 'missing indexnow host/key'];
        }

        $totalCount = $this->getTotalPublishedCount();
        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $batchResults = [];
        $batchNumber = 1;
        $offset = 0;

        while ($offset < $totalCount) {
            echo "查询第 " . ceil(($offset + 1) / $this->dbChunkSize) . " 批数据库记录..." . PHP_EOL;
            
            // 从数据库分批查询文章
            $articles = $this->getPublishedArticlesChunk($offset, $this->dbChunkSize);
            
            if (empty($articles)) {
                break;
            }

            echo "获取到 " . count($articles) . " 篇文章，开始处理..." . PHP_EOL;

            // 将文章按API限制分批处理
            $articleChunks = array_chunk($articles, $this->maxBatchSize);
            
            foreach ($articleChunks as $chunk) {
                echo "处理第 {$batchNumber} 批次，共 " . count($chunk) . " 篇文章..." . PHP_EOL;
                
                $urls = [];
                foreach ($chunk as $article) {
                    $urls[] = $this->generateArticleUrl($article);
                }
                
                $batchResult = $this->pushUrlBatch($urls);
                
                $totalProcessed += count($urls);
                
                if ($batchResult['ok']) {
                    $totalSuccess += count($urls);
                    echo "第 {$batchNumber} 批次成功推送 " . count($urls) . " 个URL" . PHP_EOL;
                } else {
                    $totalFailed += count($urls);
                    echo "第 {$batchNumber} 批次推送失败: {$batchResult['error']}" . PHP_EOL;
                }

                $batchResults[] = [
                    'batch' => $batchNumber,
                    'count' => count($urls),
                    'success' => $batchResult['ok'],
                    'http_code' => $batchResult['http'] ?? 0,
                    'error' => $batchResult['error'] ?? null
                ];

                $batchNumber++;

                // 批次间延迟，避免API限制
                if ($this->delayBetweenBatches > 0) {
                    echo "等待 {$this->delayBetweenBatches} 秒后处理下一批次..." . PHP_EOL;
                    sleep($this->delayBetweenBatches);
                }
            }

            $offset += $this->dbChunkSize;
            
            // 显示总体进度
            $progress = min(100, round(($offset / $totalCount) * 100, 2));
            echo "总体进度: {$progress}% ({$offset}/{$totalCount})" . PHP_EOL;
        }

        return [
            'ok' => $totalFailed === 0,
            'summary' => [
                'total_articles' => $totalCount,
                'total_processed' => $totalProcessed,
                'total_success' => $totalSuccess,
                'total_failed' => $totalFailed,
                'total_batches' => $batchNumber - 1
            ],
            'batch_details' => $batchResults
        ];
    }

    /** 推送URL批次到IndexNow */
    protected function pushUrlBatch(array $urls): array
    {
        if (empty($urls)) {
            return ['ok' => true, 'count' => 0];
        }

        trigger_log(date('Y-m-d H:i:s') . " indexnow batch URLs: " . var_export($urls, true));

        $payload = json_encode([
            'host'        => $this->indexnowHost,
            'key'         => $this->loadKey(),
            'keyLocation' => $this->indexnowKeyLocation,
            'urlList'     => array_values(array_unique($urls)),
        ], JSON_UNESCAPED_SLASHES);

        trigger_log(date('Y-m-d H:i:s') . " indexnow batch payload: " . var_export($payload, true));

        $ch = curl_init('https://api.indexnow.org/indexnow');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30, // 增加超时时间
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($http >= 200 && $http < 300) {
            return [
                'ok' => true,
                'count' => count($urls),
                'http' => $http,
                'resp' => $resp
            ];
        }

        $errorMsg = "IndexNow push failed http={$http} err={$err} resp={$resp}";
        error_log($errorMsg);

        return [
            'ok' => false,
            'count' => count($urls),
            'http' => $http,
            'error' => $err ?: "HTTP {$http}",
            'resp' => $resp
        ];
    }

    /** 生成文章URL */
    protected function generateArticleUrl(array $article): string
    {
        $siteUrl = rtrim(options('siteUrl'), '/');
        
        if ($article['type'] === 'page') {
            return $siteUrl . '/' . $article['slug'] . '/';
        } else {
            return $siteUrl . '/archives/' . $article['cid'] . '/';
        }
    }
}