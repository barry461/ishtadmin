<?php
strpos(__DIR__, 'public') and die() ?>
@php
    list($popup_x,$popup_y) = theme_options()->appCenterPopSizeXY ;
@endphp
<!DOCTYPE HTML>
<html class="no-js" lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="HandheldFriendly" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    @if(isset($seoStatHeadCodes) && $seoStatHeadCodes)
        {!! $seoStatHeadCodes !!}
    @endif
    @yield('seo-head')
    {!! theme()->importJs('base') !!}
    {!! theme()->importCss('common', ['t'=>'20230812']) !!}
    <style>.application-popup .application-list {grid-template-columns:repeat({!! $popup_x !!},1fr);grid-template-rows:repeat({!! $popup_y !!},1fr);}</style>
    {!! theme()->linkCss('/usr/plugins/FootMenu/assets/foot_menu.css' , ['t'=>'20230812']) !!}
    <!-- 1204wst 友情推荐 start -->
    {!! theme()->linkCss('/usr/themes/Mirages/css/7.10.0/friend-link-box.css') !!}
    <!-- 1204wst 友情推荐 end -->
    {!! Device::win() ? '' : theme()->importCss('head-font-not-win') !!}
    {!! theme()->importCss('head-font') !!}
    {!! theme()->linkCss('/usr/themes/Mirages/css/7.10.0/fontawesome.min.css' , ['v'=>2]) !!}
    {!! theme()->linkCss('/usr/themes/Mirages/css/7.10.0/mirages.min.css' , ['t'=>'20231111']) !!}
    {!! theme()->linkCss('/usr/themes/Mirages/css/7.10.0/common.css' , ['t'=>'20231217']) !!}
    {!! theme()->linkCss('/usr/themes/Mirages/css/7.10.0/VirtualList/virtuallist.css' , ['t'=>'20230812']) !!}
    <script type="text/javascript">
        window['LocalConst'] = {!! $LocalConst !!};
        var Mlog = function (message) { };
        var BIAOQING_PAOPAO_PATH = LocalConst.BIAOQING_PAOPAO_PATH;
        var BIAOQING_ARU_PATH = LocalConst.BIAOQING_ARU_PATH;
        LocalConst.KEY_CDN_TYPE = 'mirages-cdn-type';
        LocalConst.UPYUN_SPLIT_TAG = '!';
        {{-- 自动夜间模式 --}}
        var hour = new Date().getHours();
        if (hour <= 5 || hour >= 22) {
            LocalConst.USE_MIRAGES_DARK = true;
        }
    </script>
    {!! theme()->importJs('common') !!}
    <style type="text/css">
        body, button, input, optgroup, select, textarea {
            font-family: 'Mirages Custom', 'Merriweather', 'Open Sans', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft Yahei', 'WenQuanYi Micro Hei',  'Segoe UI Emoji', 'Segoe UI Symbol', Helvetica, Arial, sans-serif;
        }
        .github-box, .github-box .github-box-title h3 {
            font-family: 'Mirages Custom', 'Merriweather', 'Open Sans', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft Yahei', 'WenQuanYi Micro Hei',  'Segoe UI Emoji', 'Segoe UI Symbol', Helvetica, Arial, sans-serif !important;
        }
        .aplayer {
            font-family: 'Mirages Custom', 'Myriad Pro', 'Myriad Set Pro', 'Open Sans', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft Yahei', 'WenQuanYi Micro Hei',  Helvetica, arial, sans-serif !important;
        }
        /* Serif */
        body.content-lang-en.content-serif .post-content {
            font-family: 'Lora', 'PT Serif', 'Source Serif Pro', Georgia, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft Yahei', 'WenQuanYi Micro Hei',  serif;
        }
        body.content-lang-en.content-serif.serif-fonts .post-content {
            font-family: 'Lora', 'PT Serif', 'Source Serif Pro', 'Noto Serif CJK SC', 'Noto Serif CJK', 'Noto Serif SC', 'Source Han Serif SC', 'Source Han Serif', 'source-han-serif-sc', 'PT Serif', 'SongTi SC', 'MicroSoft Yahei',  serif;
        }
        body.serif-fonts .post-content, body.serif-fonts .blog-title {
            font-family: 'Noto Serif CJK SC', 'Noto Serif CJK', 'Noto Serif SC', 'Source Han Serif SC', 'Source Han Serif', 'source-han-serif-sc', 'PT Serif', 'SongTi SC', 'MicroSoft Yahei',  Georgia, serif;
        }
        .dark-mode-state-indicator {
            position: absolute;
            top: -999em;
            left: -999em;

            z-index: 1;
        }

        @media (prefers-color-scheme: dark) {
            .dark-mode-state-indicator {
                z-index: 11;
            }
        }

    /** 页面样式调整 */
        @media(max-width: 767px) {
        body.card #index, body.card #archive {
            padding: 4rem 3rem 3.5rem;
        }
        body.card .container {
            max-width: 710px;
        }
        body.card #index article, body.card #archive article {
            padding: .9375rem 0 1.25rem;
        }
        body.card #index article .post-card-mask, body.card #archive article .post-card-mask {
            background-color: rgba(0,0,0,.3);
            height: 12.5rem;
        }
        body.card #index article .post-card-container, body.card #archive article .post-card-container {
            padding: 1rem 1rem;
        }
        .page-navigator {
            margin-top: 2rem;
        }
        body.card #index article .post-card-ads, body.card #archive article .post-card-ads {
            background-color: rgba(0,0,0,.1);
        }
        body.card #index article .post-card-title, body.card #archive article .post-card-title {
            font-size: 1.1625rem;
        }
    }
    @media (max-width: 390px) {
        .page-jump input {
            max-width: 3rem;
        }
    }
    @media(max-width: 336px) {
        body.card #index article .post-card-mask, body.card #archive article .post-card-mask {
            height: 10.5rem;
        }
        a.btn, .btn>a {
            padding: .4375rem 2.25rem;
        }
        .page-navigator {
            margin-top: 1.5rem;
        }
    }
    @media screen and (min-width: 768px) and (max-width: 1301px) {
        body.card .container {
            max-width: 720px;
        }
    }
    @media screen and (min-width: 1302px) and (max-width: 1599px) {
        body.card .container {
            max-width: 864px;
        }
    }
    @media screen and (min-width: 1600px) and (max-width: 1799px){
        body.card .container {
            max-width: 896px;
        }
    }
    @media screen and (min-width: 1800px) and (max-width: 1999px){
        body.card .container {
            max-width: 960px;
        }
    }
    @media screen and (min-width: 2000px) and (max-width: 2399px) {
        body.card .container {
            max-width: 992px;
        }
    }
    @media screen and (min-width: 2400px) {
        body.card .container {
            max-width: 1024px;
        }
    }
    #qr-box {
        background-color: transparent;
    }
    .post-buttons, #qr-box {
        display: none;
    }
    #body-bottom {
        margin-top: 0;
    }
            
    @media screen and (max-width: 40rem) {
    #post article {
        margin-top: 2.6rem;
    }
    }
                
    </style>
    <style type="text/css">
        /** 页面样式调整 */
        @if(!theme_options()->postQRCodeURL || !theme_options()->rewardQRCodeURL)
        .post-buttons a {
            width: -webkit-calc(100% / 2 - .3125rem);
            width: calc(100% / 2 - .3125rem);
        }
        @endif
        @if(theme_options()->codeColor)
        .post .post-content > *:not(pre) code {
            color: {!! theme_options()->codeColor !!};
        }
        @endif
    </style>
    @if(theme_options()->themeColor)
        @include("widget.head_colors")
    @endif
    {!! Utils::replaceStaticPath(theme_options()->customHTMLInHeadBottom) !!}
    @if(!isset($is404Page) || !$is404Page)
    <script>
        var _czc = _czc || [];
        var _hmt = _hmt || [];
    </script>
    @endif
    @if(!theme_options()->loadJQueryInHead)
        {!! theme()->importJs('/usr/themes/Mirages/static/jquery/2.2.4/jquery.min.js') !!}
    @endif
    {!! theme()->importJs('/usr/plugins/tbxw/js/zzz.js') !!}

    {!! $seoPublicHeader !!}
