<?php
/**
 * CategoriesController.php
 * @author  chenmoyuan
 */
class CategoriesController extends WebController
{


    

    //分类下的文章列表
    public function categoryAction()
    {
        try {
            $slug = $this->getRequest()->getParam('slug');
            if (empty($slug)) {
                return $this->x404();
            }
            // 处理第一页重定向
            if ($this->handleFirstPageRedirect('category', [$slug])) {
                return;
            }
            // yac()->delete("category:{$slug}");
            /** @var CategoriesModel $category */
            $category = yac()
            ->fetch("category:{$slug}" , function () use ($slug){
                return CategoriesModel::query()->where('slug', $slug)->first();
            });
            if (empty($category)) {
                return $this->x404();
            }

            $this->limit = 30;
            $this->page = $this->getRequest()->getParam('page') ?? 1;
            $table = Yaf_Registry::get('database')->prefix;
            $fullTable = $table.'contents';
            $categoryId = $category->id ?? null;
            $key = 'content:category-list-'.$categoryId.'_'.$this->page;
            $ads_ids = redis()->sMembers('rk:contents:ads:ids');

            $query = ContentsModel::queryWebListPost()
                ->join('category_relationships', 'category_relationships.cid', '=', 'contents.cid')
                ->where('category_relationships.category_id', $categoryId)
                ->when(!empty($ads_ids), function ($q) use ($ads_ids) {
                    $q->whereNotIn('category_relationships.cid', $ads_ids);
                });


            $list = cached($key)
                ->group(CategoriesModel::GP_CONTENT_CATEGORY_LIST)
                ->chinese(CategoriesModel::CN_CONTENT_CATEGORY_LIST)
                ->fetchPhp(function () use ($category, $fullTable, $query) {
                    if($category->sort_column){
                        return $query->clone()
                            ->with([
                                'categoryRelationships.category',
                                'fields',
                                'author:uid,mail,screenName',
                            ])
                            ->orderByDesc($category->sort_column)
                            ->orderByDesc('created')
                            ->forPage($this->page, $this->limit)
                            ->get();
                    }else{
                        return $query->clone()
                            ->with([
                                'categoryRelationships.category',
                                'fields',
                                'author:uid,mail,screenName',
                            ])
                            ->orderByDesc('created')
                            ->forPage($this->page, $this->limit)
                            ->get();
                    }
                });

            $count = cached("content:category-list-".$categoryId)
                ->group(CategoriesModel::GP_CONTENT_CATEGORY_LIST_COUNT)
                ->chinese(CategoriesModel::CN_CONTENT_CATEGORY_LIST_COUNT)
                ->fetchPhp(function () use ($query) {
                    return $query->count('contents.cid');
                });

            
            
            $pageResult = $this->pageAssign($count, $this->limit);
            if ($pageResult === true) {
                return true;
            }
            list($this->page, $totalPage) = $pageResult;
            // canonical
            $header = SeoTplModel::seo_tpl('article_category_header');
            // 清理模版中可能存在的空的/多余的 ld+json 开始标签，避免把后续<link>包进脚本里
            $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $header);
            $permanent_domain = rtrim(options('siteUrl'), '/');
            if ($this->page > 1) {
                $canonical_url = $permanent_domain . url('category.page', [$category->slug, $this->page], false);
            } else {
                $canonical_url = $permanent_domain . url('category', [$category->slug], false);
            }
            // prev/next
            $prev_link = '';
            $next_link = '';
            if ($this->page > 1) {
                if ($this->page == 2) {
                    $prev_url = $permanent_domain . url('category', [$category->slug], false);
                } else {
                    $prev_url = $permanent_domain . url('category.page', [$category->slug, $this->page - 1], false);
                }
                $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
            }
            if ($this->page < $totalPage) {
                $next_url = $permanent_domain . url('category.page', [$category->slug, $this->page + 1], false);
                $next_link = '<link rel="next" href="' . $next_url . '" />';
            }

            // 基础变量（来自配置）
            $brand = options('brand', '') ?: options('title', '007吃瓜');
            $homeUrl = rtrim(options('siteUrl'), '/') . '/';
            $favicon = options('favicon_ico', '/favicon.ico');
            $logoUrl = options('logo_url', '');
            $twitterSite = options('twitter_site', '@your_handle');

            $categoryName = $category->name;
            $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';

            // 读取 config 并解析模板
            $remark = SeoTplModel::seo_config('article_category_header');
            $remarkVars = $this->parseRemarkVariables($remark);
            $titleTpl = $remarkVars['TITLE'] ?? '{CATEGORY} - 吃瓜黑料分类专题｜最新爆料合集持续更新 - {PAGE}｜{BRAND}';
            $descTpl = $remarkVars['DESCRIPTION'] ?? '{CATEGORY}专题{PAGE}汇集全网最新爆料与黑料吃瓜内容，涵盖明星八卦、校园事件、网红翻车、OnlyFans流出、伦理禁忌、男同吃瓜、AV鉴赏等热门话题，聚合精品视频与实锤图文，访问007cg1.com 畅享免费在线观看，高清秒播，每日持续更新，让你不错过任何劲爆瓜料。';
            $keywordsTpl = $remarkVars['KEYWORDS'] ?? '{CATEGORY},{CATEGORY}专题,热门爆料,黑料吃瓜,明星八卦,校园事件,网红翻车,实锤,OnlyFans合集,伦理禁忌,男同吃瓜,AV鉴赏,{BRAND}';

            $replaceVars = [
                'CATEGORY' => $categoryName,
                'PAGE' => $pageLabel,
                'BRAND' => $brand,
            ];
            // 先根据全局 SEO 模板生成默认 TDK（兜底）
            $title = $this->replaceVariables($titleTpl, $replaceVars);
            $description = $this->replaceVariables($descTpl, $replaceVars);
            $keywords = $this->replaceVariables($keywordsTpl, $replaceVars);

            // 分类若填写了自己的 TDK，则优先使用分类里的内容（同样支持 {page}/{PAGE}/{CATEGORY}/{BRAND} 等占位符）
            if (!empty($category->seo_title)) {
                $title = $this->replaceVariables($category->seo_title, $replaceVars);
            }
            if (!empty($category->seo_description)) {
                $description = $this->replaceVariables($category->seo_description, $replaceVars);
            }
            if (!empty($category->seo_keywords)) {
                $keywords = $this->replaceVariables($category->seo_keywords, $replaceVars);
            }

            //过滤非字符
//            $title = filter_pure_text($title);
//            $description = filter_pure_text($description);
            // LOGO 绝对化
            $logoAbs = $logoUrl;
            if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
                $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
            }
            // LD-JSON - 优先使用分页模板
            if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
                $ldJsonTemplate = $remarkVars['LD_JSON_PAGE'];
            } else {
                $ldJsonTemplate = $remarkVars['LD_JSON'] ?? '';
            }
            
            if (!empty($ldJsonTemplate)) {
                // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
                $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                    '{HOMEURL}' => $homeUrl,
                    '{BRAND}' => $brand,
                    '{LOGOURL}' => $logoAbs,
                    '{CANONICAL}' => $canonical_url,
                    '{TITLE}' => filter_pure_text($title),
                    '{DESCRIPTION}' => filter_pure_text($description),
                    '{CATEGORY}' => $categoryName,
                    '{CATEGORY_URL}' => $permanent_domain . url('category', [$category->slug], false),
                    '{PAGE}' => $this->page > 1 ? "第{$this->page}页" : '',
                ]);
                
                // 执行变量替换
                $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTemplate);

                $ld_json = $this->setSocialUrls($ld_json);

            } else {
                // 保底结构
                $ldData = [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        [
                            '@type' => 'Organization',
                            '@id' => $homeUrl. '#org',
                            'name' => $brand,
                            'url' => $homeUrl,
                            'logo' => [ '@type' => 'ImageObject', 'url' => $logoAbs ],
                        ],
                        [
                            '@type' => 'WebSite',
                            '@id' => $homeUrl . '#website',
                            'url' => $homeUrl,
                            'name' => $brand,
                            'inLanguage' => 'zh-CN',
                            'publisher' => [ '@id' => $homeUrl . '#org' ],
                        ],
                        [
                            '@type' => 'CollectionPage',
                            '@id' => $canonical_url . '#collection',
                            'url' => $canonical_url,
                            'name' => filter_pure_text($title),
                            'description' => filter_pure_text($description),
                            'inLanguage' => 'zh-CN',
                            'isPartOf' => [ '@id' => $homeUrl . '#website' ],
                            'publisher' => [ '@id' => $homeUrl . '#org' ],
                            'about' => [ '@type' => 'Thing', 'name' => $categoryName ],
                        ],
                    ],
                ];
                $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
            }

            // 替换头部模板
            $replace = [
                '{CANONICAL}' => $canonical_url,
                '{TITLE}' => htmlspecialchars(filter_pure_text($title)),
                '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
                '{KEYWORDS}' => htmlspecialchars($keywords),
                '{FAVICON}' => $favicon,
                '{BRAND}' => htmlspecialchars($brand),
                '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
                '{TWITTER_SITE}' => $twitterSite,
                '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
                '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
                '{HOMEURL}' => $homeUrl,
                '{PREV}' => $prev_link,
                '{NEXT}' => $next_link,
                '{CATEGORY}' => htmlspecialchars($categoryName),
                '{PAGE}' => $pageLabel,
                '{LD_JSON}' => $ld_json,
            ];
            // 合并所有后台变量到替换数组（系统变量优先级更高）
            $replace = array_merge($this->getVariableReplacements(), $replace);
            $header = str_replace(array_keys($replace), array_values($replace), $header);
            // 兜底：如果header中没有 ld+json，则追加
            if (stripos($header, 'application/ld+json') === false) {
                $header .= "\n" . $ld_json;
            }

            $this->assign('header', $header);

            // 列表与分页
            $this->assign('lists', $list);
            $this->assign('currentPage', $this->page);
            $this->assign('totalPage', $totalPage);
            $this->assign('meta', $category);
            $this->assign('PageNavigator', new PageNavigator($this->page, $totalPage, url_raw('category.page' , [$slug]), url_raw('category', [$slug])));
            $this->assign('name', $category->description);
