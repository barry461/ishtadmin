<?php

class HomeController extends WebController
{


    public function indexAction()
    {
        try {

            if ($this->getRequest()->get('s') !== null) {
                return $this->redirect(url('search', [$this->getRequest()->get('s')]));
            }
            $this->page = $this->getRequest()->getParam('page') ?? 1;


            $table = \Yaf\Registry::get('database')->prefix;
            $fullTable = $table . 'contents';
            $query = ContentsModel::queryWebListPost()
                ->where('is_home', 1);

            $list = cached('content:home-' . $this->page)
                ->group(ContentsModel::GP_HOME_CONTENT_LIST)
                ->chinese(ContentsModel::CN_HOME_CONTENT_LIST)
                ->fetchPhp(function () use ($fullTable, $query) {
                    return $query
                        ->clone()
                        ->with([
                            'categoryRelationships.category',
                            'fields',
                            'author:uid,mail,screenName',
                        ])
                        ->orderByDesc('home_top')
                        ->orderByDesc('created')
                        ->forPage($this->page, $this->limit)
                        ->get();
                });
            $count = cached('content:home:count')
                ->group(ContentsModel::GP_HOME_CONTENT_LIST_COUNT)
                ->chinese(ContentsModel::CN_HOME_CONTENT_LIST_COUNT)
                ->fetchPhp(function () use ($query) {
                    return $query->count();
                });

            $pageResult = $this->pageAssign($count, $this->limit);
            if ($pageResult === true) {
                return true;
            }
            list($this->page, $totalPage) = $pageResult;


            $permanent_domain = rtrim(options('siteUrl'), '/');

            // 当访问第一页且 URL 包含 /page/1/ 时，做 301 跳转到主域名
            if ($this->page == 1) {
                if ($this->handleFirstPageRedirect('home')) {
                    return true;
                }
            }

            // canonical 与实际 URL 规则保持一致：第一页为主页，其它页为 /page/{n}/
            $canonical_url = $permanent_domain . (
                $this->page > 1
                    ? url('home.page', ['page' => $this->page], false)
                    : url('home', [], false)
                );


            $header = SeoTplModel::seo_tpl("index_header");

            // 按备注规则设置品牌与主页
            $brand = options('brand', '') ?: options('title', '');
            $homeUrl = options('siteUrl', 'https://007cg1.com/');
            $homeHost = parse_url($homeUrl, PHP_URL_HOST) ?: $homeUrl;

            // 站点资源（图标/Logo 等依然从后台配置读取）
            $favicon = options('favicon_ico', '/favicon.ico');
            $logoUrl = options('logo_url', '');

            // 生成分页相关URL
            $prev_url = '';
            if ($this->page > 1) {
                if ($this->page == 2) {
                    $prev_url = $permanent_domain . url('home', [], false);
                } else {
                    $prev_url = $permanent_domain . url('home.page', ['page' => $this->page - 1], false);
                }
            }
            $next_url = $this->page < $totalPage ? $permanent_domain . url('home.page', ['page' => $this->page + 1], false) : '';

            // 从SEO模板配置中获取变量定义
            $seoRemark = SeoTplModel::seo_config('index_header');
            $remarkVars = $this->parseRemarkVariables($seoRemark);

            // 生成标题/描述/关键词（严格按配置中变量规则替换）
            $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';

            // 使用配置中的TITLE模板
            $titleTemplate = $remarkVars['TITLE'] ?? '{BRAND} - 黑料吃瓜视频｜成人爆料合集｜明星黑料｜宅男必备每日更新热门资源 - {PAGE}';
            $title = $this->replaceVariables($titleTemplate, [
                'BRAND' => $brand,
                'PAGE' => $pageLabel
            ]);

            // 使用配置中的DESCRIPTION模板
            $descriptionTemplate = $remarkVars['DESCRIPTION'] ?? '007吃瓜网专注收录最新黑料吃瓜视频与成人爆料合集{PAGE}，涵盖热门榜单、OnlyFans精选、男同吃瓜、AV鉴赏、校园爆料、明星黑料、伦理禁忌等内容，访问007吃瓜官网 007cg1.com 免费在线观看，畅享高清秒播，每日持续更新，持续为宅男提供全网最全的吃瓜天堂。';
            $description = $this->replaceVariables($descriptionTemplate, [
                'PAGE' => $pageLabel,
                "BRAND" => $brand,
            ]);

            // 使用配置中的KEYWORDS模板
            $keywordsTemplate = $remarkVars['KEYWORDS'] ?? '{BRAND},黑料,成人爆料,OnlyFans,男同,AV鉴赏,明星黑料,校园吃瓜,每日更新,热门资源,宅男必备,福利视频,成人视频,爆料合集';
            $keywords = $this->replaceVariables($keywordsTemplate, [
                'BRAND' => $brand
            ]);

            // 生成符合配置说明的 LD-JSON（使用配置中的字段）
            // 确保 LD-JSON 中的 logo 使用绝对 URL
            $logoAbs = $logoUrl;
            if ($logoAbs) {
                if (!preg_match('#^https?://#i', $logoAbs)) {
                    $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
                }
            }
            //过滤非字符
//            $title = filter_pure_text($title);
//            $description = filter_pure_text($description);




            // 使用配置中的LD_JSON模板，如果存在的话
            // 如果是分页（page > 1），优先使用 LD_JSON_PAGE 模板
            if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
                $ldJsonTemplate = $remarkVars['LD_JSON_PAGE'];
            } else {
                $ldJsonTemplate = $remarkVars['LD_JSON'] ?? '';
            }

            if (!empty($ldJsonTemplate)) {
                // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
                $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                    '{BRAND}' => $brand,
                    '{TITLE}' => filter_pure_text($title),
                    '{DESCRIPTION}' => filter_pure_text($description),
                    '{HOMEURL}' => $homeUrl,
                    '{CANONICAL}' => $canonical_url,
                    '{LOGOURL}' => $logoAbs,
                    '{PAGE}' => $pageLabel,
                ]);