</head>
<body class="{!! $bodyClass !!}">
<script>
    loadPrefersDarkModeState();
    if (LocalConst.USE_MIRAGES_DARK || (LocalConst.AUTO_NIGHT_SHIFT && LocalConst.PREFERS_DARK_MODE)) {
        var body = document.querySelector("body");
        body.classList.remove('theme-white');
        body.classList.add('theme-dark');
        body.classList.add('dark-mode');

        if (LocalConst.USE_MIRAGES_DARK) {
            body.classList.remove('dark-mode');
        } else if (LocalConst.AUTO_NIGHT_SHIFT && LocalConst.PREFERS_DARK_MODE) {
            body.classList.add('os-dark-mode');
        }
    }


    const isInViewport = (el) => {
        const rect = el.getBoundingClientRect();
        const vh = window.innerHeight || document.documentElement.clientHeight;
        return rect.top < vh;
    };

    const handleImage = (el) => {
        const bg = el.getAttribute("data-src");
        if (bg) {
            loadImageEle(bg, el)
            el.removeAttribute("data-src");
            el.classList.remove('lazy')
        }
    };

    const handleBg = (el) => {
        const bg = el.getAttribute("data-bg");
        if (!bg) return
        loadBackgroundImage(bg,el)
        el.removeAttribute("data-bg");
        el.classList.remove('lazy-bg');
    };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            if (el.tagName === 'IMG') {
                handleImage(el);
            } else {
                handleBg(el);
            }
            obs.unobserve(el);
        });
    }, {
        rootMargin: '500px',
        threshold: 0.01
    });


    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".lazy-bg").forEach(el => {
            isInViewport(el) ? handleBg(el) : observer.observe(el);
        })
        document.querySelectorAll('img.lazy').forEach(el => {
            isInViewport(el) ? handleImage(el) : observer.observe(el);
        })
    });
