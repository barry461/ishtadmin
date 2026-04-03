<?php

class TagController extends WebController
{

    public function listAction()
    {

        $this->limit = 100;
        $this->page = $this->getRequest()->getParam('page') ?? 1;

        // 处理第一页重定向
        if ($this->handleFirstPageRedirect('tag.list')) {
            return;
        }

        $taglist = cached('tags-list-new')
            ->group('gp:tags-list-new')
            ->chinese('WEB端标签列表缓存')
            ->fetchPhp(function () {
                return TagsModel::query()
                    ->whereHas('relationships', function ($query) {
                        // 只查询有关联公开文章的标签
                        $query->whereHas('content', function ($contentQuery) {
                            $contentQuery->where('status', ContentsModel::STATUS_PUBLISH)
                                ->where('type', ContentsModel::TYPE_POST)
                                ->where('created', '<', time())
                                ->where('app_hide', ContentsModel::APP_HIDE_NO)
                                ->where('web_show', ContentsModel::WEB_SHOW_YES)
                                ->where('is_slice', 1);
                        });
                    })
                    ->orderByDesc('id')
                    ->forPage($this->page, $this->limit)
                    ->get();
            });

        $pageResult = $this->pageAssign(
            TagsModel::query()->whereHas('relationships', function ($query) {
                $query->whereHas('content', function ($contentQuery) {
                    $contentQuery->where('status', ContentsModel::STATUS_PUBLISH)
                        ->where('type', ContentsModel::TYPE_POST)
                        ->where('created', '<', time())
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('web_show', ContentsModel::WEB_SHOW_YES)
                        ->where('is_slice', 1);
                });
            }),
            $this->limit
        );
        if ($pageResult === true) {
            return true;
        }
        list($this->page, $totalPage) = $pageResult;

        // 基础变量
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');
        $logoUrl = options('logo_url', '');
        $homeUrl = rtrim(options('siteUrl'), '/') . '/';
        $twitterSite = options('twitter_site', '@your_handle');

        // 生成分页相关URL（标签列表页不需要特殊编码处理）
        $permanent_domain = rtrim(options('siteUrl'), '/');
        $canonical_url = $this->page > 1
            ? $permanent_domain . url('tag.page', [$this->page], false)
            : $permanent_domain . url('tag.list', [], false);

        $prev_link = '';
        $next_link = '';

        if ($this->page > 1) {
            if ($this->page == 2) {
                $prev_url = $permanent_domain . url('tag.list', [], false);
            } else {
                $prev_url = $permanent_domain . url('tag.page', [$this->page - 1], false);
            }
            $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
        }

        if ($this->page < $totalPage) {
            $next_url = $permanent_domain . url('tag.page', [$this->page + 1], false);
            $next_link = '<link rel="next" href="' . $next_url . '" />';
        }

        // config 模板（tags_list）
        $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';
        $remark = SeoTplModel::seo_config('tags_list');
        $remarkVars = $this->parseRemarkVariables($remark);
        $titleTpl = $remarkVars['TITLE'] ?? '标签云导航 - {PAGE} - 全站热门吃瓜与黑料话题索引 | {BRAND}';
        $descTpl = $remarkVars['DESCRIPTION'] ?? '{BRAND}标签云页面集中展示全站所有热门标签...';
        $keywordsTpl = $remarkVars['KEYWORDS'] ?? '标签云,热门标签,...,{BRAND}';
        $title = $this->replaceVariables($titleTpl, ['PAGE' => $pageLabel, 'BRAND' => $brand]);
        $description = $this->replaceVariables($descTpl, ['PAGE' => $pageLabel, 'BRAND' => $brand]);
        $keywords = $this->replaceVariables($keywordsTpl, ['BRAND' => $brand]);

        // LOGO 绝对 URL
        $logoAbs = $logoUrl;
        if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
            $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
        }

