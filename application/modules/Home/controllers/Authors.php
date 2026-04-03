<?php


/**
 * 作者详情
 */
class AuthorsController extends WebController
{


    public function authorsAction()
    {

        $authorId = $this->getRequest()->getParam('id');
        if (empty($authorId)) {
            return $this->x404();
        }
        $author = UsersModel::find($authorId, ['uid', 'screenName']);
        if (empty($author)) {
            return $this->x404();
        }
        $screenName = $author->screenName;
        $this->limit = 15;
        $this->page = $this->getRequest()->getParam('page') ?? 1;

        // 处理第一页重定向
        if ($this->handleFirstPageRedirect('authors', [$authorId])) {
            return;
        }

        $query = ContentsModel::queryWebPost()->where('authorId', $authorId);

        $count = cached("author-list-count:{$authorId}")
            ->group('gp:author-list-count')
            ->chinese("WEB端作者列表分页缓存")
            ->fetchPhp(function () use ($query) {
                return $query->count();
            });

        $pageResult = $this->pageAssign($count, $this->limit);
        if ($pageResult === true) {
            return true;
        }
        list($this->page, $totalPage) = $pageResult;

        $list = cached('author-list-' . $authorId . ':' . $this->page)
            ->group('gp:author-list')
            ->chinese("WEB端作者列表缓存")
            ->fetchPhp(function () use ($query) {
                return $query->clone()
                    ->selectRaw('cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,fake_view,view')
                    ->with([
                        'categoryRelationships.category',
                        'fields',
                        'author'
                    ])
                    ->orderByDesc('created')
                    ->forpage($this->page, $this->limit)
                    ->get();
            });

        // 基础变量
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');
        $logoUrl = options('logo_url', '');
        $homeUrl = rtrim(options('siteUrl'), '/') . '/';
        $twitterSite = options('twitter_site', '@your_handle');
        $permanent_domain = rtrim(options('siteUrl'), '/');

        // 生成分页相关URL
        $canonical_url = $this->page > 1
            ? $permanent_domain . url('author.page', [$authorId, $this->page], false)
            : $permanent_domain . url('authors', [$authorId], false);

        $prev_link = '';
        $next_link = '';

        if ($this->page > 1) {
            if ($this->page == 2) {
                $prev_url = $permanent_domain . url('authors', [$authorId], false);
            } else {
                $prev_url = $permanent_domain . url('author.page', [$authorId, $this->page - 1], false);
            }
            $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
        }

        if ($this->page < $totalPage) {
            $next_url = $permanent_domain . url('author.page', [$authorId, $this->page + 1], false);
            $next_link = '<link rel="next" href="' . $next_url . '" />';
        }

        // 备注模板
        $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';
        $remark = SeoTplModel::seo_config('author_publish');
        $remarkVars = $this->parseRemarkVariables($remark);
        $titleTpl = $remarkVars['TITLE'] ?? '{AUTHOR_NAME}发布专栏 - {PAGE} | 畅享精彩合集资源 - {BRAND}';
        $descTpl = $remarkVars['DESCRIPTION'] ?? '{BRAND}作者{AUTHOR_NAME}的作品专栏{PAGE}...';
        $keywordsTpl = $remarkVars['KEYWORDS'] ?? '{AUTHOR_NAME},{AUTHOR_NAME}合集,{AUTHOR_NAME}专栏,黑料网,吃瓜网,成人视频,禁漫小说,{BRAND}';

        $title = $this->replaceVariables($titleTpl, ['AUTHOR_NAME' => $screenName, 'PAGE' => $pageLabel, 'BRAND' => $brand]);
        $description = $this->replaceVariables($descTpl, ['AUTHOR_NAME' => $screenName, 'PAGE' => $pageLabel, 'BRAND' => $brand]);
        $keywords = $this->replaceVariables($keywordsTpl, ['AUTHOR_NAME' => $screenName, 'BRAND' => $brand]);

        //过滤标题和字符
        $title = filter_pure_text($title);
        $description = filter_pure_text($description);

        // 获取SEO模版并替换变量
        $header = SeoTplModel::seo_tpl('author_publish');
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
            '{AUTHOR_NAME}' => htmlspecialchars($screenName),
            '{PAGE}' => $pageLabel,
        ];
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);
        // LD-JSON - 优先使用分页模板
        if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
            $ldJsonTpl = $remarkVars['LD_JSON_PAGE'];
        } else {
            $ldJsonTpl = $remarkVars['LD_JSON'] ?? '';
        }
        if (!empty($ldJsonTpl)) {
            // 生成作者URL
            $authorUrl = $permanent_domain . url('authors', [$authorId], false);
            // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
            $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                '{HOMEURL}' => $homeUrl,
                '{BRAND}' => $brand,
                '{LOGOURL}' => $logoAbs,
                '{CANONICAL}' => $canonical_url,
                '{TITLE}' => filter_pure_text($title),
                '{DESCRIPTION}' => filter_pure_text($description),
                '{AUTHOR_NAME}' => $screenName,
                '{AUTHOR_URL}' => $authorUrl,
                '{PAGE}' => '第' . $this->page . '页',
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
                        'about' => ['@type' => 'Thing', 'name' => $screenName],
                    ],
                ],
            ];
            $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
        }
        $header = str_replace('{LD_JSON}', $ld_json, $header);

        // 设置header到视图
        $this->assign('header', $header);
        $this->assign('lists', $list);
        $this->assign('currentPage', $this->page);
        $this->assign('totalPage', $totalPage);
        $this->assign('screenName', $screenName);
        $this->assign('PageNavigator', new PageNavigator($this->page, $totalPage, url_raw('author.page', [$authorId]), url_raw('authors', [$authorId])));
        $this->display('author');

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