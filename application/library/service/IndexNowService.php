<?php

namespace service;

use ContentsModel;

class IndexNowService
{
    protected $indexnowHost;
    protected $indexnowKey;
    protected $indexnowKeyLocation;
    protected $redisKey = 'indexnow:urlset';
    protected $dailyOffsetKey = 'indexnow:daily_offset';
    protected $dailyDateKey = 'indexnow:daily_date';

    public function __construct()
    {
        $this->indexnowKey = (string) setting('index_now_key');
        $this->indexnowHost = (string) setting('index_now_host');
        
        if ($this->indexnowHost && $this->indexnowKey) {
            $host = parse_url(options('siteUrl'), PHP_URL_HOST) ?: 'localhost';
            $this->indexnowKeyLocation = "https://{$host}/{$this->indexnowKey}.txt";
        } else {
            $this->indexnowKeyLocation = '';
        }
    }

    /**
     * 每小时提交任务：优先提交新链接，然后提交现有文章
     */
    public function hourlySubmit(int $newLinksBatchSize = 5000, int $articlesLimit = 1000): array
    {
        $results = [
            'new_links' => null,
            'articles' => null,
        ];

        // 1. 优先提交当天的新链接（从Redis队列）
        $results['new_links'] = $this->flushNewLinksFromQueue($newLinksBatchSize);
        
        // 2. 提交现有文章
        $results['articles'] = $this->submitExistingArticles($articlesLimit);

        return $results;
    }

    /**
     * 从Redis队列提交新链接
     */
    public function flushNewLinksFromQueue(int $batchSize = 5000): array
    {
        $redis = redis();
        $urls = [];
        
        // 从Redis队列取出URL
        for ($i = 0; $i < $batchSize; $i++) {
            $u = $redis->sPop($this->redisKey);
            if (!$u) break;
            $urls[] = $u;
        }
        
        if (empty($urls)) {
            return ['ok' => true, 'count' => 0, 'http' => 0];
        }
        
        // 提交到IndexNow
        $result = $this->pushUrlsToIndexNow($urls);
        
        if (!$result['ok']) {
            // 失败时回退到队列
            foreach ($urls as $u) {
                $redis->sAdd($this->redisKey, $u);
            }
        }
        
        return $result;
    }

    /**
     * 提交现有文章到IndexNow
     */
    public function submitExistingArticles(int $limit = 1000): array
    {
        $redis = redis();
        
        // 检查是否需要重置offset（新的一天）
        $today = date('Y-m-d');
        $lastDate = $redis->get($this->dailyDateKey);
        
        if ($lastDate !== $today) {
            // 新的一天，重置offset
            $redis->set($this->dailyDateKey, $today, 86400 * 2); // 2天过期
            $redis->set($this->dailyOffsetKey, 0);
            trigger_log("新的一天，重置IndexNow offset");
        }
        
        // 获取当前offset
        $offset = (int) $redis->get($this->dailyOffsetKey);
        trigger_log("开始提交现有文章，当前offset: {$offset}, 目标limit: {$limit}");
        
        // 查询文章（使用 queryWebPost 确保只查询 web 端显示的文章）
        $articles = ContentsModel::queryWebPost()
            ->select(['cid', 'slug', 'type'])
            ->orderBy('cid', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        $articleCount = $articles->count();
        if ($articleCount > 0) {
            $firstCid = $articles->first()->cid;
            $lastCid = $articles->last()->cid;
            trigger_log("查询到文章数量: {$articleCount} 条 (offset: {$offset}, limit: {$limit}), CID范围: {$firstCid} - {$lastCid}");
            
            // 记录所有文章的CID列表
            $cids = $articles->pluck('cid')->toArray();
            trigger_log("文章CID列表: " . implode(', ', $cids));
            
            // 记录前10条和后10条的详细信息（包含slug和type）
            $details = [];
            foreach ($articles->take(10) as $article) {
                $details[] = "CID:{$article->cid}, slug:{$article->slug}, type:{$article->type}";
            }
            if ($articleCount > 10) {
                $details[] = "... (省略 " . ($articleCount - 20) . " 条) ...";
                foreach ($articles->skip($articleCount - 10) as $article) {
                    $details[] = "CID:{$article->cid}, slug:{$article->slug}, type:{$article->type}";
                }
            }
            trigger_log("文章详情 (前10条和后10条):\n" . implode("\n", $details));
        } else {
            trigger_log("查询到文章数量: 0 条 (offset: {$offset}, limit: {$limit})");
        }
        
        if ($articles->isEmpty()) {
            // 没有更多文章，重置offset继续循环
            $redis->set($this->dailyOffsetKey, 0);
            trigger_log("已到达文章末尾，重置offset继续循环");
            return ['ok' => true, 'count' => 0, 'message' => '已到达末尾，重置offset'];
        }
        
        // 生成URL列表
        $urls = [];
        $siteUrl = rtrim(options('siteUrl'), '/');
        
        foreach ($articles as $article) {
            $url = $siteUrl . '/archives/' . $article->cid . '/';
            $urls[] = $url;
        }

        // 提交到IndexNow
        $result = $this->pushUrlsToIndexNow($urls);
        
        if ($result['ok']) {
            // 更新offset
            $newOffset = $offset + count($urls);
            $redis->set($this->dailyOffsetKey, $newOffset);
            trigger_log("✅ 成功提交 " . count($urls) . " 条文章，offset已更新: {$offset} -> {$newOffset}，下次将从offset {$newOffset} 开始");
        } else {
            trigger_log("❌ 提交失败: " . ($result['error'] ?? 'unknown error') . "，offset保持为 {$offset}，下次将重试相同的文章");
        }
        
        return [
            'ok' => $result['ok'],
            'count' => count($urls),
            'offset' => $offset,
            'new_offset' => $result['ok'] ? ($offset + count($urls)) : $offset,
            'http' => $result['http'] ?? 0,
            'error' => $result['error'] ?? null
        ];
    }

    /**
     * 推送URL列表到IndexNow
     */
    public function pushUrlsToIndexNow(array $urls): array
    {
        if (empty($urls)) {
            return ['ok' => true, 'count' => 0];
        }
        
        if (!$this->indexnowKey || !$this->indexnowHost) {
            return ['ok' => false, 'error' => 'missing indexnow host/key'];
        }
        
        // 从文件读取key
        $keyPath = APP_PATH . '/public/www/' . $this->indexnowKey . '.txt';
        if (!is_file($keyPath)) {
            return ['ok' => false, 'error' => 'IndexNow key file not found'];
        }
        $key = trim(file_get_contents($keyPath));
        
        $payload = json_encode([
            'host' => $this->indexnowHost,
            'key' => $key,
            'keyLocation' => $this->indexnowKeyLocation,
            'urlList' => array_values(array_unique($urls)),
        ], JSON_UNESCAPED_SLASHES);

        $ch = curl_init('https://api.indexnow.org/indexnow');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 20,
        ]);
        
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($http >= 200 && $http < 300) {
            return ['ok' => true, 'count' => count($urls), 'http' => $http, 'resp' => $resp];
        }
        
        error_log("IndexNow push failed http={$http} err={$err} resp={$resp}");
        return ['ok' => false, 'http' => $http, 'error' => $err ?: "HTTP {$http}", 'resp' => $resp];
    }

    /**
     * 获取当前offset信息（用于调试）
     */
    public function getOffsetInfo(): array
    {
        $redis = redis();
        return [
            'offset' => (int) $redis->get($this->dailyOffsetKey),
            'date' => $redis->get($this->dailyDateKey),
            'queue_count' => $redis->sCard($this->redisKey),
        ];
    }
}

