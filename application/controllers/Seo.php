<?php

use Tbold\Utils\Sitemap;
use Tbold\WebsiteController;

class SeoController extends WebsiteController
{
    protected $host;
    protected $scheme;
    protected $indexnowHost;
    protected $indexnowKey;
    protected $indexnowKeyLocation;
    protected $indexnowRedisKey = 'indexnow:urlset';
    protected $indexnowBatchSize = 5000;
    protected $indexnowPushSecret;

    protected function init()
    {
        // 优先使用后台配置的网站地址
        $this->host = parse_url(options('siteUrl'), PHP_URL_HOST) ?: $this->getRequest()->getServer('HTTP_HOST');
        // 写死使用https
        $this->scheme = 'https';
        $this->indexnowKey = (string) setting('index_now_key');
        $this->indexnowHost = $this->host;
        $this->indexnowKeyLocation = "https://{$this->host}/{$this->indexnowKey}.txt";
        $this->indexnowPushSecret = (string) setting('index_now_secret', '');
    }

    public function robotsAction()
    {
        $response = $this->getResponse();
        $response->setHeader("Content-Type", "text/plain");
        $robots = str_render((string)options('robots'), ['host' => $this->host]);
        $response->setBody($robots);
        return false;
    }

    public function sitemap_indexAction()
    {
        $totalPosts = ContentsModel::queryWebPost()->count();
        $totalPages = max(1, ceil($totalPosts / 5000));
        $baseUrl = "{$this->scheme}://{$this->host}/sitemap";
        
        $items = [
            ['loc' => "{$baseUrl}/sitemap-home.xml", 'lastmod' => date('Y-m-d')],
            ['loc' => "{$baseUrl}/sitemap-category.xml", 'lastmod' => date('Y-m-d')],
        ];
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $items[] = ['loc' => "{$baseUrl}/sitemap-archives-{$i}.xml", 'lastmod' => date('Y-m-d')];
        }
        
        return $this->renderSitemap(Sitemap::newIndex(), $items);
    }

    public function sitemap_homeAction()
    {
        $items = [[
            'loc' => "{$this->scheme}://{$this->host}/",
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ]];
        
        return $this->renderSitemap(Sitemap::newUrl(), $items);
    }

    public function sitemap_categoryAction()
    {
        $items = [];
        
        // 添加固定独立页面
        $staticPages = [
            ['url' => '/contribute.html', 'name' => '求瓜投稿'],
            ['url' => '/ybml.html', 'name' => '回家的路'],
            ['url' => '/archives.html', 'name' => '往期内容'],
            ['url' => '/tags.html', 'name' => '所有标签'],
        ];
        
        foreach ($staticPages as $page) {
            $items[] = [
                'loc' => "{$this->scheme}://{$this->host}{$page['url']}",
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }
        
        // 查询"吃瓜分享赏金令"独立页面，找不到则使用备用链接
        $rewardPage = ContentsModel::query()
            ->where('title', '吃瓜分享赏金令')
            ->where('type', ContentsModel::TYPE_PAGE)
            ->where('status', 'publish')
            ->first();
        
        if ($rewardPage && ($rewardUrl = $rewardPage->url())) {
            $items[] = [
                'loc' => "{$this->scheme}://{$this->host}{$rewardUrl}",
                'lastmod' => $rewardPage->modified
                    ? date('Y-m-d', is_numeric($rewardPage->getRawOriginal('modified')) ? (int)$rewardPage->getRawOriginal('modified') : strtotime((string)$rewardPage->modified))
                    : date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        } else {
            // 备用链接
            $items[] = [
                'loc' => "{$this->scheme}://{$this->host}/213699.html",
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }
        
        // 添加分类页
        $categories = CategoriesModel::query()->get();
        foreach ($categories as $category) {
            if ($url = $category->url()) {
                $items[] = [
                    'loc' => "{$this->scheme}://{$this->host}{$url}",
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                ];
            }
        }
        
        return $this->renderSitemap(Sitemap::newUrl(), $items);
    }

    public function sitemap_archivesAction()
    {
        $page = max((int) $this->getRequest()->getParam('page'), 1);
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        
        $posts = ContentsModel::queryWebPost()
            ->orderByDesc('modified')
            ->offset($offset)
            ->limit($limit)
            ->get(['cid', 'slug', 'modified', 'type']);
        
        $items = [];
        foreach ($posts as $post) {
            if ($url = $post->url()) {
                $items[] = [
                    'loc' => "{$this->scheme}://{$this->host}{$url}",
                    'lastmod' => $post->modified ? $post->modified->format('Y-m-d') : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }
        
        return $this->renderSitemap(Sitemap::newUrl(), $items);
    }

    protected function renderSitemap(Sitemap $sitemap, array $items): bool
    {
        $sitemap->setHost($this->host);
        $sitemap->setScheme($this->scheme);
        $sitemap->setItems($items);
        $this->getResponse()->setHeader("Content-Type", "application/xml; charset=utf-8");
        $this->getResponse()->setBody($sitemap->render());
        return false;
    }

    public function index_nowAction()
    {
        $slug = $this->getRequest()->getParam('slug');
        $key = setting('index_now_key');
        
        if (strlen($slug) != 32 || $key != $slug) {
            header('HTTP/1.0 404 Not Found');
            exit('not found');
        }
        
        header('Content-Type: text/plain');
        echo $key;
    }

    public static function indexnowEnqueue(string $url): void
    {
        if ($url) {
            redis()->sAdd('indexnow:urlset', $url);
        }
    }

    protected function indexnowFlushBatch(?int $batchSize = null): array
    {
        $batchSize = $batchSize ?: $this->indexnowBatchSize;
        $redis = redis();
        $urls = [];
        
        for ($i = 0; $i < $batchSize; $i++) {
            if (!$u = $redis->sPop($this->indexnowRedisKey)) break;
            $urls[] = $u;
        }
        
        if (empty($urls)) {
            return ['ok' => true, 'count' => 0, 'http' => 0];
        }

        $payload = json_encode([
            'host' => $this->indexnowHost,
            'key' => $this->indexnowKey,
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

        foreach ($urls as $u) {
            $redis->sAdd($this->indexnowRedisKey, $u);
        }
        
        error_log("IndexNow push failed http={$http} err={$err} resp={$resp}");
        return ['ok' => false, 'http' => $http, 'err' => $err];
    }

    public function indexnow_pushAction()
    {
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $secret = (string) $this->getRequest()->getQuery('secret', '');
        
        if ($ip !== '127.0.0.1' && $ip !== '::1' && (!$this->indexnowPushSecret || $secret !== $this->indexnowPushSecret)) {
            header('HTTP/1.1 403 Forbidden');
            exit('forbidden');
        }
        
        $batch = (int) $this->getRequest()->getQuery('batch', $this->indexnowBatchSize);
        $ret = $this->indexnowFlushBatch($batch);
        
        header('Content-Type: application/json');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    public function indexnow_cliAction()
    {
        $batch = (int) ($this->getRequest()->getParam('batch') ?: $this->indexnowBatchSize);
        $ret = $this->indexnowFlushBatch($batch);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
