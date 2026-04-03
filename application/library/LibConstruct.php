<?php

class LibConstruct
{

    protected static function getCurrentCanonicalUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'hjvideo.com';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $request_uri;
    }
    protected static function make_melon_entity_collection($melons): array
    {
        $article_entity = [];
        foreach ($melons as $melon) {
            $article_entity[] = self::make_article_entity($melon);
        }
        return [
            "@type"   => "CollectionPage",
            "name"    => register('site.app_name')."",
            "hasPart" => $article_entity
        ];
    }

    protected static function make_video_entity_collection($title, $videos): array
    {
        $video_entity = [];
        foreach ($videos as $video) {
            $video_entity[] = self::make_mv_entity($video);
        }
        return [
            "@type"   => "CollectionPage",
            "name"    => $title,
            "hasPart" => $video_entity
        ];
    }

    public static function make_index($title, $description, $canonical_url = null): string
    {
        $web_site_name = options('title');
        $web_site_alternate_name = setting('web_site_alternate_name', '');
        $web_site_alternate_names = explode(",", trim($web_site_alternate_name));
        $web_site_alternate_names = array_filter($web_site_alternate_names);
        $web_site_alternate_names = array_unique($web_site_alternate_names);

        // 如果没有传入canonical_url，则使用当前URL
        if ($canonical_url === null) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'hjvideo.com';
            $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
            $canonical_url = $scheme . '://' . $host . $request_uri;
        }

        $columns = [];

        $data = [
            "@context" => "https://schema.org",
            "@graph"   => [
                [
                    "@type"           => "WebSite",
                    "name"            => $web_site_name,
                    "alternateName"   => $web_site_alternate_names,
                    "url"             => options('siteUrl') . '/',
                    "potentialAction" => [
                        [
                            "@type"       => "SearchAction",
                            "target"      => [
                                "@type"       => "EntryPoint",
                                "urlTemplate" => options('siteUrl') . "/search/{search_term_string}/1/"
                            ],
                            "query-input" => "required name=search_term_string",
                            "about"       => [
                                "@type" => "CollectionPage",
                                "name"  => "文章搜索"
                            ]
                        ]
                    ]
                ],
                [
                    "@type"       => "WebPage",
                    "name"        => $title,
                    "url"         => $canonical_url,
                    "description" => $description,
                    "hasPart"     => $columns
                ]
            ]
        ];


        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return <<<DF
    <script type="application/ld+json">
    {$ld_json}
    </script>
