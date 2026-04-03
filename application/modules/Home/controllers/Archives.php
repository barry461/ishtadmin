<?php

use tools\Markdown;
use Tracking\Helper;

/**
 * 文章详情
 */
class ArchivesController extends WebController
{


    public function indexAction()
    {
        $cid = $this->getRequest()->getParam('id');

        if (empty($cid)) {
            return $this->x404();
        }
        /** @var ContentsModel $content */
        $content = cached("archive:{$cid}")
            ->group('gp:content:archives')
            ->chinese("WEB端文章详情缓存")
            ->fetchPhp(function () use ($cid) {
                // 允许 publish 和 hidden 状态访问，removed 状态返回 404
                return ContentsModel::query()
                    ->where('cid', $cid)
                    ->where('type', ContentsModel::TYPE_POST)
                    ->whereIn('status', [ContentsModel::STATUS_PUBLISH, ContentsModel::STATUS_HIDDEN])
                    ->where('created', '<', time())
                    ->first();
            });
        // var_dump($content);die();
        if (empty($content)) {
            // 缓存文章存在性，区分已删除(410)与下架/未发布(404)
            $exists = cached("archive:{$cid}:exists")
                ->group('gp:content:archives-exist')
                ->chinese("WEB端文章存在性缓存")
                ->expired(300)
                ->fetchPhp(function () use ($cid) {
                    return ContentsModel::query()
                        ->where('cid', $cid)
                        ->exists();
                });

            if (!$exists) {
                return $this->x410();
            }

            return $this->x404();
        }

        if (empty($content->modified)) $content->modified = $content->created;
        if ($content->fieldValue("redirect")) {
            header("Location:" . replace_share($content->fieldValue("redirect")));
            return false;
        }


        // 增加浏览量
        ContentsModel::incrByView($cid);

        $content->load(['fields', 'author']);
        $content->loadTagWithCategory();
        $content->loadWebMarkdown();

        // 站内内链自动插入，仅作用于文章正文内容
        if (!empty($content->content)) {
            $internalLinkService = new \service\InternalLinkService();
            $content->content = $internalLinkService->autoLinkContent(
                (string) $content->content,
                (int) $content->cid
            );
        }
        $prev = cached("archive:{$cid}:prev")
            ->group('gp:content:archives-prev')
            ->chinese("WEB端文章详情上一篇缓存")
            ->fetchPhp(function () use ($cid) {
                return ContentsModel::queryPrev($cid)->first(['cid', 'slug', 'title']);
            });
        $next = cached("archive:{$cid}:next")
            ->group('gp:content:archives-next')
            ->chinese("WEB端文章详情下一篇缓存")
            ->fetchPhp(function () use ($cid) {
                return ContentsModel::queryNext($cid)->first(['cid', 'slug', 'title']);
            });


        list($title, $description, $keywords) = $content->getTDK();
        // 文章详情页的 meta description 改为“正文前150字”
        $summary150 = text_excerpt($content->content, 150);
        $siteTitle = theme_options()->title;
        // var_dump($title);die();
        $this->assignAppList();
        // 获取TDK并确保都是字符串
        list($title, $description, $keywords) = $content->getTDK();
        $title = is_array($title) ? implode(' ', $title) : (string)$title;
        $description = is_array($description) ? implode(' ', $description) : (string)$description;
        $keywords = is_array($keywords) ? implode(',', $keywords) : (string)$keywords;

        // 统一使用正文前150字作为描述
        $description = $summary150;

        $cover_array = get_content_thumbs($content->text, 1);
        $cover = is_array($cover_array) ? reset($cover_array) : $cover_array;
        $permanent_domain = rtrim(options('siteUrl'), '/');
        $canonical_url = $permanent_domain . rtrim($content->url()->getPath(), '/');

        // 如果URL以.html结尾，不添加尾部斜杠
        if (!str_ends_with($canonical_url, '.html')) {
            $canonical_url .= '/';
        }

        $prev_link = '';
        $next_link = '';

        if ($prev) {
            $prev_url = $permanent_domain . $prev->url()->getPath();
            $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
            $this->seo()->linkRel($prev_url, 'prev');
        }

        if ($next) {
            $next_url = $permanent_domain . $next->url()->getPath();
            $next_link = '<link rel="next" href="' . $next_url . '" />';
            $this->seo()->linkRel($next_url, 'next');
        }

        // 生成Tags
        $tags_html = '';
        if (!empty($content->tags)) {
            foreach ($content->tags as $tag) {
                $tags_html .= '<meta property="article:tag" content="' . htmlspecialchars($tag->name) . '">';
            }
        }

        // 发布时间处理
        $release_date_raw = $content->created ?? '';
        if (!empty($release_date_raw) && strlen($release_date_raw) == 10) {
            $release_date_raw .= ' 00:00:00';
        }
        $datetime_object = false;
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $release_date_raw)) {
            $datetime_object = DateTime::createFromFormat("Y-m-d H:i:s", $release_date_raw);
        }
        if ($datetime_object instanceof DateTime) {
            $datetime_object->setTimezone(new DateTimeZone('Asia/Shanghai'));
            $release_date = $datetime_object->format('c');
        } else {
            $release_date = date('c');
        }

        // 基础变量：按备注规则固定 BRAND/HOMEURL，其余资源从后台读取
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');
        $logoUrl = options('logo_url', '');
        $homeUrl = rtrim(options('siteUrl'), '/') . '/';
        $twitterSite = options('twitter_site', '@your_handle');

        // 从 remark 模板生成标题/描述/关键词
        $remark = SeoTplModel::seo_config('article_detail_header');
        $remarkVars = $this->parseRemarkVariables($remark);
        $titleTpl = $remarkVars['TITLE'] ?? '{TITLE} | 黑料吃瓜成人视频每日更新 - {BRAND}';
        $keywordsTpl = $remarkVars['KEYWORDS'] ?? '{KEYWORDS},黑料网,吃瓜网,成人视频,禁漫小说,{BRAND}';
        $descTpl = $remarkVars['DESCRIPTION'] ?? '{DESCRIPTION}';
        $articleTitle = $this->replaceVariables($titleTpl, ['TITLE' => filter_pure_text($title), 'BRAND' => $brand]);
        $description = $this->replaceVariables($descTpl, ['DESCRIPTION' => $summary150]);
        $keywords = $this->replaceVariables($keywordsTpl, ['KEYWORDS' => $keywords, 'BRAND' => $brand]);

        // 获取分类信息
        $categoryName = '';
        $slug = '';
        $categoryUrl = '';
        if (!empty($content->categories)) {
            $categoryCount = count($content->categories);
            $content_cate = $content->categories->toArray();
            $content_cate = array_values($content_cate);
            if ($categoryCount >= 2) {
                $categoryName = $content_cate[1]['name']??'';
                $slug = $content_cate[1]['slug']??'';
            } elseif ($categoryCount == 1) {
                $categoryName = $content_cate[0]['name']??'';
                $slug = $content_cate[0]['slug']??'';
            }
            $categoryUrl = $permanent_domain . url('category', [$slug], false);
        }

        // 获取作者信息
        $authorName = $content->authorValue('screenName') ?: '匿名';
        $authorUrl = $homeUrl . 'author/' . $content->authorValue('uid') . '/';

        // 处理修改时间
        $modifiedDate = $content->modified ? $content->date('c') : $release_date;

        // 确保 LOGO 为绝对 URL
        $logoAbs = $logoUrl;
        if ($logoAbs) {
            if (!preg_match('#^https?://#i', $logoAbs)) {
                $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
            }
        }

        // 获取文章封面图（banner 字段）
        $banner = $content->fieldValue('banner');

        // 确保封面图为绝对 URL
        $bannerAbs = '';
        if ($banner) {
            if (preg_match('#^https?://#i', $banner)) {
                $bannerAbs = $banner;  // 已经是绝对路径
            } else {
                $bannerAbs = rtrim($permanent_domain, '/') . '/' . ltrim($banner, '/');
            }
        }

        // OG/Twitter 图片优先使用封面图，没有则回退到 logo
        $ogImage = $bannerAbs ?: $logoAbs;

        // 组装替换
        $replace = [
            '{TITLE}' => htmlspecialchars(filter_pure_text($articleTitle)),
            '{KEYWORDS}' => htmlspecialchars($keywords),
            '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
            '{CANONICAL}' => $canonical_url,
            '{BRAND}' => htmlspecialchars($brand),
            '{FAVICON}' => $favicon,
            '{TWITTER_SITE}' => $twitterSite,
            '{TWITTER_IMAGE}' => $ogImage, // Twitter图片使用OG图片
            '{OG_IMAGE}' => $ogImage,
            '{OG_IMAGE_TYPE}' => 'image/png',
            '{RELEASE_DATE}' => $release_date,
            '{MODIFIED_DATE}' => $modifiedDate,
            '{AUTHOR_NAME}' => htmlspecialchars($authorName),
            '{CATEGORY}' => htmlspecialchars($categoryName),
            '{AUTHOR_URL}' => $authorUrl,
            '{HOMEURL}' => $homeUrl,
            '{LOGOURL}' => $logoAbs,
            '{VIDEO_TITLE}' => htmlspecialchars($articleTitle),
            '{VIDEO_DESCRIPTION}' => htmlspecialchars($description),
            '{VIDEO_THUMBNAIL}' => $ogImage,
            '{VIDEO_UPLOAD_DATE}' => $release_date,
            '{VIDEO_DURATION}' => 'PT7M15S',
            '{VIDEO_EMBED_URL}' => $canonical_url,
            '{VIDEO_AUTHOR_NAME}' => $categoryUrl,
        ];

        $header = SeoTplModel::seo_tpl('article_detail_header');
        // 清理模版中可能存在的空的/多余的 ld+json 开始标签
        $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>\s*/i', '', $header);
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);

        // LD-JSON：优先使用 remark 中的LD-JSON模板
        $ldJsonTpl = $remarkVars['LD-JSON'] ?? $remarkVars['LD_JSON'] ?? '';
        if (!empty($ldJsonTpl)) {
            $videoTitle = filter_pure_text(mb_substr($articleTitle, 0, 10));
            // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
            $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                '{CANONICAL}' => $canonical_url,
                '{TITLE}' => filter_pure_text($articleTitle),
                '{DESCRIPTION}' => filter_pure_text($description),
                '{BRAND}' => $brand,
                '{HOMEURL}' => $homeUrl,
                '{LOGOURL}' => $logoAbs,
                '{OG_IMAGE}' => $ogImage,
                '{RELEASE_DATE}' => $release_date,
                '{MODIFIED_DATE}' => $modifiedDate,
                '{CATEGORY}' => $categoryName,
                '{KEYWORDS}' => $keywords,
                '{AUTHOR_NAME}' => $authorName,
                '{AUTHOR_URL}' => $authorUrl,
                '{VIDEO_TITLE}' => $videoTitle,
                '{VIDEO_DESCRIPTION}' => "本站收录" . $videoTitle . "完整版视频，资源加密仅供站内播放。",
                '{VIDEO_THUMBNAIL}' => $ogImage,
                '{VIDEO_UPLOAD_DATE}' => $release_date,
                '{VIDEO_DURATION}' => 'PT7M15S',
                '{VIDEO_EMBED_URL}' => $canonical_url,
                '{CATEGORY_URL}' => $categoryUrl
            ]);
            $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTpl);
            $ld_json = $this->setSocialUrls($ld_json);
        } else {
            // 保底结构
            $ldData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebPage',
                        'url' => $canonical_url,
                        'name' => filter_pure_text($articleTitle),
                        'description' => filter_pure_text($description),
                        'inLanguage' => 'zh-CN',
                        'isPartOf' => [
                            '@type' => 'WebSite',
                            'name' => $brand,
                            'url' => $homeUrl,
                            'publisher' => [
                                '@type' => 'Organization',
                                'name' => $brand,
                                'logo' => ['@type' => 'ImageObject', 'url' => $logoAbs],
                            ],
                        ],
                    ],
                    [
                        '@type' => 'Article',
                        'mainEntityOfPage' => ['@type' => 'WebPage', 'url' => $canonical_url],
                        'headline' => filter_pure_text($articleTitle),
                        'description' => filter_pure_text($description),
                        'url' => $canonical_url,
                        'inLanguage' => 'zh-CN',
                        'image' => ['@type' => 'ImageObject', 'url' => $ogImage],
                        'datePublished' => $release_date,
                        'dateModified' => $modifiedDate,
                        'articleSection' => $categoryName,
                        'keywords' => $keywords,
                        'author' => ['@type' => 'Person', 'name' => $authorName, 'url' => $authorUrl],
                        'publisher' => ['@type' => 'Organization', 'name' => $brand, 'logo' => ['@type' => 'ImageObject', 'url' => $logoAbs]],
                    ],
                    [
                        '@type' => 'VideoObject',
                        'url' => $canonical_url,
                        'name' => filter_pure_text($articleTitle),
                        'description' => filter_pure_text($description),
                        'thumbnailUrl' => $ogImage,
                        'uploadDate' => $release_date,
                        'duration' => 'PT7M15S',
                        'embedUrl' => $canonical_url,
                    ],
                ],
            ];
            $ld_json = '<script type="application/ld+json">' . "\n" . json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
        }
        $header = str_replace('{LD_JSON}', $ld_json, $header);
        if (stripos($header, 'application/ld+json') === false) {
            $header .= "\n" . $ld_json;
        }
        $this->assign('header', $header);
        $this->assign('content', $content);
        $this->assign('outjump', $content->fieldValue('outjump'));
        $this->assign('is_ads', $content->fieldValue('ads_field'));
        $this->assign('gravatar_url', Utils::gravatarUrl($content->authorValue('mail'), 50, theme_options()->commentsAvatarRating, null, true));
        $this->assign('author', $content->author);
        $this->assign('headTitle', $content->authorValue('headTitle') ?: theme_options()->headTitle);
        theme_options()->banner = $content->fieldValue('banner');
        theme_options()->headTitle = filter_pure_text($content->title);

        $this->assign('tags', $content->tags);
        $this->assign('next', $next);
        $this->assign('prev', $prev);
        $shareUrl = trim(options('share_domian'),'/').'/archives/'.$content->cid.'/';
        $this->assign('share_content_url',$shareUrl);
        // 获取文章详情页底部追加内容
        $articleBottomContent = options('article_bottom_content');
        $this->assign('articleBottomContent', $articleBottomContent);

