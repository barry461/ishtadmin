<?php

use service\OptionService;
use website\Router;
use \Yaf_Controller_Abstract;

class WebController extends Tbold_WebsiteController
{
    protected $limit;
    protected $page;
    /** @var ArrayObject */
    private $_jsonLd = null;
    private $_twitter = null;
    private $_og = null;
    private $hasPage = null;
    private $config = [];
    private $is404Page = false;

    protected function init()
    {
        APP_MODULE == "index" && $this->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->ss();
        // 使用YacCacheManager获取配置数据
        $this->limit = YacCacheManager::getConfig("pageSize");
        $this->hasPage = $this->getRequest()->getParam('page');
        $this->page = max($this->hasPage ?? 1, 1);
        $this->config = YacCacheManager::getSiteConfig();
        $this->initVariables();
        $this->varsAssigns();
        $this->onEvent(self::EVENT_VIEW_RENDER_BEFORE, function () {
            // 使用最新主题JS常量（禁用此处缓存以避免样式异常）
            $this->assign('LocalConst', json_encode(theme_options()->JavascriptLocalConst()));
        });
    }

    protected function tryThemeAction($require_file, $clazz, $method)
    {
        try {
            $method = "{$method}Action";
            \Yaf_Loader::import($require_file);
            /** @var self $object */
            $object = new $clazz($this->_request, $this->_response, $this->_view);
            if (!$object instanceof self) {
                return false;
            }
            copy_all_members($this, $object);
            if (!method_exists($object, $method)) {
                return false;
            }
            return $object->$method();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function initVariables()
    {
        if (defined('GLOBAL_VARIABLES')) {
            return;
        }
        $page = $this->hasPage === null ? '' : sprintf("第%d页", $this->page);

        // 定义全局变量
        define("GLOBAL_VARIABLES", [
            '${host}' => $_SERVER['HTTP_HOST'],
            '${uri}' => $_SERVER['REQUEST_URI'],
            '${page}' => $page,
        ]);
        $this->bindVariables(VariableModel::variables());
    }


    protected function ss()
    {
        DB::listen(function ($query) {
            $sql = $query->sql;
            foreach ($query->bindings as $binding) {
                $binding = is_numeric($binding) ? $binding : "'{$binding}'";
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }
            wf("Full SQL", $sql);
        });
    }


    protected function seoJsonLd(): ArrayObject
    {
        // Deprecated: return empty JSON-LD config to avoid injecting legacy SEO
        if ($this->_jsonLd === null) {
            $this->_jsonLd = new ArrayObject();
        }
        return $this->_jsonLd;
    }

    protected function seoTwitter(): ArrayObject
    {
        if ($this->_twitter === null) {
            $this->_twitter = new ArrayObject();
        }
        return $this->_twitter;
    }

    protected function seoOg(): ArrayObject
    {
        if ($this->_og === null) {
            $this->_og = new ArrayObject();
        }
        return $this->_og;
    }

    protected function seoAssign()
    {
        // Deprecated: noop, new SEO headers are rendered by controllers/templates
        return;
    }


    protected function varsAssigns()
    {
        // 公共导航与页面元数据走 Yac 缓存
        $category = YacCacheManager::getNavData('category');
        $metas = YacCacheManager::getNavData('metas');
        $pages = YacCacheManager::getNavData('pages');
        $this->initMirages();
        $pcNavs = $category->chunk(theme_options()->maxNavbarMenuNum ?? 5);
        // 确保 $category 是集合对象，如果不是则使用空集合
        if (!$category || !method_exists($category, 'chunk')) {
            $category = collect();
        }
        $this->assign('config', $this->config);
        $this->assign('pc_navs', $pcNavs[0] ?? []);
        $this->assign('dropdown_navs', $pcNavs[1] ?? []);
        $this->assign('nav_items', $category);
        $this->assign('metas', $metas);
        $this->assign('pages', $pages);
        $this->assign('copyright', $this->copyright());
        $this->seoAssign();

    }


    protected function initMirages()
    {
        // 不使用缓存，直接读取最新主题配置，避免对象/结构被缓存破坏
        $opt = options('theme:Mirages');
        if ($opt instanceof ArrayObject) {
            $optionsArr = $opt->getArrayCopy();
        } elseif (is_object($opt) && method_exists($opt, 'getArrayCopy')) {
            $optionsArr = $opt->getArrayCopy();
        } elseif ($opt instanceof Traversable) {
            $optionsArr = iterator_to_array($opt);
        } else {
            $optionsArr = (array)$opt;
        }
        $options = ThemeOptions::init($optionsArr);
        $this->assign('theme', $options);

        $options->miragesInited = true;

        $this->onEvent(self::EVENT_VIEW_RENDER_BEFORE, function () use ($options) {
            list($hideRssBarItem, $hideNightShiftBarItem, $toolbarItemsOutput)
                = $options->toolbarItems();

            $footMenu = $options->renderFooter();

            $this->assign('mirages', $options);
            $this->assign('footMenu', $footMenu);
            $this->assign('hideRssBarItem', $hideRssBarItem);
            $this->assign('hideNightShiftBarItem', $hideNightShiftBarItem);
            $this->assign('toolbarItemsOutput', $toolbarItemsOutput);
            $this->assign('bodyClass', $options->bodyClass());

            // 使用YacCacheManager获取SEO模板
            $publicHeader = YacCacheManager::getSeoTemplate('public_header');

            // 404页面不加载公共 header 与统计代码
            if ($this->is404Page) {
                $publicHeader = '';
                $seoStatHeadCodes = '';
                $seoStatFooterCodes = '';
            } else {
                // 去掉全局模版中的<title>，避免覆盖各页面按备注规则生成的标题
                $publicHeader = preg_replace('/<title>.*?<\\/title>/is', '', $publicHeader);
                // 移除 public_header 中所有 ld+json 脚本块，防止吞噬后续 <link> 标签
                $publicHeader = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>[\s\S]*?<\\/script>/i', '', $publicHeader);
                // 防御性清理任何可能残留的孤立开头或结尾标签
                $publicHeader = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $publicHeader);

                // 统计与验证代码：head / footer 区
                if (class_exists('SeoStatCodeModel')) {
                    $seoStatHeadCodes = SeoStatCodeModel::renderByPosition('head');
                    $seoStatFooterCodes = SeoStatCodeModel::renderByPosition('footer');
                } else {
                    $seoStatHeadCodes = '';
                    $seoStatFooterCodes = '';
                }
            }

            $this->assign('seoPublicHeader', $publicHeader);
            $this->assign('seoStatHeadCodes', $seoStatHeadCodes ?? '');
            $this->assign('seoStatFooterCodes', $seoStatFooterCodes ?? '');
            // 传递是否为404页面的标志给模板
            $this->assign('is404Page', $this->is404Page);

            $this->assign('swiperImagesIos', $options->renderTechIos());
            $this->assign('swiperImagesAnd', $options->renderTechAnd());
        });

    }

    /**
     * 设置seo的 prev和next
     * @param $page
     * @param $totalPage
     * @param $routerName
     * @param $params
     *
     * @return void
     */
    protected function seoLinkRelPrvNex($page, $totalPage, $routerName, $params = [])
    {
        if ($page <= $totalPage) {
            $args = $params;
            $args[] = $page + 1;
            $this->seo()->linkRel(url($routerName, $args), 'next');
        }
        if ($page > 1) {
            $args = $params;
            $args[] = $page - 1;
            $this->seo()->linkRel(url($routerName, $args), 'prev');
        }
    }

    protected function pageAssign($query, $limit)
    {

        if ($query instanceof \Closure) {
            $query = $query();
        }
        $count = is_numeric($query) ? $query : $query->count();
        $currentPage = $this->getRequest()->getParam('page', 1);

        $currentPage = max($currentPage, 1);
        $this->page = $currentPage;


        $totalPage = ceil($count / $limit);

        // 如果访问的页码超过总页码数，返回404
        if ($totalPage > 0 && $currentPage > $totalPage) {
            return $this->x404();
        }
        $this->assign('totalPage', $totalPage);;
        $this->assign('currentPage', $currentPage);
        return [
            $currentPage, $totalPage,
        ];
    }

    protected function copyright(): string
    {
        return YacCacheManager::getCopyright() ?? '';
    }

    /**
     * 获取所有后台变量的替换数组
     * 用于 SEO 模板变量替换
     * @return array 返回格式为 ['{VAR_NAME}' => 'value', ...]
     */
    protected function getVariableReplacements(): array
    {
        $variables = VariableModel::variables();
        $replacements = [];

        foreach ($variables as $varKey => $varValue) {
            $replacements['{' . $varKey . '}'] = htmlspecialchars($varValue);
        }

        return $replacements;
    }

    /**
     * 获取后台变量替换数组（原始值，用于JSON等不需要HTML转义的场景）
     * @return array
     */
    protected function getVariableReplacementsRaw(): array
    {
        $variables = VariableModel::variables();
        $replacements = [];

        foreach ($variables as $varKey => $varValue) {
            $replacements['{' . $varKey . '}'] = $varValue;  // 不转义
        }

        return $replacements;
    }

    protected function assignAppList()
    {
        // 使用YacCacheManager获取应用列表数据
        $appList = YacCacheManager::getAppList();
        $this->assign('appList', $appList);
    }


    /**
     * 渲染统一的错误页面，保持页面模板一致但返回不同状态码
     */
    private function renderErrorPage(int $statusCode): bool
    {
        // 标记为404页面，用于禁用统计代码（同模板复用）
        $this->is404Page = true;
	\Yaf_Registry::set('html:skip', true);
        // 设置状态码（默认404，也可以是410）
        if ($statusCode === 410) {
            header("HTTP/1.1 410 Gone");
            http_response_code(410);
            header("X-Robots-Tag: noindex, nofollow");
        } else {
            header("HTTP/1.1 404 Not Found");
            http_response_code(404);
        }
        try {
            // 获取站点基本信息
            $siteName = options('brand', '') ?: options('title', '007吃瓜');
            $favicon = options('favicon_ico', '/favicon.ico');
            $logoUrl = options('logo_url', '');
            $twitterSite = options('twitter_site', '@your_handle');

            // 生成404页面标题和描述
            $title = "页面走丢了 - {$siteName}";
            $description = "很抱歉，您访问的页面不存在。请返回首页或浏览我们的热门内容。";

            // 获取SEO模版并替换变量（404页面不输出结构化数据）
            $header = '';
            try {
                $header = SeoTplModel::seo_tpl('x404_header');
                // 移除所有结构化数据相关的内容
                $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>.*?<\/script>/is', '', $header);
                $header = preg_replace('/\{LD_JSON\}/', '', $header);

                // 添加面包屑结构化数据
                $permanent_domain = rtrim(options('siteUrl'), '/');
                $homeUrl = $permanent_domain . '/';
                $url = $permanent_domain . rtrim($this->getRequest()->getRequestUri(), '/') . '/';
                // LOGO 绝对 URL
                $logoAbs = $logoUrl;
                if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
                    $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
                }

                $replace = [
                    '{TITLE}' => htmlspecialchars($title),
                    '{DESCRIPTION}' => htmlspecialchars($description),
                    '{BRAND}' => htmlspecialchars($siteName),
                    '{FAVICON}' => $favicon,
                    '{LOGOURL}' => $logoAbs,
                    '{TWITTER_SITE}' => $twitterSite,
                ];
                $header = str_replace(array_keys($replace), array_values($replace), $header);
            } catch (\Exception $e) {
                // 如果SEO模板获取失败，使用简单的header
                $header = '<title>' . htmlspecialchars($title) . '</title>';
            }

            $hotTags = collect();

            // 获取推荐文章（随机获取8篇文章）
            $recommendedPosts = collect();
            try {
                $recommendedPosts = ContentsModel::queryRecommend();
            } catch (\Exception $e) {
                // 如果文章获取失败，使用空集合
                $recommendedPosts = collect();
            }

            // 设置数据到视图
            $this->assign('header', $header);
            $this->assign('hotTags', $hotTags);
            $this->assign('recommendedPosts', $recommendedPosts);

            $this->display('x404');
            return true;

        } catch (\Exception $e) {
            // 如果出现任何错误，显示简单的404页面
            $this->assign('header', '<title>页面走丢了 </title>');
            $this->assign('hotTags', collect());
            $this->assign('recommendedPosts', collect());
            $this->display('x404');
            return true;
        }
    }