DF;

    }

    protected static function make_mv_entity($mv): array
    {
        $actor = $mv->actors ? $mv->actors : setting('web_author', '');
        $description = $mv->description;

        $datetime_object = DateTime::createFromFormat("Y-m-d H:i:s", $mv->created_at);
        $datetime_object->setTimezone(new DateTimeZone('UTC'));
        $iso8601_utc_format = $datetime_object->format("Y-m-d\TH:i:sP");

        $permanent_domain = setting('site_permanent_domain', '');
        $permanent_url = 'https://' . $permanent_domain;
        $canonical_url = $permanent_url . LibUrl::url_vod_detail($mv->id);
        $author_url = $permanent_url . LibUrl::url_vod_author($mv->user_id);
        $embed_url = $permanent_url . LibUrl::url_vod_embed($mv->id);

        $mode = 1;
        $default_cover = $permanent_url . '/static/web/images/thumb.png?t=1748339311' . $mv->id;
        $cover = $mode ? $default_cover : $mv->reptile_cover;

        return [
            "@context"             => "http://schema.org",
            "@type"                => "VideoObject",
            "name"                 => $mv->title,
            "duration"             => format_duration($mv->video_duration_int),
            "thumbnailUrl"         => $cover,
            "uploadDate"           => $iso8601_utc_format,
            "description"          => $description,
            "author"               => $actor,
            "embedUrl"             => $embed_url,
           // "contentUrl"           => $canonical_url,
            "uploader"             => [
                "@type" => "Person",
                "name"  => $mv->user_name,
                "url"   => $author_url
            ],
            "interactionStatistic" => [
                [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "http://schema.org/WatchAction",
                    "userInteractionCount" => number_format_thousands($mv->view_fct)
                ],
                [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "http://schema.org/LikeAction",
                    "userInteractionCount" => number_format_thousands($mv->like_fct)
                ]
            ]
        ];
    }

    protected static function make_article_entity($melon): array
    {
        $title = $melon->title ?? '';
        $publish_date_raw = $melon->publish_time ?? '';

        if (!empty($publish_date_raw) && strlen($publish_date_raw) == 10) {
            // 如果只有日期，补时间
            $publish_date_raw .= ' 00:00:00';
        }

        $datetime_object = false;
        if (!empty($publish_date_raw) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $publish_date_raw)) {
            $datetime_object = DateTime::createFromFormat("Y-m-d H:i:s", $publish_date_raw);
        }

        if ($datetime_object instanceof DateTime) {
            $datetime_object->setTimezone(new DateTimeZone('UTC'));
            $iso8601_utc_format = $datetime_object->format("Y-m-d\TH:i:sP");
        } else {
            $iso8601_utc_format = date('c'); 
        }

   
        $permanent_domain = setting('site_permanent_domain', '');
        $permanent_url = 'https://' . $permanent_domain;
        $canonical_url = $permanent_url . LibUrl::url_articles_detail($melon->id);
        $author_url = $permanent_url . LibUrl::url_articles_author($melon->user_id);

        $mode = 1;
        $default_cover = $permanent_url . '/static/web/images/article_thumb.png?t=1748339598' . $melon->id;
        $cover = $mode ? $default_cover : $melon->reptile_cover;
        $images = [$cover];

        return [
            "@context"      => "https://schema.org",
            "@type"         => "NewsArticle",
            "url"           => $canonical_url,
            "headline"      => $title,
            "image"         => $images,
            "datePublished" => $iso8601_utc_format,
            "dateModified"  => $iso8601_utc_format,
            "author"        => [
                [
                    "@type" => "Person",
                    "name"  => $melon->author->screenName,
                    "url"   => $author_url
                ]
            ]
        ];
    }

    public static function make_list($type, $title, $description, $mvs, $canonical_url, $next_url): string
    {
        $columns = [];
//        foreach ($mvs as $item) {
//            $columns[] = $type == 1 ? self::make_mv_entity($item) : self::make_article_entity($item);
//        }

        $data = [
            "@context"    => "http://schema.org",
            "@type"       => "CollectionPage",
            "name"        => $title,
            "url"         => $canonical_url,
            "description" => $description,
            "hasPart"     => $columns,
        ];

        if ($next_url) {
            $data['pagination'] = [
                "@type" => "WebPage",
                "name"  => "下一页",
                "url"   => $next_url
            ];
        }

        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return <<<DF
    <script type="application/ld+json">
    {$ld_json}
    </script>
DF;
    }

    public static function make_mv($mv): string
    {
        $data = self::make_mv_entity($mv);
        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return <<<DF
    <script type="application/ld+json">
    {$ld_json}
    </script>
DF;
    }

    public static function make_article($melon): string
    {
        $data = self::make_article_entity($melon);
        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return <<<DF
    <script type="application/ld+json">
    {$ld_json}
    </script>
DF;
    }

    public static function make_default($title, $canonical_url): string
    {
        $web_site_name = setting('web_site_name', '');
        $web_share_logo = '/static/web/images/logo.png';
        $permanent_domain = setting('site_permanent_domain', '');
        $permanent_url = 'https://' . $permanent_domain;
        $web_share_logo = $permanent_url . '/' . ltrim($web_share_logo, '/');
        $main_page = $permanent_url . '/';

        $default_date = '2025-05-10 12:00:00';
        $datetime_object = DateTime::createFromFormat("Y-m-d H:i:s", $default_date);
        $datetime_object->setTimezone(new DateTimeZone('UTC'));
        $iso8601_utc_format = $datetime_object->format("Y-m-d\TH:i:sP");

        $data = [
            "@context"         => "https://schema.org",
            "@type"            => "Article",
            "headline"         => $title,
            "url"              => $canonical_url,
            "image"            => [
                $web_share_logo
            ],
            "author"           => [
                "@type" => "Organization",
                "name"  => $web_site_name,
                "url"   => $main_page
            ],
            "publisher"        => [
                "@type" => "Organization",
                "name"  => $web_site_name,
                "logo"  => [
                    "@type" => "ImageObject",
                    "url"   => $web_share_logo
                ]
            ],
            "datePublished"    => $iso8601_utc_format,
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id"   => $main_page
            ]
        ];
        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return <<<DF
    <script type="application/ld+json">
    {$ld_json}
    </script>
