<?php

use service\CacheKeyService;
use service\ContentsService;
use tools\HttpCurl;


class CachemanageController extends BackendBaseController
{

    use \repositories\HoutaiRepository, \website\HtmlCache;

    public function listAjaxAction(): bool
    {
        $name = trim($this->getRequest()->getQuery('name', ''));
        if ($name === '__undefined__') {
            $name = '';
        }
        $groups = CacheKeyService::all_group();

        if ($name !== '') {
            $groups = $groups->filter(function ($item) use ($name) {
                return isset($item->name) && stripos($item->name, $name) !== false;
            })->values();
        }

        $result = [
            'count' => $groups->count(),
            'data'  => $groups,
            "msg"   => '',
            "desc"  => '',
            'code'  => 0
        ];
        return $this->ajaxReturn($result);
    }

    public function indexAction()
    {
        $this->display();
    }

    protected function getModelClass(): string
    {
        return '';
    }

    protected function getPkName(): string
    {
        return 'id';
    }

    protected function getLogDesc(): string
    {
        return '';
    }

    public function refreshAction(): bool
    {
        try {
            if ($_POST['group']) {
                CacheKeyService::clear_group($_POST['group']);
            }
            return $this->ajaxSuccessMsg('成功清理缓存');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function refresh_redisAction()
    {
        $names = $_POST['names'];
        if (!empty($names)) {
            bg_run(function ()use($names) {

                $name_list = explode(",", $names);
                foreach ($name_list as $name) {
                    $caches = CacheKeysModel::where('name', $name)->get();

                    foreach ($caches as $item) {
                        cached("")->clearGroup($item->key);
                        CacheKeyService::sRem($item->name, $item->key);
                        $item->delete();
                    }
                }
            });
        }
        return $this->ajaxSuccessMsg('成功清理缓存');
    }
    
    public function refresh_yacAction(): bool
    {
        try {
            $clearedCount = 0;
            $errors = [];
            
            // 1. 清理HTML缓存相关的YAC
            if (class_exists('\Yac')) {
                try {
                    $htmlYac = new \Yac("html_");
                    if (method_exists($htmlYac, 'flush')) {
                        $htmlYac->flush();
                        $clearedCount++;
                    }
                } catch (Exception $e) {
                    $errors[] = "HTML缓存清理失败: " . $e->getMessage();
                }
            }
            
            // 2. 清理LibYac实例
            try {
                $libYac = new \tools\LibYac();
                if (method_exists($libYac, 'flush')) {
                    $libYac->flush();
                    $clearedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "LibYac清理失败: " . $e->getMessage();
            }
            
            // 3. 清理默认YAC实例
            try {
                $defaultYac = new \Yac();
                if (method_exists($defaultYac, 'flush')) {
                    $defaultYac->flush();
                    $clearedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "默认YAC清理失败: " . $e->getMessage();
            }
            
            // 4. 清理文件缓存目录
            try {
                $cacheDir = APP_PATH . '/storage/internal_cache';
                if (is_dir($cacheDir)) {
                    $this->clearDirectory($cacheDir);
                    $clearedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "文件缓存清理失败: " . $e->getMessage();
            }
            
            // 5. 清理其他可能的缓存文件
            try {
                $viewsCacheDir = APP_PATH . '/storage/views';
                if (is_dir($viewsCacheDir)) {
                    $this->clearDirectory($viewsCacheDir);
                    $clearedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "视图缓存清理失败: " . $e->getMessage();
            }
            
            // 6. 清理变量管理缓存
            try {
                if (class_exists('VariableModel')) {
                    VariableModel::clearCached();
                    yacsys()->expire('x-variable', 1);
                    $clearedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "变量管理缓存清理失败: " . $e->getMessage();
            }
            
            $message = "成功清理了 {$clearedCount} 个缓存区域";
            if (!empty($errors)) {
                $message .= "，但有 " . count($errors) . " 个错误: " . implode('; ', $errors);
            }
            
            return $this->ajaxSuccessMsg($message);
            
        } catch (Exception $e) {
            return $this->ajaxError('清理缓存时发生错误: ' . $e->getMessage());
        } catch (Throwable $e) {
            return $this->ajaxError('系统错误: ' . $e->getMessage());
        }
    }

    /**
     * yac 缓存信息
     * @return bool
     */
    public function yacinfoAction(): bool
    {
        $message = '';

        $info = $this->_initNewHtmlCache()->yacinfo();

        $hitRate = round($info['hits'] / ($info['hits'] + $info['miss']) * 100, 2);
        $memUsage = round($info['slots_used'] / $info['slots_size'] * 100, 4);
        echo "命中率：{$hitRate}% 内存使用率：{$memUsage}%\n\n";
        echo "<pre>";
        print_r($info);
    }

    private function _initNewHtmlCache()
    {
        static $NEWHTMLCACHE = null;

        if( is_null($NEWHTMLCACHE) ){
            $options = require APP_PATH.'/application/html.php';
            $this->NewHtmlCache(is_array($options) ? $options : []);
            $NEWHTMLCACHE = &$this;
        }
        return $NEWHTMLCACHE;
    }

    /**
     * 选择性清理缓存
     */
    public function selective_clearAction(): bool
    {
        try {
            $cacheTypes = $_POST['cache_types'] ?? [];
            $options = $_POST['options'] ?? [];
            
            if (empty($cacheTypes)) {
                return $this->ajaxError('请至少选择一种缓存类型');
            }
            
            $clearedCount = 0;
            $errors = [];
            $details = [];
            
            // 处理缓存类型清理
            foreach ($cacheTypes as $type) {
                $result = $this->clearCacheType($type, $options);
                if ($result['success']) {
                    $clearedCount += $result['count'];
                    $details[] = $result['message'];
                } else {
                    $errors[] = $result['error'];
                }
            }

            $message = "成功清理了 {$clearedCount} 个缓存项";
            if (!empty($details)) {
                $message .= "。详情：" . implode('; ', $details);
            }
            if (!empty($errors)) {
                $message .= "。错误：" . implode('; ', $errors);
            }
            
            return $this->ajaxSuccessMsg($message);
            
        } catch (Exception $e) {
            return $this->ajaxError('选择性清理缓存时发生错误: ' . $e->getMessage());
        } catch (Throwable $e) {
            return $this->ajaxError('系统错误: ' . $e->getMessage());
        }
    }
    
    /**
     * 清理指定类型的缓存
     * @param string $type
     * @param array $options
     * @return array
     */
    private function clearCacheType(string $type, array $options): array
    {
        try {
            $count = 0;
            $message = '';
            
            switch ($type) {
                // 首页列表缓存
                case 'index_list_cache':
                    // 清理分类缓存
                    if (class_exists('CategoriesModel')) {
                        yacsys()->expire('category:categories', 1);
                        yacsys()->expire('category:pages', 1);
                    }
                    //数据缓存
                    CacheKeyService::clear_group('gp:content:home-list');
                    CacheKeyService::clear_group('gp:content:home-count');
                    // yac缓存
                    $names='/';
                    $this->_initNewHtmlCache()->del($names);
                    bg_run(function ()use($names) {
                        $q_url = rtrim(options('siteUrl'),'/').$names;
                        $r = HttpCurl::get($q_url);
                        trigger_log("首页{$q_url}重载缓存 - ". ($r ? "成功":"失败") );
                    });
                    $count++;
                    $message = '首页列表缓存';
                    break;
                case 'cate_list_cache':
                    $serviceContent = new ContentsService();
                    $cates = $serviceContent->categoryMeats();

                    // 清理分类缓存
                    if (class_exists('CategoriesModel')) {
                        yacsys()->expire('category:categories', 1);
                        yacsys()->expire('category:pages', 1);
                    }
                    if (class_exists('AppCategoryModel')) {
                        AppCategoryModel::clearCache();
                        $count++;
                    }
                    if (class_exists('PcAppCategoryModel')) {
                        PcAppCategoryModel::clearCache();
                        $count++;
                    }
                    //数据缓存
                    CacheKeyService::clear_group('gp:content:category-list');
                    CacheKeyService::clear_group('gp:content:category-list-count');
                    YacCacheManager::clearCache('metas');
                    // yac缓存
                    foreach ($cates as $c_row){
                        $names = "/category/".$c_row['slug']."/";
                        $this->_initNewHtmlCache()->del($names);
                        bg_run(function ()use($names) {
                            $q_url = rtrim(options('siteUrl'),'/').$names;
                            $r = HttpCurl::get($q_url);
                            trigger_log("分类列表页{$q_url}重载缓存 - ". ($r ? "成功":"失败") );
                        });
                    }
                    $count++;
                    $message = '分类列表缓存';
                    break;
                // 文章详情缓存
                case 'content_cache':
//                    $this->clearContentCache();
                    //数据缓存
                    CacheKeyService::clear_group('list-comment-list');
                    // internal缓存过期标记
                    $this->clearCacheOptions(['force_refresh']);
                    $count++;
                    $message = '文章详情缓存';
                    break;
                case 'advert_cache':
                    //数据缓存
                    CacheKeyService::clear_group('gp:advert-list');
                    // internal缓存过期标记
                    $this->clearCacheOptions(['force_refresh']);
                    $count++;
                    $message = '广告管理缓存';
                    break;
                case 'transit_cache':
                    yacsys()->delete('options:all');
                    $count++;
                    $message = '中转页面缓存';
                    break;
//                case 'user_data':
//                    $this->clearUserDataCache();
//                    $count++;
//                    $message = '用户数据缓存';
//                    break;
//
//                case 'app_categories':
//                    if (class_exists('AppCategoryModel')) {
//                        AppCategoryModel::clearCache();
//                        $count++;
//                        $message = '应用分类缓存';
//                    }
//                    break;
//
//                case 'pc_categories':
//                    if (class_exists('PcAppCategoryModel')) {
//                        PcAppCategoryModel::clearCache();
//                        $count++;
//                        $message = 'PC分类缓存';
//                    }
//                    break;
                    
                // 文件缓存
//                case 'file_cache':
//                    $cacheDir = APP_PATH . '/storage/internal_cache';
//                    if (is_dir($cacheDir)) {
//                        $this->clearDirectory($cacheDir);
//                        $count++;
//                        $message = '文件缓存目录';
//                    }
//                    break;
//
//                case 'views_cache':
//                    $viewsCacheDir = APP_PATH . '/storage/views';
//                    if (is_dir($viewsCacheDir)) {
//                        $this->clearDirectory($viewsCacheDir);
//                        $count++;
//                        $message = '视图模板缓存';
//                    }
//                    break;
//
//                case 'yac_html':
//                    if (class_exists('\Yac')) {
//                        $htmlYac = new \Yac("html_");
//                        if (method_exists($htmlYac, 'flush')) {
//                            $htmlYac->flush();
//                            $count++;
//                            $message = 'HTML页面缓存';
//                        }
//                    }
//                    break;
                    
                // 配置缓存
                case 'system_settings':
                    if (class_exists('SettingModel')) {
                        SettingModel::pushCached();
                        yacsys()->expire('system:setting', 1);
                        $count++;
                        $message = '系统设置缓存';
                    }
                    break;
                    
                case 'system_variables':
                    if (class_exists('VariableModel')) {
                        VariableModel::clearCached();
                        yacsys()->expire('x-variable', 1);
                        cached('x-variable')->clearCached();
                        $count++;
                        $message = '系统变量缓存';
                    }
                    break;
                    
                case 'yac_lib':
                    $libYac = new \tools\LibYac();
                    if (method_exists($libYac, 'flush')) {
                        $libYac->flush();
                        $count++;
                        $message = 'LibYac缓存';
                    }
                    break;
                    
                case 'yac_default':
                    if (class_exists('\Yac')) {
                        $defaultYac = new \Yac();
                        if (method_exists($defaultYac, 'flush')) {
                            $defaultYac->flush();
                            $count++;
                            $message = '导航及SEO缓存';
                        }
                    }
                    YacCacheManager::clearCache('all');
                    break;
                    
                case 'yac_all':
                    $clearedCount = 0;
                    // 清理默认YAC实例
                    if (class_exists('\Yac')) {
                        try {
                            $defaultYac = new \Yac();
                            if (method_exists($defaultYac, 'flush')) {
                                $defaultYac->flush();
                                $clearedCount++;
                            }
                        } catch (Exception $e) {
                            // 忽略错误
                        }
                    }
                    // 清理LibYac实例
                    try {
                        $libYac = new \tools\LibYac();
                        if (method_exists($libYac, 'flush')) {
                            $libYac->flush();
                            $clearedCount++;
                        }
                    } catch (Exception $e) {
                        // 忽略错误
                    }
                    // 清理YacCacheManager
                    try {
                        YacCacheManager::clearCache('all');
                        $clearedCount++;
                    } catch (Exception $e) {
                        // 忽略错误
                    }
                    $count = $clearedCount;
                    $message = '全部YAC缓存';
                    break;
                    
                default:
                    return ['success' => false, 'error' => "未知的缓存类型: {$type}"];
            }
            
            return [
                'success' => true,
                'count' => $count,
                'message' => $message
            ];
            
        } catch (Exception $e) {
            trigger_log("清理 {$type} 失败: " . $e->getMessage());
            return ['success' => false, 'error' => "清理 {$type} 失败: " . $e->getMessage()];
        }
    }

    private function clearCacheOptions(array $options): array
    {
        try {
            $count = 0;
            $message = '';
            foreach ($options as $type){
                switch ($type) {
                    case 'force_refresh':
                        $file = APP_PATH.'/application/config.php';
                        $site = @include($file);
                        if(!is_array($site)) $site = [];
                        $site['internal_cache_expiration_time'] = date('Y-m-d H:i:s');
                        $write_data = "<?php


return ".var_export($site,true).";";
                        safe_write($file, $write_data);
                        break;
                    default:
                        return ['success' => false, 'error' => "未知的缓存类型: {$type}"];
                }
            }


            return [
                'success' => true,
                'count' => $count,
                'message' => $message
            ];

        } catch (Exception $e) {
            trigger_log("清理 {$type} 失败: " . $e->getMessage());
            return ['success' => false, 'error' => "清理 {$type} 失败: " . $e->getMessage()];
        }
    }
    
    /**
     * 清理内容相关缓存
     */
    private function clearContentCache(): void
    {
        // 清理内容缓存
        if (class_exists('ContentsModel')) {
            // 清理内容相关的YAC缓存
            yac()->expire('content:total', 1);
            yac()->expire('transit_seo_map', 1);
        }
        
        // 清理分类缓存
        if (class_exists('CategoriesModel')) {
            yac()->expire('category:categories', 1);
            yac()->expire('category:pages', 1);
        }
    }
    
    /**
     * 清理用户数据缓存
     */
    private function clearUserDataCache(): void
    {
        // 清理用户相关的缓存
        if (class_exists('UserModel')) {
            // 这里可以添加用户相关的缓存清理逻辑
        }
        
        // 清理VIP相关缓存
        if (class_exists('InfoVipModel')) {
            // 清理VIP列表缓存等
        }
    }
    
    /**
     * 清理目录中的所有文件
     * @param string $dir
     * @return void
     */
    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }
}