        // 获取SEO模版并替换变量
        $header = SeoTplModel::seo_tpl('tags_list');
        // 清理模版中可能存在的空的/多余的 ld+json 开始标签
        $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $header);
        $replace = [
            '{TITLE}' => htmlspecialchars($title),
            '{DESCRIPTION}' => htmlspecialchars($description),
            '{KEYWORDS}' => htmlspecialchars($keywords),
            '{CANONICAL}' => $canonical_url,
            '{PREV}' => $prev_link,
            '{NEXT}' => $next_link,
            '{BRAND}' => htmlspecialchars($brand),
            '{FAVICON}' => $favicon,
            '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
            '{TWITTER_SITE}' => $twitterSite,
            '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
            '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
            '{HOMEURL}' => $homeUrl,
            '{PAGE}' => $pageLabel,
        ];
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);
        // 强制覆盖 canonical，确保与 URL 编码后一致
        $header = preg_replace('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', '', $header);
        $header = '<link rel="canonical" href="' . $canonical_url . '" />' . "\n" . $header;

        // LD-JSON - 优先使用分页模板
        if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
            $ldJsonTpl = $remarkVars['LD_JSON_PAGE'];
        } else {
            $ldJsonTpl = $remarkVars['LD_JSON'] ?? '';
        }
        if (!empty($ldJsonTpl)) {
            // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
            $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                '{HOMEURL}' => $homeUrl,
                '{BRAND}' => $brand,
                '{LOGOURL}' => $logoAbs,
                '{CANONICAL}' => $canonical_url,
                '{TITLE}' => $title,
                '{DESCRIPTION}' => $description,
                '{TAGS_URL}' => $permanent_domain . url('tag.list', [], false),
                '{PAGE}' => '第' . $this->page . '页'
            ]);
            $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTpl);
            $ld_json = $this->setSocialUrls($ld_json);
        } else {
            $ldData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'Organization',
                        '@id' => $homeUrl . '#org',
                        'name' => $brand,
                        'url' => $homeUrl,
                        'logo' => ['@type' => 'ImageObject', 'url' => $logoAbs],
                    ],
                    [
                        '@type' => 'WebSite',
                        '@id' => $homeUrl . '#website',
                        'url' => $homeUrl,
                        'name' => $brand,
                        'inLanguage' => 'zh-CN',
                        'publisher' => ['@id' => $homeUrl . '#org'],
                    ],
                    [
                        '@type' => 'CollectionPage',
                        '@id' => $canonical_url . '#collection',
                        'url' => $canonical_url,
                        'name' => $title,
                        'description' => $description,
                        'inLanguage' => 'zh-CN',
                        'isPartOf' => ['@id' => $homeUrl . '#website'],
                        'publisher' => ['@id' => $homeUrl . '#org'],
                    ],
                ],
            ];
            $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
        }
        // 兜底：header 不含 ld+json 则追加
        $header = str_replace('{LD_JSON}', $ld_json, $header);
        if (stripos($header, 'application/ld+json') === false) {
            $header .= "\n" . $ld_json;
        }

        // 设置header到视图
        $this->assign('header', $header);
        $this->assign('lists', $taglist);
        $this->assign('PageNavigator', new PageNavigator($this->page, $totalPage, url_raw('tag.page'), url_raw('tag.list')));
        $this->display('tag-list');
    }

    public function detailAction()
    {
        $tag = $this->getRequest()->getParam('tag');

        try {
            if (empty($tag)) {
                return $this->x404();
            }

            /** @var TagsModel $tagData */
            $tagData = TagsModel::where('name', $tag)->first();

            if (empty($tagData)) {
                return $this->x404();
            }

            $mid = $tagData->id;

            if (!isset($mid)) {
                return $this->x404();
            }

            $this->page = $this->getRequest()->getParam('page') ?? 1;

            // 处理第一页重定向
            if ($this->handleFirstPageRedirect('tag.detail', [$tag])) {
                return;
            }

            // $query = ContentsModel::queryWebPost()->where('tag_id', $mid);
            $query = ContentsModel::queryWebListPost()
                ->join('tag_relationships', 'tag_relationships.cid', '=', 'contents.cid')
                ->where('tag_relationships.tag_id', $mid);

            $countQuery = clone $query;
            $contentList = cached('tag-detail-' . $mid . '-page' . $this->page)
                ->group('gp:tag-detail')
                ->chinese("WEB端标签详情列表缓存")
                ->fetchPhp(function () use ($mid, $countQuery) {
                    return $countQuery
                        ->with(['tagRelationships.tag', 'fields', 'author'])
                        ->whereHas('tagRelationships', function ($countQuery) use ($mid) {
                            $countQuery->where('tag_id', $mid);
                        })
                        ->orderByDesc('contents.created')
                        ->forPage($this->page, $this->limit)
                        ->get();
                });
            //空标签返回404
            if (empty($contentList)) {
                return $this->x404();
            }

            $pageResult = $this->pageAssign($query, $this->limit);
            if ($pageResult === true) {
                return true;
            }
            list($this->page, $totalPage) = $pageResult;

            if ($totalPage == 0) {
                return $this->x404();
            }

            // 基础变量
            $brand = options('brand', '') ?: options('title', '007吃瓜');
            $favicon = options('favicon_ico', '/favicon.ico');
            $logoUrl = options('logo_url', '');
            $homeUrl = rtrim(options('siteUrl'), '/') . '/';
            $twitterSite = options('twitter_site', '@your_handle');

            // 生成分页相关URL（标签名称会自动进行URL编码）
            $permanent_domain = rtrim(options('siteUrl'), '/');
            $canonical_url = $this->page > 1
                ? $permanent_domain . url('tag_detail.page', [$tag, $this->page], false)
                : $permanent_domain . url('tag.detail', [$tag], false);

            $prev_link = '';
            $next_link = '';

            if ($this->page > 1) {
                if ($this->page == 2) {
                    $prev_url = $permanent_domain . url('tag.detail', [$tag], false);
                } else {
                    $prev_url = $permanent_domain . url('tag_detail.page', [$tag, $this->page - 1], false);
                }
                $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
            }

            if ($this->page < $totalPage) {
                $next_url = $permanent_domain . url('tag_detail.page', [$tag, $this->page + 1], false);
                $next_link = '<link rel="next" href="' . $next_url . '" />';
            }

            // remark 模板（tag_detail）
            $tagName = $tagData->name;
            $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';
            $remark = SeoTplModel::seo_config('tag_detail');
            $remarkVars = $this->parseRemarkVariables($remark);
            $titleTpl = $remarkVars['TITLE'] ?? '{TAG_NAME} - {PAGE} | 热门吃瓜黑料合集每日更新 - {BRAND}';
            $descTpl = $remarkVars['DESCRIPTION'] ?? '「{TAG_NAME}」标签专题{PAGE}聚合本站相关内容...';
            $keywordsTpl = $remarkVars['KEYWORDS'] ?? '{TAG_NAME},{TAG_NAME}合集,{TAG_NAME}专题,{TAG_NAME}最新,热门{TAG_NAME},黑料网,吃瓜网,成人视频,禁漫小说,{BRAND}';
            $title = $this->replaceVariables($titleTpl, ['TAG_NAME' => $tagName, 'PAGE' => $pageLabel, 'BRAND' => $brand]);
            $description = $this->replaceVariables($descTpl, ['TAG_NAME' => $tagName, 'PAGE' => $pageLabel]);
            $keywords = $this->replaceVariables($keywordsTpl, ['TAG_NAME' => $tagName, 'BRAND' => $brand]);

            // 获取SEO模版并替换变量
            $header = SeoTplModel::seo_tpl('tag_detail');
            // 清理模版中可能存在的空的/多余的 ld+json 开始标签
            $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $header);
            // LOGO 绝对 URL
            $logoAbs = $logoUrl;
            if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
                $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
            }

            $replace = [
                '{TITLE}' => htmlspecialchars(filter_pure_text($title)),
                '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
                '{KEYWORDS}' => htmlspecialchars($keywords),
                '{CANONICAL}' => $canonical_url,
                '{PREV}' => $prev_link,
                '{NEXT}' => $next_link,
                '{BRAND}' => htmlspecialchars($brand),
                '{FAVICON}' => $favicon,
                '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
                '{TWITTER_SITE}' => $twitterSite,
                '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
                '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
                '{HOMEURL}' => $homeUrl,
                '{TAG_NAME}' => htmlspecialchars($tagName),
                '{PAGE}' => $pageLabel,
            ];
            // 合并所有后台变量到替换数组（系统变量优先级更高）
            $replace = array_merge($this->getVariableReplacements(), $replace);
            $header = str_replace(array_keys($replace), array_values($replace), $header);
            // 强制覆盖 canonical，确保与 URL 编码后一致
            $header = preg_replace('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', '', $header);
            $header = '<link rel="canonical" href="' . $canonical_url . '" />' . "\n" . $header;

            // LD-JSON - 优先使用分页模板
            if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
                $ldJsonTpl = $remarkVars['LD_JSON_PAGE'];
            } else {
                $ldJsonTpl = $remarkVars['LD_JSON'] ?? '';
            }

            //过滤非字符