                $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTemplate);

                //如果有socialUrl就设置 没有就不设置
                $ld_json = $this->setSocialUrls($ld_json);
            } else {
                // 使用默认的LD-JSON结构
                $ldData = [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        [
                            '@type' => 'WebSite',
                            'name' => $brand,
                            'url' => $homeUrl,
                            'inLanguage' => 'zh-CN',
                            'publisher' => [
                                '@type' => 'Organization',
                                'name' => $brand,
                                'logo' => [
                                    '@type' => 'ImageObject',
                                    'url' => $logoAbs,
                                ],
                            ],
                        ],
                        [
                            '@type' => 'CollectionPage',
                            'name' => filter_pure_text($title),
                            'url' => $canonical_url,
                            'description' => filter_pure_text($description),
                            'inLanguage' => 'zh-CN',
                            'isPartOf' => [
                                '@type' => 'WebSite',
                                'name' => $brand,
                                'url' => $homeUrl,
                            ],
                        ],
                    ],
                ];
                $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
            }

            // 移除模版中所有现有 <title>，再插入规则化标题，避免被其他来源覆盖
            $header = preg_replace('/<title>.*?<\\/title>/is', '', $header);
            $header = '<title>' . htmlspecialchars($title) . '</title>' . "\n" . $header;

            $replace = [
                '{TITLE}' => htmlspecialchars(filter_pure_text($title)),
                '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
                '{KEYWORDS}' => htmlspecialchars($keywords),
                '{CANONICAL}' => $canonical_url,
                '{FAVICON}' => $favicon,
                '{BRAND}' => htmlspecialchars($brand),
                '{HOMEURL}' => $homeUrl,
                '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
                '{PREV}' => $prev_url ? '<link rel="prev" href="' . $prev_url . '" />' : '',
                '{NEXT}' => $next_url ? '<link rel="next" href="' . $next_url . '" />' : '',
                '{LD_JSON}' => $ld_json,
                '{PAGE}' => $pageLabel,
                '{TWITTER_SITE}' => options('twitter_site', ''),
                '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
                '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
            ];

            // 合并所有后台变量到替换数组（系统变量优先级更高）
            $replace = array_merge($this->getVariableReplacements(), $replace);
            $header = str_replace(array_keys($replace), array_values($replace), $header);
            $this->assign('header', $header);
            $this->assign('lists', $list);
            $this->assign('currentPage', $this->page);
            $this->assign('totalPage', $totalPage);
            $this->assign('PageNavigator', new PageNavigator($this->page, $totalPage, url_raw('home.page'), url_raw('home')));
            //$this->seoLinkRelPrvNex($this->page, $totalPage, 'home.page');

            // 传递特定变量给模板（用于首页头部显示）
            $allVariables = VariableModel::variables();
            $this->assign('homeHeaderTitle', $allVariables['VAR_HOME_HEADER_TITLE'] ?? '');
            $this->assign('homeHeaderDescription', $allVariables['VAR_HOME_HEADER_DESCRIPTION'] ?? '');

            $this->display('index');
        } catch (\Throwable $e) {
            wf('首页出现异常', $e->getMessage(), true, '/storage/logs/home-log.log');
            return $this->x404();
        }

    }

    /**
     * 解析SEO模板配置中的变量定义
     * @param string $remark 配置内容（config字段）
     * @return array 变量数组
     */
    private function parseRemarkVariables($remark)
    {
        $vars = [];
        if (empty($remark)) {
            return $vars;
        }

        $scripts = [];
        if (preg_match_all("/\{LD_JSON\}\s*=\s*<script(.*?)<\/script>/is", $remark, $scripts)) {
            $vars["LD_JSON"] = "<script" . $scripts[1][0] . '</script>';
            $remark = str_replace($scripts[0][0], '', $remark);
        }

        // 解析 LD_JSON_PAGE 模板
        if (preg_match_all("/\{LD_JSON_PAGE\}\s*=\s*<script(.*?)<\/script>/is", $remark, $scripts)) {
            $vars["LD_JSON_PAGE"] = "<script" . $scripts[1][0] . '</script>';
            $remark = str_replace($scripts[0][0], '', $remark);
        }

        // 按行分割备注内容
        $lines = explode("\n", $remark);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !strpos($line, '=')) {
                continue;
            }

            // 解析 "变量名 = 值" 格式
            if (preg_match('/^\{([^}]+)\}\s*=\s*(.+)$/', $line, $matches)) {
                $varName = trim($matches[1]);
                $varValue = trim($matches[2]);
                $vars[$varName] = $varValue;
            }
        }

        return $vars;
    }

}