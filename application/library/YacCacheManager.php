<?php

/**
 * YAC缓存管理器
 * 统一管理公共数据、配置数据、不经常改动数据的缓存
 */
class YacCacheManager
{
    // 缓存键前缀
    const PREFIX_CONFIG = 'config:';
    const PREFIX_THEME = 'theme:';
    const PREFIX_NAV = 'nav:';
    const PREFIX_SITE = 'site:';
    const PREFIX_APPS = 'apps:';
    const PREFIX_SEO = 'seo:';
    const PREFIX_CATEGORY = 'category:';

    /**
     * 获取配置数据（带缓存）
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getConfig($key, $default = null)
    {
        return yac()->fetch(self::PREFIX_CONFIG . $key, function () use ($key, $default) {
            return options($key, $default);
        });
    }

    /**
     * 获取主题选项（带缓存）
     * @param string $key 主题键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getThemeOption($key, $default = null)
    {
        return yac()->fetch(self::PREFIX_THEME . $key, function () use ($key, $default) {
            return theme_options()->get($key, $default);
        });
    }

    /**
     * 获取站点配置（带缓存）
     * @return array
     */
    public static function getSiteConfig()
    {
        return yac()->fetch(self::PREFIX_CONFIG . 'site', function () {
            return \Yaf_Registry::get('site');
        });
    }

    /**
     * 获取导航数据（带缓存）
     * @param string $type 类型：category, metas, pages
     * @return mixed
     */
    public static function getNavData($type)
    {
        $data = yac()->fetch(self::PREFIX_NAV . $type, function () use ($type) {
            $service = new \service\ContentsService();
            switch ($type) {
                case 'category':
                    return $service->category();
                case 'metas':
                    return $service->categoryMeats();
                case 'pages':
                    return $service->categoryPages();
                default:
                    return null;
            }
        });

        // 规范化返回：始终返回 Collection，避免出现 bool/null 导致调用方报错
        if (!($data instanceof \Illuminate\Support\Collection)) {
            $data = collect($data ?: []);
        }
        return $data;
    }

    /**
     * 获取应用列表（带缓存）
     * @return mixed
     */
    public static function getAppList()
    {
        return yac()->fetch(self::PREFIX_APPS . 'archives', function () {
            $categories = AppCategoryModel::query()
                ->where('status', AppCategoryModel::STATUS_OK)
                ->orderBy('sort')
                ->get()
                ->keyBy('id');

            $apps = AppModel::query()
                ->select(['id', 'name', 'thumb', 'url', 'category_id'])
                ->whereIn('category_id', $categories->keys())
                ->orderBy('sort')
                ->get();
            
            $grouped = collect();
            foreach ($categories as $category) {
                $group = $apps->where('category_id', $category->id)->values();
                if ($group->isNotEmpty()) {
                    $grouped->put($category->name, $group);
                }
            }
            return $grouped;
        });
    }

    /**
     * 获取SEO模板（带缓存）
     * @param string $key 模板键
     * @return string
     */
    public static function getSeoTemplate($key)
    {
        return SeoTplModel::seo_tpl($key);
    }

    /**
     * 获取版权信息（带缓存）
     * @return string
     */
    public static function getCopyright()
    {
        return yac()->fetch(self::PREFIX_SITE . 'copyright', function () {
            // 获取后台设置的版权信息
            $optionService = new \service\OptionService();
            return $optionService->getSubKey('plugin:FootMenu', 'footer_copyright');
        });
    }

    /**
     * 获取主题JS常量（带缓存）
     * @return array
     */
    public static function getThemeJsConst()
    {
        return yac()->fetch(self::PREFIX_THEME . 'js_const', function () {
            return theme_options()->JavascriptLocalConst();
        });
    }

    /**
     * 清除指定类型的缓存
     * @param string $type 缓存类型：config, theme, nav, site, apps, seo, category
     * @return bool
     */
    public static function clearCache($type)
    {
        $prefix = '';
        switch ($type) {
            case 'config':
                $prefix = self::PREFIX_CONFIG;
                break;
            case 'theme':
                $prefix = self::PREFIX_THEME;
                break;
            case 'metas':
            case 'category':
            case 'pages':
                $prefix = self::PREFIX_NAV. $type;
                break;
            case 'site':
                $prefix = self::PREFIX_SITE;
                break;
            case 'apps':
                $prefix = self::PREFIX_APPS;
                break;
            case 'seo':
                $prefix = self::PREFIX_SEO;
                break;
//            case 'category':
//                $prefix = self::PREFIX_CATEGORY;
                break;
            case 'all':
                return yac()->flush();
            default:
                return false;
        }
        
        // 清除指定前缀的缓存
        return yac()->delete($prefix);
    }

    /**
     * 清除所有缓存
     * @return bool
     */
    public static function clearAllCache()
    {
        return yac()->flush();
    }

    /**
     * 获取缓存统计信息
     * @return array
     */
    public static function getCacheStats()
    {
        return [
            'memory_usage' => yac()->info(),
            'cache_keys' => [
                'config' => self::PREFIX_CONFIG,
                'theme' => self::PREFIX_THEME,
                'nav' => self::PREFIX_NAV,
                'site' => self::PREFIX_SITE,
                'apps' => self::PREFIX_APPS,
                'seo' => self::PREFIX_SEO,
                'category' => self::PREFIX_CATEGORY,
            ]
        ];
    }
}