DF;
    }

    public static function make_author($screenName, $canonicalUrl, $page = 1): string
    {
        $web_site_name = setting('web_site_name', register('site.app_name'));
        $permanent_domain = setting('site_permanent_domain', '');
        $site_url = 'https://' . $permanent_domain;

        $data = [
            "@context" => "https://schema.org",
            "@type"    => "WebPage",
            "name"     => "{$screenName}发布的文章 - 第{$page}页",
            "url"      => $canonicalUrl,
            "description" => "{$screenName}发布的爆料文章合集，包含黑料泄密、网红吃瓜、明星私拍、每日更新。",
            "publisher" => [
                "@type" => "Organization",
                "name"  => $web_site_name,
                "url"   => $site_url,
                "logo"  => [
                    "@type" => "ImageObject",
                    "url"   => $site_url . "/static/web/images/logo.png"
                ]
            ]
        ];

        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return <<<LD
    <script type="application/ld+json">
    {$ld_json}
    </script>
    LD;
    }



    public static function make_search($keyword, $canonical_url, $page = 1, $count = 0): string
    {
        $web_site_name = setting('web_site_name', register('site.app_name'));
        $permanent_domain = setting('site_permanent_domain', '91si.com');
        $site_url = 'https://' . $permanent_domain;

        $data = [
            "@context" => "https://schema.org",
            "@type"    => "WebPage",
            "name"     => "搜索{$keyword}的吃瓜文章第{$page}页 - 吃瓜黑料最新合集",
            "url"      => $canonical_url,
            "description" => "您正在查看与“{$keyword}”相关的全部吃瓜文章第{$page}页，".register('site.app_name')."为你持续更新全网最全最新的黑料吃瓜资源，共{$count}篇文章。",
            "publisher" => [
                "@type" => "Organization",
                "name"  => $web_site_name,
                "url"   => $site_url,
                "logo"  => [
                    "@type" => "ImageObject",
                    "url"   => $site_url . "/static/web/images/logo.png"
                ]
            ]
        ];

        $ld_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return <<<LD
<script type="application/ld+json">
{$ld_json}
</script>
LD;
    }

    public static function make_authors_list(string $title, string $canonicalUrl, int $page): string
{
    $web_site_name = setting('web_site_name', register('site.app_name'));
    $site_url = 'https://' . setting('site_permanent_domain', '');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "WebPage",
        "name"     => $title . " - 第{$page}页",
        "url"      => $canonicalUrl,
        "description" => "浏览".register('site.app_name')."社区的乱伦原创博主主页第{$page}页，关注免费观看更多私密内容。",
        "publisher" => [
            "@type" => "Organization",
            "name"  => $web_site_name,
            "url"   => $site_url,
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => $site_url . "/static/web/images/logo.png"
            ]
        ]
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return <<<LD
<script type="application/ld+json">
{$json}
</script>
LD;
}

public static function make_tags_list(string $title, string $canonicalUrl, int $page): string
{
    $web_site_name = setting('web_site_name', register('site.app_name'));
    $site_url = 'https://' . setting('site_permanent_domain', '');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "WebPage",
        "name"     => $title . " - 第{$page}页",
        "url"      => $canonicalUrl,
        "description" => "浏览".register('site.app_name')."社区的热门标签列表第{$page}页，获取最新乱伦原创、吃瓜黑料资源。",
        "publisher" => [
            "@type" => "Organization",
            "name"  => $web_site_name,
            "url"   => $site_url,
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => $site_url . "/static/web/images/logo.png"
            ]
        ]
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return <<<LD
<script type="application/ld+json">
{$json}
</script>
LD;
}

public static function make_tag_detail(string $tag, string $canonicalUrl, int $page, int $count): string
{
    $web_site_name = setting('web_site_name', register('site.app_name'));
    $site_url = 'https://' . setting('site_permanent_domain', '');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "CollectionPage",
        "name"     => "标签「{$tag}」相关文章第{$page}页",
        "url"      => $canonicalUrl,
        "description" => "浏览标签「{$tag}」相关的吃瓜文章第{$page}页，共{$count}篇文章，每日更新最新黑料爆料内容。",
        "publisher" => [
            "@type" => "Organization",
            "name"  => $web_site_name,
            "url"   => $site_url,
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => $site_url . "/static/web/images/logo.png"
            ]
        ]
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return <<<LD
<script type="application/ld+json">
{$json}
</script>
LD;
}
public static function make_hot_list(string $title, string $canonicalUrl, int $page): string
{
    $web_site_name = setting('web_site_name', register('site.app_name'));
    $site_url = 'https://' . setting('site_permanent_domain', '');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "CollectionPage",
        "name"     => $title . " - 第{$page}页",
        "url"      => $canonicalUrl,
        "description" => "查看".register('site.app_name')."社区人气最高的爆款文章TOP榜单，第{$page}页，每天持续更新。",
        "publisher" => [
            "@type" => "Organization",
            "name"  => $web_site_name,
            "url"   => $site_url,
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => $site_url . "/static/web/images/logo.png"
            ]
        ]
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return <<<LD
<script type="application/ld+json">
{$json}
</script>
LD;
}

public static function make_history_list(string $title, string $canonicalUrl, int $page): string
{
    $web_site_name = setting('web_site_name', register('site.app_name'));
    $site_url = 'https://' . setting('site_permanent_domain', '');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "CollectionPage",
        "name"     => "{$title} - 第{$page}页",
        "url"      => $canonicalUrl,
        "description" => register('site.app_name')."往期福利合集持续更新，第{$page}页，每日更新高能内容。",
        "publisher" => [
            "@type" => "Organization",
            "name"  => $web_site_name,
            "url"   => $site_url,
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => $site_url . "/static/web/images/logo.png"
            ]
        ]
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return <<<LD
<script type="application/ld+json">
{$json}
</script>
LD;
}


}