    /**
     * 返回410 Gone状态码（文章已永久删除超过1个月）
     * 复用404页面显示，但状态码为410
     * @return bool
     */
    public function x410(): bool
    {
        // 公用模板，返回 410 状态码
        return $this->renderErrorPage(410);
    }

    public function x404(): bool
    {
        // 404 状态码固定为 404
        return $this->renderErrorPage(404);
    }

    public function displayJson($json): bool
    {
        $json = json_encode($json);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($json);
        return true;
    }

    public function showJson($data, $msg = 'ok', $code = 0): bool
    {
        return $this->displayJson([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 处理第一页重定向
     * 如果访问的是第一页且URL包含/page/1/，则重定向到无分页的URL
     *
     * @param string $routeName 路由名称（如 'home', 'category', 'tag.detail' 等）
     * @param array $routeParams 路由参数
     * @return bool 如果发生了重定向返回true，否则返回false
     */
    protected function handleFirstPageRedirect($routeName, $routeParams = [])
    {
        // 如果是第一页且URL包含/page/1/，则重定向到无分页的URL
        if ($this->page == 1 && strpos($this->getRequest()->getRequestUri(), '/1/') !== false) {
            \Yaf_Registry::set('html:skip', true);
            $targetUrl = url($routeName, $routeParams, false);
            if ($routeName == 'search') {
                $targetUrl .= '/';
            }
            header("Location: $targetUrl", true, 301);
            return true;
        }
        return false;
    }

    protected function setSocialUrls(string $ldJson): string
    {
        $variables = VariableModel::variables();
        $socialUrl = $variables['VAR_SOCIAL_URL'] ?? '';
        // 处理社交媒体URL数组
        $socialUrlArray = [];
        if (!empty($socialUrl)) {
            $socialUrls = array_map('trim', explode(',', $socialUrl));
            $socialUrls = array_filter($socialUrls); // 过滤空值
            $socialUrlArray = array_values($socialUrls); // 重新索引数组
        }
        //socialUrl 替换
//        $socialUrls = $this->getSocialUrls();


        //当设置的social_url 不为空时，将social_url 添加到ld_json中，为空时删除same_as的key
        if (!empty($socialUrls)) {
            return $this->setSocialUrlIntoLdJson($socialUrlArray, $ldJson);
        } else {
            return $this->correctSocialUrlLdJson($ldJson);
        }
//        // 为模板生成社交媒体URL字符串
//        $socialUrlReplacement = '';
//        if (!empty($socialUrlArray)) {
//            // 不添加引号，让模板处理引号格式
//            $escapedUrls = array_map(function ($url) {
//                return addslashes($url);
//            }, $socialUrlArray);
//            // 生成逗号分隔的URL字符串
//            $socialUrlReplacement = implode('","', $escapedUrls);
//        }
//        return $socialUrlReplacement;
    }

    /**
     * 修正结构化数据：因为模板是 "sameAs": ["{SOCIALURL}"] ,在social_url没有的时候需要将模板字段清空
     * @param string $htmlScript
     * @return string
     */
    protected function correctSocialUrlLdJson(string $htmlScript): string
    {
        //提取 JSON 内容
        if (!preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $htmlScript, $matches)) {
            return $htmlScript; // 没找到则原样返回
        }

        $jsonContent = trim($matches[1]);

        //解析 JSON
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $htmlScript; // JSON 不合法则直接返回原内容
        }

        //递归删除 sameAs
        $removeKeyRecursive = function (&$array, $keyToRemove) use (&$removeKeyRecursive) {
            if (!is_array($array)) return;
            foreach ($array as $key => &$value) {
                if ($key === $keyToRemove) {
                    unset($array[$key]);
                } elseif (is_array($value)) {
                    $removeKeyRecursive($value, $keyToRemove);
                }
            }
        };
        $removeKeyRecursive($data, 'sameAs');

        //重新编码为 JSON
        $cleanJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        //替换回原 script 块
        $newScript = preg_replace(
            '/(<script[^>]*type=["\']application\/ld\+json["\'][^>]*>)(.*?)(<\/script>)/is',
            '$1' . "\n" . $cleanJson . "\n" . '$3',
            $htmlScript
        );

        return $newScript;
    }

    protected function setSocialUrlIntoLdJson(array $sameAsUrls, string $json): string
    {
//        //提取 JSON 内容
//        if (!preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $json, $matches)) {
//            return $json; // 没找到则原样返回
//        }
//
//        $jsonContent = trim($matches[1]);
//
//        //解析 JSON
//        $data = json_decode($jsonContent, true);
//        if (json_last_error() !== JSON_ERROR_NONE) {
//            return $json; // JSON 不合法则直接返回原内容
//        }

        return preg_replace_callback(
            '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            function ($matches) use ($sameAsUrls) {
                $jsonContent = trim($matches[1]);
                $data = json_decode($jsonContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // JSON 解析失败则返回原内容
                    return $matches[0];
                }

                // 递归遍历并只修改存在 sameAs 的键
                $updateSameAsRecursive = function (&$array) use (&$updateSameAsRecursive, $sameAsUrls) {
                    if (!is_array($array)) return;

                    foreach ($array as $key => &$value) {
                        if ($key === 'sameAs') {
                            $array[$key] = $sameAsUrls;
                        } elseif (is_array($value)) {
                            $updateSameAsRecursive($value);
                        }
                    }
                };

                $updateSameAsRecursive($data);

                // 再转回 JSON，保持中文与 URL 不转义
                $cleanJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                return "<script type=\"application/ld+json\">\n" . $cleanJson . "\n</script>";
            },
            $json
        );
    }