</script>
<!--[if lt IE 9]>
<div class="browse-happy" role="dialog">
当前网页 <strong>不支持</strong> 你正在使用的浏览器. 为了正常的访问, 请 <a href="http://browsehappy.com/">升级你的浏览器</a>.
</div>
<![endif]-->
<div class="sp-progress"></div>
<div id="wrap">
    <span id="backtop" class="waves-effect waves-button"><i class="fa fa-angle-up"></i></span>
    <header>
        @include('widget.sidebar')
        @if(theme_options()->navbarStyle == 1)
            @include('widget.navbar')
        @endif
    </header>

    <div id="body">
        @include("widget.headfix_pages")
        <script type="text/javascript">
            var wrap = document.querySelector('#wrap');
            var navbar = document.querySelector('#navbar');
            wrap.classList.remove('display-menu-tree');
            var body = document.querySelector('body');
            body.classList.remove('display-menu-tree');
            LocalConst.TOC_AT_LEFT = {{ var_export(theme_options()->showTOCAtLeft,1)}};
            LocalConst.ENABLE_MATH_JAX = {{ var_export(theme_options()->enableMathJax,1) }};
            LocalConst.ENABLE_FLOW_CHART = {{ var_export(theme_options()->enableFlowChat,1)}};
            LocalConst.ENABLE_MERMAID = {{ var_export(theme_options()->enableMermaid,1)}};
            @if(theme_options()->defaultTOCClass)
            if (window.innerWidth >= 1008) {
                wrap.classList.add('no-animation');
                if (navbar) {
                    navbar.classList.add('no-animation');
                }
                wrap.classList.add('{{theme_options()->defaultTOCClass}}');
                body.classList.add('{{theme_options()->defaultTOCClass}}');
                setTimeout(function () {
                    wrap.classList.remove('no-animation');
                    if (navbar) {
                        navbar.classList.remove('no-animation');
                    }
                }, 1000);
            }
            @endif