//        $title = filter_pure_text($title);
//        $description = filter_pure_text($description);
            if (!empty($ldJsonTpl)) {
                // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
                $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                    '{HOMEURL}' => $homeUrl,
                    '{BRAND}' => $brand,
                    '{LOGOURL}' => $logoAbs,
                    '{CANONICAL}' => $canonical_url,
                    '{TITLE}' => filter_pure_text($title),
                    '{DESCRIPTION}' => filter_pure_text($description),
                    '{TAG_NAME}' => $tagName,
                    '{PAGE}' => '第' . $this->page . '页',
                    '{TAG_URL}' => $permanent_domain . url('tag.detail', [$tag], false),
                ]);
                $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTpl);
                $ld_json = $this->setSocialUrls($ld_json);

            } else {
                $ldData = [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        [
                            '@type' => 'Organization',
                            '@id' => $homeUrl . '#org',
                            'name' => $brand,
                            'url' => $homeUrl,
                            'logo' => ['@type' => 'ImageObject', 'url' => $logoAbs],
                        ],
                        [
                            '@type' => 'WebSite',
                            '@id' => $homeUrl . '#website',
                            'url' => $homeUrl,
                            'name' => $brand,
                            'inLanguage' => 'zh-CN',
                            'publisher' => ['@id' => $homeUrl . '#org'],
                        ],
                        [
                            '@type' => 'CollectionPage',
                            '@id' => $canonical_url . '#collection',
                            'url' => $canonical_url,
                            'name' => filter_pure_text($title),
                            'description' => filter_pure_text($description),
                            'inLanguage' => 'zh-CN',
                            'isPartOf' => ['@id' => $homeUrl . '#website'],
                            'publisher' => ['@id' => $homeUrl . '#org'],
                            'about' => ['@type' => 'Thing', 'name' => $tagName],
                        ],
                    ],
                ];
                $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
            }
            $header = str_replace('{LD_JSON}', $ld_json, $header);
            if (stripos($header, 'application/ld+json') === false) {
                $header .= "\n" . $ld_json;
            }

            // 设置header到视图
            $this->assign('header', $header);
            $this->assign('lists', $contentList);
            $this->assign('currentPage', $this->page);
            $this->assign('totalPage', $totalPage);
            $this->assign('slugname', $tag);

            $this->assign('PageNavigator', new PageNavigator($this->page, $totalPage, url_raw('tag_detail.page', [$tag]), url_raw('tag.detail', [$tag])));

            $this->display('tag-detail');
        } catch (\Throwable $th) {
            wf('出现异常', $th->getMessage());
            return $this->x404();
        }
    }

    private function parseRemarkVariables($remark)
    {
        $vars = [];
        if (empty($remark)) return $vars;

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