    /**
     * 替换模板中的变量（统一方法，自动合并后台变量）
     * @param string $template 模板字符串
     * @param array $variables 变量数组（key不带花括号，如 ['BRAND' => 'xxx']）
     * @param bool $htmlEscape 是否HTML转义（false用于JSON，true用于HTML）
     * @return string
     */
    protected function replaceVariables(string $template, array $variables = [], bool $htmlEscape = false): string
    {
        // 1. 合并后台变量（带花括号，如 {VAR_BRAND}）
        $backendVars = $htmlEscape 
            ? $this->getVariableReplacements()      // HTML场景，转义
            : $this->getVariableReplacementsRaw();  // JSON场景，不转义
        
        // 2. 将传入的变量转换为带花括号格式（如 BRAND => {BRAND}）
        $runtimeVars = [];
        foreach ($variables as $k => $v) {
            $runtimeVars['{' . $k . '}'] = $v;
        }

        // 2.1 分页占位符规则：支持 {page} = " - 第x页"
        // 控制器已约定传入的 PAGE 形如 "第3页" 或空字符串
        if (isset($variables['PAGE'])) {
            $pageLabel = (string)$variables['PAGE'];
            $runtimeVars['{page}'] = $pageLabel !== '' ? (' - ' . $pageLabel) : '';
        }
        
        // 3. 合并：运行时变量优先级高于后台变量
        $allVars = array_merge($backendVars, $runtimeVars);
        
        // 4. 执行替换
        return str_replace(array_keys($allVars), array_values($allVars), $template);
    }

}