{{--            {!! theme_options()->detectBodyClassForPJAX('no-banner', 'body') !!}--}}
            @if(is_url('category','category.page','tag.detail'))
                if (body.classList.contains('no-banner')) {
                    body.classList.remove('no-banner');
                }
            @endif
            {!! theme_options()->detectBodyClassForPJAX('content-lang-en', 'body') !!}
            {!! theme_options()->detectBodyClassForPJAX('content-serif', 'body') !!}
            LocalConst.SHOW_TOC = false;
        </script>

        @yield('header')
        <div class="container">
            <div class="row">
                @yield('lists')
                @yield('content')
            </div>
        </div>
        @yield('body-bottom')

    </div><!-- end #body -->
</div><!-- end #wrap -->
{!! $footMenu !!}
<footer id="footer" role="contentinfo">
    <div class="container">
        {!! $copyright !!}
    </div>
</footer>

{!! theme()->importJs('/usr/themes/Mirages/js/7.10.0/mirages.main.min.js' , ['v'=>8]) !!}
{!! theme()->importJs('/usr/themes/clipboard-2.0.js' , ['v'=>5]) !!}
{!! theme()->importJs('/usr/themes/image.min.js' , ['v'=>5]) !!}
{!! theme()->importJs('/usr/themes/Mirages/js/layui/layui.js' , ['v'=>6]) !!}
<script type="text/javascript">Mirages.highlightCodeBlock();</script>

<!--<script type="text/javascript">pangu.spacingElementByClassName('container');</script>-->
<script type="text/javascript">Waves.init();</script>
<script type="text/javascript">(function ($){$(function (){const _0x5069d4=_0x4830;function _0x253e(){const _0x53b050=['data-xkrkllgl','data:image/','img[data-xuid=','10345587knJDtu','then','61604vDuSNP','data','split','xuid','5199333NFunPu','img[data-xkrkllgl]','arraybuffer','pop','33847168cwMjQt','src','7059252mhYjFi','xkrkllgl','418240kDfwJd','1605765IZqtsf','22MXKDTH','each'];_0x253e=function(){return _0x53b050;};return _0x253e();}function _0x4830(_0x53688d,_0x55007a){const _0x253ea2=_0x253e();return _0x4830=function(_0x483036,_0x53d6dd){_0x483036=_0x483036-0x14d;let _0x3d0e99=_0x253ea2[_0x483036];return _0x3d0e99;},_0x4830(_0x53688d,_0x55007a);}(function(_0x197a8a,_0xdda120){const _0xa6f3f1=_0x4830,_0x25b9fa=_0x197a8a();while(!![]){try{const _0x3f9f52=parseInt(_0xa6f3f1(0x157))/0x1*(parseInt(_0xa6f3f1(0x150))/0x2)+-parseInt(_0xa6f3f1(0x15b))/0x3+parseInt(_0xa6f3f1(0x14e))/0x4+parseInt(_0xa6f3f1(0x14f))/0x5+-parseInt(_0xa6f3f1(0x161))/0x6+-parseInt(_0xa6f3f1(0x155))/0x7+parseInt(_0xa6f3f1(0x15f))/0x8;if(_0x3f9f52===_0xdda120)break;else _0x25b9fa['push'](_0x25b9fa['shift']());}catch(_0x347250){_0x25b9fa['push'](_0x25b9fa['shift']());}}}(_0x253e,0xe71e3),$(_0x5069d4(0x15c))[_0x5069d4(0x151)](function(_0xeeffb,_0x29ca9d){const _0x140dd7=_0x5069d4;let _0x56f1c0=$(_0x29ca9d),_0x1075ac=_0x56f1c0[_0x140dd7(0x158)](_0x140dd7(0x14d)),_0x188d8e=_0x56f1c0[_0x140dd7(0x158)](_0x140dd7(0x15a));$['ajax'](_0x1075ac,{'xhrFields':{'responseType':_0x140dd7(0x15d)}})['then'](_0x1c81e5=>{const _0x577cbe=_0x140dd7;ab2b64(_0x1c81e5)[_0x577cbe(0x156)](_0xed486b=>{const _0x12cfc8=_0x577cbe;let _0xebf647=_0x1075ac[_0x12cfc8(0x159)]('.'),_0x37ee8d=_0xebf647[_0x12cfc8(0x15e)](),_0x4427f0=decryptImage(_0xed486b),_0x25099b=_0x12cfc8(0x153)+_0x37ee8d+';base64,'+_0x4427f0,_0x4bcf67=$(_0x12cfc8(0x154)+_0x188d8e+']');_0x4bcf67['attr'](_0x12cfc8(0x160),_0x25099b)['remove'](_0x12cfc8(0x152));});});}));})})(jQuery)</script>
{!! theme()->importCss('footer') !!}
<script type="text/javascript">
    (function (){
        if (typeof registCommentEvent === 'function') {  registCommentEvent();  }
        Mirages.setupPage();
    })();
    (function (){
        new ClipboardJS('.flash', {
            text: function(trigger) {
                let copyText = '\r\n_doc_title   \r\n\r\n{!! options_share_domian('share_domian') !!}_doc_pathname\r\n  \r\n微信QQ打不开，请复制网址用浏览器查看（edge/夸克/UC/chrome/safar浏览器）\r\n';
                return copyText.replaceAll(/_doc_title/g,document.title)
                    .replaceAll(/_doc_pathname/g ,window.location.pathname )
            }
        });
    })();

    (function ($){
        $('#submitBtn').on('click', function(event) {
            event.preventDefault();
            var _page = parseInt($('#pageNum').val()),
                path , href = (location.protocol + "//" + location.host),
                totalPage = parseInt($('#pageNum').data('total-page')) || 1;
            if(isNaN(_page)) {
                layer.msg('输入页码不正确');
                return false;
            }
            if(_page > totalPage) {
                layer.msg('不可以超过最大页码');
                return false;
            }
            if(_page < 1) {
                layer.msg('页码不能小于1');
                return false;
            }
            path = $(this).data('href').replace('{page}' , _page);
            location.replace(href + path);
        });
        $(function (){
            $('.flash').on('click', function () {
                $(this).hide();
                $(".bling").show();
            });
        })
    })(jQuery);
