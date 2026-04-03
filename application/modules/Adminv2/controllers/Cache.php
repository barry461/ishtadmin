<?php

use service\CacheKeyService;
use tools\HttpCurl;

class CacheController extends AdminV2BaseController
{
    private static $YAC_KEYS = [
        'category:{slug}' => [
            'file' => 'application/modules/Home/controllers/Categories.php',
            'line' => 26,
        ],
    ];
    
    private static $REDIS_DIRECT_KEYS = [
        'ban_member_ips' => [
            'file' => 'application/modules/Home/controllers/Comments.php',
            'line' => 119,
        ],
        'rk:contents:ads:ids' => [
            'file' => 'application/modules/Home/controllers/Categories.php',
            'line' => 40,
        ],
    ];
    
    private static $REDIS_CACHED_KEYS = [
        'content:home-{page}' => [
            'file' => 'application/modules/Home/controllers/Home.php',
            'line' => 22,
            'group' => 'gp:content:home-list',
        ],
        'content:home:count' => [
            'file' => 'application/modules/Home/controllers/Home.php',
            'line' => 38,
            'group' => 'gp:content:home-count',
        ],
        'content:category-list-{categoryId}_{page}' => [
            'file' => 'application/modules/Home/controllers/Categories.php',
            'line' => 50,
            'group' => 'gp:content:category-list',
        ],
        'content:category-list-{categoryId}' => [
            'file' => 'application/modules/Home/controllers/Categories.php',
            'line' => 78,
            'group' => 'gp:content:category-list-count',
        ],
        
        'archive:{cid}' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 21,
            'group' => 'gp:content:archives',
        ],
        'archive:{cid}:exists' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 36,
            'group' => 'gp:content:archives-exist',
        ],
        'archive:{cid}:prev' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 66,
            'group' => 'gp:content:archives-prev',
        ],
        'archive:{cid}:next' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 72,
            'group' => 'gp:content:archives-next',
        ],
        'comments:all:{cid}' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 369,
        ],
        'contents:archives-{page}' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 417,
        ],
        'contents:history-total' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 435,
        ],
        'tags-list' => [
            'file' => 'application/modules/Home/controllers/Archives.php',
            'line' => 550,
            'group' => 'gp:content:tags-list',
        ],
        'author-list-count:{authorId}' => [
            'file' => 'application/modules/Home/controllers/Authors.php',
            'line' => 33,
            'group' => 'gp:author-list-count',
        ],
        'author-list-{authorId}:{page}' => [
            'file' => 'application/modules/Home/controllers/Authors.php',
            'line' => 46,
            'group' => 'gp:author-list',
        ],
        'tags-list-new' => [
            'file' => 'application/modules/Home/controllers/Tag.php',
            'line' => 17,
            'group' => 'gp:tags-list-new',
        ],
        'tag-detail-{mid}-page{page}' => [
            'file' => 'application/modules/Home/controllers/Tag.php',
            'line' => 231,
            'group' => 'gp:tag-detail',
        ],
        'search-list-{md5}:page:{page}' => [
            'file' => 'application/modules/Home/controllers/Searchs.php',
            'line' => 46,
            'group' => 'gp:search-list',
        ],
        'search-list-{md5}:total' => [
            'file' => 'application/modules/Home/controllers/Searchs.php',
            'line' => 63,
            'group' => 'gp:search-count',
        ],
        'list-comment:{cid}-{page}' => [
            'file' => 'application/modules/Home/controllers/Comments.php',
            'line' => 24,
            'group' => 'list-comment-list',
        ],
        'content:home-rss-items' => [
            'file' => 'application/modules/Home/controllers/Feeds.php',
            'line' => 33,
            'group' => 'gp:content:rss-list',
        ],
        'comments:rss:{cid}' => [
            'file' => 'application/modules/Home/controllers/Feeds.php',
            'line' => 128,
            'group' => 'gp:content:rss-comments',
        ],
        'comments:atom:{cid}' => [
            'file' => 'application/modules/Home/controllers/Feeds.php',
            'line' => 245,
            'group' => 'gp:content:rss-comments',
        ],
        'comments:rss-comments:{cid}' => [
            'file' => 'application/modules/Home/controllers/Feeds.php',
            'line' => 317,
            'group' => 'gp:content:rss-comments',
        ],
    ];
    
    private static $FILE_CACHE_KEYS = [];
    
    /**
     * GET /adminv2/cache/getKeys
     */
    public function getKeysAction()
    {
        $data = [
            'yac' => self::$YAC_KEYS,
            'redis_direct' => self::$REDIS_DIRECT_KEYS,
            'redis_cached' => self::$REDIS_CACHED_KEYS,
            'file_cache' => self::$FILE_CACHE_KEYS,
        ];
        
        return $this->showJson($data);
    }
    
    /**
     * GET /adminv2/cache/getYacKeys
     */
    public function getYacKeysAction()
    {
        return $this->showJson(self::$YAC_KEYS);
    }
    
    /**
     * GET /adminv2/cache/getRedisDirectKeys
     */
    public function getRedisDirectKeysAction()
    {
        return $this->showJson(self::$REDIS_DIRECT_KEYS);
    }
    
    /**
     * GET /adminv2/cache/getRedisCachedKeys
     */
    public function getRedisCachedKeysAction()
    {
        return $this->showJson(self::$REDIS_CACHED_KEYS);
    }
    
    /**
     * GET /adminv2/cache/getFileCacheKeys
     */
    public function getFileCacheKeysAction()
    {
        return $this->showJson(self::$FILE_CACHE_KEYS);
    }
    
    /**
     * POST /adminv2/cache/clear
     * type: data|yac|file
     */
    public function clearAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }
        
        $type = $this->data['type'] ?? '';
        
        if (empty($type)) {
            return $this->validationError('缓存类型不能为空');
        }
        
        if (!in_array($type, ['data', 'yac', 'file'])) {
            return $this->validationError('缓存类型错误，只支持: data, yac, file');
        }
        
        try {
            $result = [];
            
            switch ($type) {
                case 'data':
                    $result = $this->clearDataCache();
                    break;
                case 'yac':
                    $result = $this->clearYacCache();
                    break;
                case 'file':
                    $result = $this->clearFileCache();
                    break;
            }
            
            return $this->showJson([
                'type' => $type,
                'status' => $result['status'] ?? 'success',
                'message' => $result['message'] ?? '清理成功',
                'details' => $result['details'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson('清理失败：' . $e->getMessage());
        }
    }
    
    private function clearDataCache()
    {
        $groups = [
            'gp:content:home-list',
            'gp:content:home-count',
            'gp:content:category-list',
            'gp:content:category-list-count',
            'gp:content:archives',
            'gp:content:archives-exist',
            'gp:content:archives-prev',
            'gp:content:archives-next',
            'gp:author-list-count',
            'gp:author-list',
            'gp:tags-list-new',
            'gp:tag-detail',
            'gp:search-list',
            'gp:search-count',
            'list-comment-list',
            'gp:content:rss-list',
            'gp:content:rss-comments',
            'gp:content:tags-list',
        ];
        
        $queue = 'jobs:work:queue';
        $clearCount = count($groups);
        if (function_exists('bg_run')) {
            bg_run(function () use ($groups) {
                try {
                    foreach ($groups as $group) {
                        CacheKeyService::clear_group($group);
                    }
                    trigger_log('数据缓存清理完成，共清理 ' . count($groups) . ' 个缓存组');
                } catch (\Throwable $e) {
                    trigger_log('数据缓存清理失败: ' . $e->getMessage());
                }
            });
        } else {
            // 如果没有 bg_run 函数，使用 Redis 队列
            try {
                $serialized = serialize([function () use ($groups) {
                    try {
                        foreach ($groups as $group) {
                            CacheKeyService::clear_group($group);
                        }
                        trigger_log('数据缓存清理完成，共清理 ' . count($groups) . ' 个缓存组');
                    } catch (\Throwable $e) {
                        trigger_log('数据缓存清理失败: ' . $e->getMessage());
                    }
                }, []]);
                $data = json_encode([$serialized]);
                redis()->rPush($queue, $data);
            } catch (\Throwable $e) {
                throw new \Exception('提交异步任务失败: ' . $e->getMessage());
            }
        }
        
        return [
            'status' => 'success',
            'message' => "已提交异步清理任务，共 {$clearCount} 个缓存组",
            'details' => [
                'groups_count' => $clearCount,
                'groups' => $groups,
                'async' => true,
            ],
        ];
    }
    
    private function clearYacCache()
    {
        $clearedCount = 0;
        $errors = [];
        if (class_exists('\Yac')) {
            try {
                $htmlYac = new \Yac("html_");
                if (method_exists($htmlYac, 'flush')) {
                    $htmlYac->flush();
                    $clearedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "HTML缓存清理失败: " . $e->getMessage();
            }
        }
        try {
            $libYac = new \tools\LibYac();
            if (method_exists($libYac, 'flush')) {
                $libYac->flush();
                $clearedCount++;
            }
        } catch (\Exception $e) {
            $errors[] = "LibYac清理失败: " . $e->getMessage();
        }
        try {
            $defaultYac = new \Yac();
            if (method_exists($defaultYac, 'flush')) {
                $defaultYac->flush();
                $clearedCount++;
            }
        } catch (\Exception $e) {
            $errors[] = "默认YAC清理失败: " . $e->getMessage();
        }
        try {
            if (class_exists('YacCacheManager')) {
                \YacCacheManager::clearCache('all');
                $clearedCount++;
            }
        } catch (\Exception $e) {
            $errors[] = "YacCacheManager清理失败: " . $e->getMessage();
        }
        
        $message = "成功清理了 {$clearedCount} 个 Yac 缓存区域";
        if (!empty($errors)) {
            $message .= "，但有 " . count($errors) . " 个错误: " . implode('; ', $errors);
        }
        
        return [
            'status' => empty($errors) ? 'success' : 'partial',
            'message' => $message,
            'details' => [
                'cleared_count' => $clearedCount,
                'errors' => $errors,
                'async' => false,
            ],
        ];
    }
    
    private function clearFileCache()
    {
        $cacheDir = APP_PATH . '/storage/internal_cache';
        
        if (!is_dir($cacheDir)) {
            return [
                'status' => 'success',
                'message' => '缓存目录不存在，无需清理',
                'details' => [
                    'cache_dir' => $cacheDir,
                    'files_cleared' => 0,
                    'async' => false,
                ],
            ];
        }
        
        $filesCleared = $this->countFiles($cacheDir);
        $queue = 'jobs:work:queue';
        $siteUrl = rtrim(options('siteUrl'), '/');
        
        if (function_exists('bg_run')) {
            bg_run(function () use ($cacheDir, $siteUrl) {
                try {
                    $this->clearDirectory($cacheDir);
                    trigger_log('文件缓存清理完成: ' . $cacheDir);
                    $homeUrl = $siteUrl . '/';
                    try {
                        $r = HttpCurl::get($homeUrl);
                        trigger_log("首页缓存重新生成: {$homeUrl} - " . ($r ? "成功" : "失败"));
                    } catch (\Exception $e) {
                        trigger_log("首页缓存重新生成失败: " . $e->getMessage());
                    }
                } catch (\Throwable $e) {
                    trigger_log('文件缓存清理失败: ' . $e->getMessage());
                }
            });
        } else {
            try {
                $serialized = serialize([function () use ($cacheDir, $siteUrl) {
                    try {
                        $this->clearDirectory($cacheDir);
                        trigger_log('文件缓存清理完成: ' . $cacheDir);
                        $homeUrl = $siteUrl . '/';
                        try {
                            $r = HttpCurl::get($homeUrl);
                            trigger_log("首页缓存重新生成: {$homeUrl} - " . ($r ? "成功" : "失败"));
                        } catch (\Exception $e) {
                            trigger_log("首页缓存重新生成失败: " . $e->getMessage());
                        }
                    } catch (\Throwable $e) {
                        trigger_log('文件缓存清理失败: ' . $e->getMessage());
                    }
                }, []]);
                $data = json_encode([$serialized]);
                redis()->rPush($queue, $data);
            } catch (\Throwable $e) {
                throw new \Exception('提交异步任务失败: ' . $e->getMessage());
            }
        }
        
        return [
            'status' => 'success',
            'message' => "已提交异步清理任务，共 {$filesCleared} 个文件/目录",
            'details' => [
                'cache_dir' => $cacheDir,
                'files_cleared' => $filesCleared,
                'async' => true,
                'regenerate' => true,
            ],
        ];
    }
    
    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                @rmdir($file);
            }
        }
    }
    
    private function countFiles(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }
        
        $count = 0;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $count++;
            } elseif (is_dir($file)) {
                $count += $this->countFiles($file);
                $count++;
            }
        }
        
        return $count;
    }
}