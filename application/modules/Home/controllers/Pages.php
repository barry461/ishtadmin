<?php

/**
 * PagesController.php 单页
 * @author  chenmoyuan
 */
class PagesController extends WebController
{


    //单页
    public function slugAction()
    {
        $slug = $this->getRequest()->getParam('slug');

        // var_dump($slug);die();
        if (!$slug) {
            return $this->x404();
        }

        /** @var ContentsModel $content */
        $content = ContentsModel::where('slug', $slug)
            ->where('type', ContentsModel::TYPE_PAGE)
            ->whereIn('status', [ContentsModel::STATUS_PUBLISH, ContentsModel::STATUS_HIDDEN])
            ->first();

        if (empty($content)) {
            return $this->x404();
        }

        if ($content->fieldValue("redirect")) {
            header("Location:" . replace_share($content->fieldValue("redirect")));
            return false;
        }

        $content->load(['fields', 'author']);
        $content->loadTagWithCategory();
        $content->loadMarkdown();
        // echo json_encode($content);die();

        // 基础站点信息
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');
        $logoUrl = options('logo_url', '');
        $homeUrl = rtrim(options('siteUrl'), '/') . '/';
        $twitterSite = options('twitter_site', '@your_handle');
        $permanent_domain = rtrim(options('siteUrl'), '/');
        $canonical_url = $permanent_domain . $content->url()->getPath();

        // config 模板读取与选择（支持按 _{slug} 后缀区分TDK）
        $remark = SeoTplModel::seo_config('page_header');
        $vars = $this->parseRemarkVariables($remark);
        // 按后缀分组
        $grouped = [];
        foreach ($vars as $k => $v) {
            if (strpos($k, '_') !== false) {
                $pos = strrpos($k, '_');
                $base = substr($k, 0, $pos);
                $suffix = substr($k, $pos + 1);
                $grouped[$suffix][$base] = $v;
            } else {
                $grouped['__default'][$k] = $v;
            }
        }
        $chosen = $grouped[$slug] ?? ($grouped['__default'] ?? []);

        // 标题/描述/关键词模板（兼容 KEYWORDS 与 KEYWORDS1 命名）
        $titleTpl = $chosen['TITLE'] ?? '';
        if ($titleTpl === '' && isset($vars['TITLE'])) {
            $titleTpl = $vars['TITLE'];
        }
        $keywordsTpl = $chosen['KEYWORDS'] ?? ($chosen['KEYWORDS1'] ?? ($vars['KEYWORDS'] ?? ($vars['KEYWORDS1'] ?? '')));
        $descTpl = $chosen['DESCRIPTION'] ?? ($vars['DESCRIPTION'] ?? '');

        // 变量替换
        $title = $this->replaceVariables($titleTpl, ['BRAND' => $brand]);
        $description = $this->replaceVariables($descTpl, ['BRAND' => $brand]);
        $keywords = $this->replaceVariables($keywordsTpl, ['BRAND' => $brand]);

        // LOGO 绝对 URL
        $logoAbs = $logoUrl;
        if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
            $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
        }

        // 头部模板替换
        $header = SeoTplModel::seo_tpl('page_header');
        // 清理模版中可能存在的空的/多余的 ld+json 开始标签
        $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $header);
        $replace = [
            '{TITLE}' => htmlspecialchars(filter_pure_text($title)),
            '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
            '{KEYWORDS}' => htmlspecialchars($keywords),
            '{CANONICAL}' => $canonical_url,
            '{FAVICON}' => $favicon,
            '{BRAND}' => htmlspecialchars($brand),
            '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
            '{TWITTER_SITE}' => $twitterSite,
            '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
            '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
            '{HOMEURL}' => $homeUrl,
        ];
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);
        // LD-JSON 优先用 remark 中的 LD_JSON
        $ldJsonTpl = $vars['LD_JSON'] ?? '';
//        //过滤非字符
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
                '{PAGE_NAME}' => $content->title,
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
                        '@type' => 'WebPage',
                        '@id' => $canonical_url . '#webpage',
                        'url' => $canonical_url,
                        'name' => filter_pure_text($title),
                        'description' => filter_pure_text($description),
                        'inLanguage' => 'zh-CN',
                        'isPartOf' => ['@id' => $homeUrl . '#website'],
                        'publisher' => ['@id' => $homeUrl . '#org'],
                    ],
                ],
            ];
            $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
        }

        $header = str_replace('{LD_JSON}', $ld_json, $header);
        if (stripos($header, 'application/ld+json') === false) {
            $header .= "\n" . $ld_json;
        }

        theme_options()->useCardView = 0;
        $this->assign('header', $header);
        $this->assign('content', $content);
        $this->assign('is_ads', $content->fieldValue('ads_field'));
        $this->assign('gravatar_url', Utils::gravatarUrl($content->authorValue('mail'), 50, theme_options()->commentsAvatarRating, null, true));
        $this->assign('outjump', $content->fieldValue('outjump'));
        $this->display('archives');


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