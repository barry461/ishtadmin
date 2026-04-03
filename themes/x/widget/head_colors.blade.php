@php
    $hexColor = theme_options()->themeColor;
    $hexColorDark = theme_options()->themeColorDark;
    if (!$hexColorDark) {
        $hexColorDark = $hexColor;
    }
@endphp
        <!-- 主题主色调 -->
<style type="text/css">
    /* Color - Custom */
    body.color-custom a {
        color: {!! $hexColor !!};
    }

    body.color-custom *::selection {
        background: {!! theme_options()->get('themeSelectionBackgroundColor',$hexColor) !!};
        color: {!! theme_options()->get('themeSelectionColor','#fff') !!};
    }
    body.color-custom #index article a, body.color-custom #post article a, body.color-custom #archive article a {
        color: {!! $hexColor !!};
    }
    body.color-custom #footer a:after, body.color-custom #header .nav li a:after, body.color-custom #post .post-meta a:after, body.color-custom #index .comments a:after, body.color-custom #index .post-content a:after, body.color-custom #post .post-content a:after, body.color-custom #archive .post-content a:after, body.color-custom #archive .comments a:after, body.color-custom #comments a:after {
        border-color: {!! $hexColor !!};
    }
    body.color-custom .post-content a {
        color: {!! $hexColor !!};
    }
    body.color-custom .post-near {
        color: {!! $hexColor !!};
    }
    body.color-custom #nav .search-box .search{
        color: {!! $hexColor !!};
    }
    body.color-custom #comments .comment-list a, body.color-custom #comments .respond a  {
        color: {!! $hexColor !!};
    }
    body.color-custom #comments .widget-title {
        color: {!! $hexColor !!};
    }
    body.color-custom .color-main {
        color: {!! $hexColor !!} !important;
    }
    body.color-custom #disqus_thread a {
        color: {!! $hexColor !!};
    }
    body.color-custom #footer a {
        color: {!! $hexColor !!};
    }
    body.color-custom .github-box .github-box-download .download:hover{
        border-color: {!! $hexColor !!} !important;
        background-color: {!! Utils::hex2RGBColor($hexColor, 0.4) !!} !important;
    }
    body.color-custom .sp-progress {
        background: linear-gradient(45deg, {!! Utils::hex2RGBColor($hexColor, 0)!!}, {!! Utils::hex2RGBColor($hexColor, 0.1)!!} 25%, {!! Utils::hex2RGBColor($hexColor, 0.35)!!} 50%, {!! Utils::hex2RGBColor($hexColor, 1)!!} 75%, {!! Utils::hex2RGBColor($hexColor, 0.1)!!});
    }

    li.index-menu-item.current>a.index-menu-link, body.color-custom li.index-menu-item.current>a.index-menu-link{
        color: {!! $hexColor !!};
        border-left: .125rem solid {!! $hexColor !!};
    }

    body.color-custom .post-content .content-file:hover .content-file-icon,
    body.color-custom .post-content .content-file:hover .content-file-filename,
    body.color-custom .comment-content .content-file:hover .content-file-icon,
    body.color-custom .comment-content .content-file:hover .content-file-filename,
    body.color-custom.theme-sunset .post-content .content-file:hover .content-file-icon,
    body.color-custom.theme-sunset .post-content .content-file:hover .content-file-filename,
    body.color-custom.theme-sunset .comment-content .content-file:hover .content-file-icon,
    body.color-custom.theme-sunset .comment-content .content-file:hover .content-file-filename {
        color: {!! $hexColor !!};
    }

    body.color-custom .post-content .content-file:hover,
    body.color-custom .comment-content .content-file:hover,
    body.color-custom.theme-sunset .post-content .content-file:hover,
    body.color-custom.theme-sunset .comment-content .content-file:hover {
        border-color: {!! $hexColor !!};
    }

    /* Color - Custom Dark */
    body.theme-dark a {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom *::selection {
        background: {!! theme_options()->get('themeSelectionBackgroundDarkColor', $hexColorDark)!!};
        color: {!! theme_options()->get('themeSelectionDarkColor', '#fff')!!};
    }
    body.theme-dark.color-custom #index article a, body.theme-dark.color-custom #post article a, body.theme-dark.color-custom #archive article a {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom #footer a:after,
    body.theme-dark.color-custom #header .nav li a:after,
    body.theme-dark.color-custom #post .post-meta a:after,
    body.theme-dark.color-custom #index .comments a:after,
    body.theme-dark.color-custom #index .post-content a:after,
    body.theme-dark.color-custom #post .post-content a:after,
    body.theme-dark.color-custom #archive .post-content a:after,
    body.theme-dark.color-custom #archive .comments a:after,
    body.theme-dark.color-custom #comments a:after
    {
        border-color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom .post-content a {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom .post-near {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom #nav .search-box .search {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom #comments .comment-list a, body.theme-dark.color-custom #comments .respond a  {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom #comments .widget-title {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom .color-main {
        color: {!! $hexColorDark!!} !important;
    }
    body.theme-dark.color-custom #disqus_thread a {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom #footer a {
        color: {!! $hexColorDark!!};
    }
    body.theme-dark.color-custom .github-box .github-box-download .download:hover{
        border-color: {!! $hexColorDark!!} !important;
        background-color: {!! Utils::hex2RGBColor($hexColorDark, 0.4)!!} !important;
    }
    body.color-custom.theme-dark #index .post-content a:not(.no-icon), body.color-custom.theme-dark #archive .post-content a:not(.no-icon),body.color-custom.theme-dark #post .post-content a:not(.no-icon) {
        color: {!! $hexColorDark!!};
    }
    body.color-custom.theme-dark li.index-menu-item.current>a.index-menu-link {
        color: {!! $hexColorDark!!};
        border-left: .125rem solid {!! $hexColorDark!!};
    }
    body.color-custom.theme-dark .sp-progress {
        background: linear-gradient(45deg, {!! Utils::hex2RGBColor($hexColorDark, 0)!!}, {!! Utils::hex2RGBColor($hexColorDark, 0.1)!!} 25%, {!! Utils::hex2RGBColor($hexColorDark, 0.35)!!} 50%, {!! Utils::hex2RGBColor($hexColorDark, 1)!!} 75%, {!! Utils::hex2RGBColor($hexColorDark, 0.1)!!});
    }

    body.color-custom.theme-dark .post-content .content-file:hover .content-file-icon,
    body.color-custom.theme-dark .post-content .content-file:hover .content-file-filename,
    body.color-custom.theme-dark .comment-content .content-file:hover .content-file-icon,
    body.color-custom.theme-dark .comment-content .content-file:hover .content-file-filename {
        color: {!! $hexColorDark!!};
    }
    body.color-custom.theme-dark .post-content .content-file:hover,
    body.color-custom.theme-dark .comment-content .content-file:hover {
        border-color: {!! $hexColorDark!!};
    }


    @unless(Device::isMobile())
    /*桌面端*/
    body.color-custom #index .post .post-title:hover,body.color-custom #archive .post .post-title:hover {
        color: {!! $hexColor !!};
    }
    body.color-custom.theme-dark #index .post .post-title:hover,body.color-custom.theme-dark #archive .post .post-title:hover {
        color: {!! $hexColorDark!!};
    }
    @endunless
</style>