//            $this->seoLinkRelPrvNex($this->page, $totalPage, 'category.page',[$slug]);

            $this->display('index');
        } catch (\Throwable $th) {
            wf('出现异常', $th->getMessage());
            return $this->x404();
        }
    }

    /**
     * 解析remark变量
     */
    private function parseRemarkVariables($remark)
    {
        $vars = [];
        if (empty($remark)) return $vars;

        $scripts = [];
        if(preg_match_all("/\{LD_JSON\}\s*=\s*<script(.*?)<\/script>/is", $remark, $scripts)){
            $vars["LD_JSON"] = "<script".$scripts[1][0].'</script>';
            $remark = str_replace($scripts[0][0], '', $remark);
        }
           // 解析 LD_JSON_PAGE 模板
        if (preg_match_all("/\{LD_JSON_PAGE\}\s*=\s*<script(.*?)<\/script>/is", $remark, $scripts)) {
            $vars["LD_JSON_PAGE"] = "<script" . $scripts[1][0] . '</script>';
            $remark = str_replace($scripts[0][0], '', $remark);
        }

        $lines = explode("\n", $remark);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !strpos($line, '=')) continue;
            if (preg_match('/^\{([^}]+)\}\s*=\s*(.+)$/', $line, $m)) {
                $vars[trim($m[1])] = trim($m[2]);
            }
        }
        return $vars;
    }

}