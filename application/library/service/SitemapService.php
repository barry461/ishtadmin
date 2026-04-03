<?php

namespace service;

use Tbold\Utils\Sitemap;
use ContentsModel;
use CategoriesModel;

class SitemapService
{
    protected $host;
    protected $scheme = 'https';
    protected $maxUrlsPerFile = 5000;
    
    public function __construct(?string $host = null)
    {
        // 写死使用https
        $this->scheme = 'https';
        
        $this->host = parse_url(options('siteUrl'), PHP_URL_HOST) ?: (config('app.host') ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        
    }
    
    public function getHost(): string
    {
        return $this->host;
    }
    
    public function generateHomeSitemap(): string
    {
        $items = [[
            'loc' => "{$this->scheme}://{$this->host}/",
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ]];
        
        return $this->renderSitemap(Sitemap::newUrl(), $items);
    }
    
    public function generateCategorySitemap(): string
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
    
    public function generateArchivesSitemap(int $page = 1): string
    {
        $page = max($page, 1);
        $offset = ($page - 1) * $this->maxUrlsPerFile;
        
        $posts = ContentsModel::queryWebPost()
            ->orderByDesc('modified')
            ->offset($offset)
            ->limit($this->maxUrlsPerFile)
            ->get(['cid', 'slug', 'modified', 'type']);
        
        $items = [];
        foreach ($posts as $post) {
            if ($url = $post->url()) {
                $items[] = [
                    'loc' => "{$this->scheme}://{$this->host}{$url}",
                    'lastmod' => $post->modified
                        ? date('Y-m-d', is_numeric($post->getRawOriginal('modified')) ? (int)$post->getRawOriginal('modified') : strtotime((string)$post->modified))
                        : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }
        
        return $this->renderSitemap(Sitemap::newUrl(), $items);
    }
    
    
    public function generateMainSitemap(): string
    {
        $baseUrl = "{$this->scheme}://{$this->host}/sitemap";
        $totalPages = max(1, ceil(ContentsModel::queryWebPost()->count() / $this->maxUrlsPerFile));
        
        $items = [
            ['loc' => "{$baseUrl}/sitemap-home.xml", 'lastmod' => date('Y-m-d')],
            ['loc' => "{$baseUrl}/sitemap-category.xml", 'lastmod' => date('Y-m-d')],
        ];
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $items[] = ['loc' => "{$baseUrl}/sitemap-archives-{$i}.xml", 'lastmod' => date('Y-m-d')];
        }
        
        return $this->renderSitemap(Sitemap::newIndex(), $items);
    }

    public function getArchivesSitemapCount(): int
    {
        return max(1, ceil(ContentsModel::queryWebPost()->count() / $this->maxUrlsPerFile));
    }
    
    public function generateAndSaveSitemap(string $type, int $page = 1): string
    {
        $generators = [
            'home' => fn() => [$this->generateHomeSitemap(), 'sitemap-home.xml'],
            'category' => fn() => [$this->generateCategorySitemap(), 'sitemap-category.xml'],
            'archives' => fn() => [$this->generateArchivesSitemap($page), "sitemap-archives-{$page}.xml"],
            'main' => fn() => [$this->generateMainSitemap(), 'sitemap.xml'],
        ];
        
        if (!isset($generators[$type])) {
            return '';
        }
        
        try {
            [$xml, $filename] = $generators[$type]();
            $filepath = APP_PATH . '/storage/sitemaps/' . $filename;
            $dir = dirname($filepath);
            
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new \Exception("无法创建目录: {$dir}");
            }
            
            if (file_put_contents($filepath, $xml) === false) {
                throw new \Exception("无法写入文件: {$filepath}");
            }
            
            return $filepath;
        } catch (\Exception $e) {
            error_log("SitemapService error: " . $e->getMessage());
            return '';
        }
    }

    protected function renderSitemap(Sitemap $sitemap, array $items): string
    {
        $sitemap->setHost($this->host);
        $sitemap->setScheme($this->scheme);
        $sitemap->setItems($items);
        return $sitemap->render();
    }
}
