<?php

namespace commands;

class IndexNowCommand
{
    public $signature = 'indexnow:push';
    public $description = '推送indexnow';

    protected $indexnowHost;
    protected $indexnowKey;
    protected $indexnowKeyLocation;
    protected $indexnowRedisKey = 'indexnow:urlset';
    protected $indexnowBatchSize = 5000;

    public function handle($argv)
    {
        $batchArg = 0;
        if (is_string($argv) && strlen($argv)) {
            if (preg_match('/batch=(\d+)/', $argv, $m)) {
                $batchArg = (int)$m[1];
            } elseif (is_numeric($argv)) {
                $batchArg = (int)$argv;
            }
        }

        $this->initConfig();

        $batch = $batchArg ?: $this->indexnowBatchSize;
        try {
            $ret = $this->indexnowFlushBatch($batch);
            echo json_encode($ret, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }catch (\Throwable $exception ){
            echo $exception->getMessage();
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

    protected function indexnowFlushBatch(int $batchSize = null): array
    {
        $batchSize = $batchSize ?: $this->indexnowBatchSize;

        if (!$this->indexnowKey || !$this->indexnowHost) {
            return ['ok' => false, 'http' => 0, 'err' => 'missing indexnow host/key'];
        }

        $urls = [];
        for ($i = 0; $i < $batchSize; $i++) {
            $u = redis()->sPop($this->indexnowRedisKey);
            if (!$u) break;
            $nu = parse_url($u);
            $u = rtrim(options('siteUrl'),'/'). '/'. ltrim($nu['path'],'/');
            $urls[] = $u;
        }
        if (empty($urls)) {
            return ['ok' => true, 'count' => 0, 'http' => 0,'err' => date('Y-m-d H:i:s').'本次未执行数据'];
        }
        trigger_log(date('Y-m-d H:i:s')."indexnow URLs:".var_export($urls,true));

        $payload = json_encode([
            'host'        => $this->indexnowHost,
            'key'         => $this->loadKey(),
            'keyLocation' => $this->indexnowKeyLocation,
            'urlList'     => array_values(array_unique($urls)),
        ], JSON_UNESCAPED_SLASHES);
        trigger_log(date('Y-m-d H:i:s')."indexnow payload:".var_export($payload,true));

        $ch = curl_init('https://api.indexnow.org/indexnow');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($http >= 200 && $http < 300) {
            return ['ok' => true, 'count' => count($urls), 'http' => $http, 'resp' => date('Y-m-d H:i:s'). $resp];
        }

        foreach ($urls as $u) {
            redis()->sAdd($this->indexnowRedisKey, $u);
        }
        error_log(date('Y-m-d H:i:s')."IndexNow push failed http=$http err=$err resp=$resp");
        return ['ok' => false, 'http' => $http, 'err' => $err];
    }
}