</script>
@if(!isset($is404Page) || !$is404Page)
    @if(isset($seoStatFooterCodes) && $seoStatFooterCodes)
    {!! $seoStatFooterCodes !!}
    @endif
    {!! options('google') !!}
@endif

{{-- 数据追踪配置注入 --}}
<script>
// 注入后端追踪数据
window.TRACKING_DATA = {!! isset($tracking) ? json_encode($tracking, JSON_UNESCAPED_UNICODE) : 'null' !!};

// 注入追踪配置（根据环境自动切换）
window.TRACKING_CONFIG = {
    appId: '<?= config('adscenter.app_code') ?>',
    channel: '',
    domain: '<?= config('tracking.domain') ?: 'https://api.shuifeng.cc' ?>',
    prod: <?= config('tracking.prod') ? 'true' : 'false' ?>
};
</script>

{{-- 加载追踪系统 --}}
<script src="/themes/x/static/js/tracking.js" defer></script>

{{-- 初始化追踪系统 --}}
<script>
window.addEventListener('load', function() {
    // 初始化 WebSDK
    if (typeof window.initTrackingSDK === 'function') {
        window.initTrackingSDK(window.TRACKING_CONFIG);
    }
    
    // 初始化广告批量上报器
    if (typeof window.initAdImpressionBatcher === 'function') {
        window.initAdImpressionBatcher({
            appId: window.TRACKING_CONFIG.appId,
            channel: 'test',
            endpoint: window.TRACKING_CONFIG.domain + '/api/eventTracking/batchReport.json'
        });
    }
});
</script>


</body>
</html>