//        if ($prev) {
//            $this->seo()->linkRel($prev->url(), 'prev');
//        }
//        if ($next) {
//            $this->seo()->linkRel($next->url(), 'next');
//        }
        $variables = VariableModel::variables();
        $this->assign("show_share_logo", $variables['show_share_logo'] ?? '1');

        // 埋点数据注入
        $trackingData = Helper::getArticleViewTracking($content->toArray());
        $this->assign('tracking', $trackingData);

        $this->display('post');
        //$this->display('archives');
    }


    public function danmakuAction()
    {
        $cid = $this->getRequest()->getParam('cid');
        $comments = cached("comments:all:{$cid}")
            ->fetchJson(function () use ($cid) {
                return CommentsModel::where('cid')
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->orderByDesc('is_top')
                    ->orderByDesc('reply_ct')
                    ->orderByDesc('created')
                    ->get('text')
                    ->toArray();
            });

        $colors = [
            '845EC2', 'FF6F91', 'FF9671', 'FFC75F', 'FF8066', '845EC2',
            'FF9000', 'FF605C',
        ];
        $data = [];
        $duration = 180;
        foreach ($comments as $comment) {
            if (empty($comment['text'])) {
                continue;
            }
            $data[] = [
                'color' => "#" . $colors[mt_rand(0, count($colors) - 1)],
                'time' => $this->generateRandomFloat(0, $duration),
                'text' => $comment['text']
            ];
        }
        $this->showJson($data);
    }

    private function generateRandomFloat(float $minValue, float $maxValue): float
    {
        $num = $minValue + mt_rand() / mt_getrandmax() * ($maxValue - $minValue);
        return sprintf("%.2f", $num);
    }

    public function historyAction()
    {
        $this->limit = 50;
        $this->page = $this->getRequest()->getParam('page') ?? 1;

        // 处理第一页重定向
        if ($this->handleFirstPageRedirect('history')) {
            return;
        }

        $query = ContentsModel::queryWebPost();

        $list = cached('contents:archives-' . $this->page)
            ->chinese('往期福利')
            ->fetchPhp(function () use ($query) {
                return $query->clone()
                    ->with([
                        'fields' => function ($query) {
                            return $query->whereIn('name', ['banner', 'disableBanner', 'redirect']);
                        },
                    ])
                    ->selectRaw("cid, title, slug, created")
                    ->orderByDesc('created')
                    ->forPage($this->page, $this->limit)
                    ->get()
                    ->each(function (ContentsModel $item) {
                        $created = $item->created;
                        $item->setAttribute('date_str', $created ? date('Y-m', strtotime($created)) : '');
                    });
            });

        $count = cached('contents:history-total')
            ->fetchPhp(function () use ($query) {
                return $query->count();
            });

        $pageResult = $this->pageAssign($count, $this->limit);
        if ($pageResult === true) {
            return true;
        }
        list($this->page, $totalPage) = $pageResult;

        // 获取站点基本信息
        $siteName = options('brand', '') ?: options('title', '007吃瓜');
        $siteDescription = options('description', '网站描述');
        $siteKeywords = options('keywords', '关键词');
        $favicon = options('favicon_ico', '/favicon.ico');
        $siteUrl = rtrim(options('siteUrl'), '/');
        $logoUrl = $siteUrl . options('logo_url', '');
        $homeUrl = $siteUrl . '/';
        $twitterSite = options('twitter_site', '@your_handle');

        // 生成分页相关URL
        $permanent_domain = rtrim(options('siteUrl'), '/');
        $canonical_url = $this->page > 1
            ? $permanent_domain . url('history.page', [$this->page], false)
            : $permanent_domain . url('history', [], false);

        $prev_url = '';
        $next_url = '';
        $prev_link = '';
        $next_link = '';

        if ($this->page > 1) {
            if ($this->page == 2) {
                $prev_url = $permanent_domain . url('history', [], false);
            } else {
                $prev_url = $permanent_domain . url('history.page', [$this->page - 1], false);
            }
            $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
        }

        if ($this->page < $totalPage) {
            $next_url = $permanent_domain . url('history.page', [$this->page + 1], false);
            $next_link = '<link rel="next" href="' . $next_url . '" />';
        }

        // 生成标题和描述
        $pageText = $this->page > 1 ? " - 第{$this->page}页" : '';
        $title = "往期内容归档{$pageText} | 历史黑料吃瓜精华持续更新 - {$siteName}";

        $pageDesc = $this->page > 1 ? "第{$this->page}页" : "";
        $description = "往期内容归档{$pageDesc}集中展示站内历史热门瓜料，涵盖校园事件、明星八卦、网红爆料、OnlyFans流出、伦理禁忌、男同吃瓜、AV鉴赏等精华内容。通过往期归档，你可以轻松回顾以往的精彩爆料，免费在线观看，高清秒播，尽享持续更新的吃瓜体验。";

        $keywords = "往期内容,历史归档,热门瓜料,明星八卦,校园事件,网红爆料,OnlyFans流出,伦理禁忌,黑料网,吃瓜网,成人视频,禁漫小说,{$siteName}";

        // 获取SEO模版并替换变量
        $header = SeoTplModel::seo_tpl('archives_list');
        $replace = [
            '{TITLE}' => htmlspecialchars($title),
            '{DESCRIPTION}' => htmlspecialchars($description),
            '{KEYWORDS}' => htmlspecialchars($keywords),
            '{CANONICAL}' => $canonical_url,
            '{PREV}' => $prev_link,
            '{NEXT}' => $next_link,
            '{BRAND}' => htmlspecialchars($siteName),
            '{FAVICON}' => $favicon,
            '{LOGOURL}' => $logoUrl,
            '{TWITTER_SITE}' => $twitterSite,
            '{HOMEURL}' => $homeUrl,
            '{PAGE}' => $this->page > 1 ? "第{$this->page}页" : '',
        ];
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);

        //logo
        $logoAbs = $logoUrl;
        if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
            $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
        }
        $brand = options('brand', '') ?: options('title', '007吃瓜');


        $remark = SeoTplModel::seo_config('archives_list');
        $remarkVars = $this->parseRemarkVariables($remark);
        // LD-JSON - 优先使用分页模板
        if ($this->page > 1 && !empty($remarkVars['LD_JSON_PAGE'])) {
            $ld_json = $remarkVars['LD_JSON_PAGE'];
        } else {
            $ld_json = $remarkVars['LD_JSON'] ?? '';
        }
        if (!empty($ld_json)) {
            // 合并后台变量，让 {VAR_xxx} 在 LD_JSON 中生效（使用原始值，不HTML转义）
            $ldJsonReplacements = array_merge($this->getVariableReplacementsRaw(), [
                '{HOMEURL}' => $homeUrl,
                '{CANONICAL}' => $canonical_url,
                '{TITLE}' => filter_pure_text($title),
                '{DESCRIPTION}' => filter_pure_text($description),
                '{PAGE}' => '第' . $this->page . '页',
                '{LOGOURL}' => $logoAbs,
                '{ARCHIVES_URL}' => $permanent_domain . url('history', [], false),
                '{BRAND}' => $brand,
            ]);
            $ldJsonTpl = $ld_json;
            $ld_json = str_replace(array_keys($ldJsonReplacements), array_values($ldJsonReplacements), $ldJsonTpl);
            $ld_json = $this->setSocialUrls($ld_json);
        }
        $header = str_replace('{LD_JSON}', $ld_json, $header);

        // 设置header到视图
        $this->assign('header', $header);

        $taglist = [];

        if ($this->page == 1) {
            $taglist = cached('tags-list')
                ->group('gp:content:tags-list')
                ->fetchPhp(function () {
                    return TagsModel::query()
                        ->orderByDesc('id')
                        ->forPage(0, 30)
                        ->get();
                });
        }

        $list = $list->groupBy('date_str');

        $this->assign([
            'taglist' => $taglist,
            'lists' => $list,
            'currentPage' => $this->page,
            'totalPage' => $totalPage,
            'PageNavigator' => new PageNavigator($this->page, $totalPage, url_raw('history.page'), url_raw('history')),
        ]);

        $this->display('history');
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