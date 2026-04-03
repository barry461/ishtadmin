<style type="text/css">

    @php
        /** @var ContentsModel $content */
        $content = $content ?? new EmptyObject();
        $textAlign = null;
        $headTitle = null;
        $mastheadTitle = null;
        $mastheadSubtitle = null;
        $disableDarkMask = null;
        if ($content){
            $textAlign = $content->fieldValue('textAlign');
            $headTitle = $content->fieldValue('headTitle');
            $mastheadTitle = $content->fieldValue('mastheadTitle');
            $mastheadSubtitle = $content->fieldValue('mastheadSubtitle');
            $disableDarkMask = $content->fieldValue('disableDarkMask');
        }

    @endphp

    @if (is_url('home','archive','category','category.page','search','search.page',
    'home.page','tag.detail','tag.list','history','history.page','authors', 'author.page'))
        {!! <<<CSS
        @media(max-width:767px){body.card #index,body.card #archive{padding:4rem 3rem 3.5rem;}body.card .container{max-width:710px;}body.card article{padding:.9375rem 0 1.25rem;}body.card article .post-card-mask{background-color:rgba(0,0,0,.3);height:12.5rem;}body.card article .post-card-container{padding:1rem;}body.card article .post-card-ads{background-color:rgba(0,0,0,.1);}body.card article .post-card-title{font-size:1.1625rem;}.page-navigator{margin-top:2rem;}}
        @media(max-width:390px){.page-jump input{max-width:3rem;}}
        @media(max-width:336px){body.card article .post-card-mask{height:10.5rem;}a.btn,.btn>a{padding:.4375rem 2.25rem;}.page-navigator{margin-top:1.5rem;}}
        @media(min-width:768px) and (max-width:1301px){body.card .container{max-width:720px;}}
        @media(min-width:1302px) and (max-width:1599px){body.card .container{max-width:864px;}}
        @media(min-width:1600px) and (max-width:1799px){body.card .container{max-width:896px;}}
        @media(min-width:1800px) and (max-width:1999px){body.card .container{max-width:960px;}}
        @media(min-width:2000px) and (max-width:2399px){body.card .container{max-width:992px;}}
        @media(min-width:2400px){body.card .container{max-width:1024px;}}
    CSS !!}
    @endif

    @if (theme_options()->showBanner && theme_options()->showBannerCurveStyle == 1 && !defined('FULL_BANNER_DISPLAY'))
        {!! <<<CSS
            #masthead::after,.inner::after{content:'';width:150%;height:4.375rem;background:#fff;left:-25%;bottom:-1.875rem;border-radius:100%;position:absolute;z-index:-1;}
            @media(max-width:25rem){#masthead::after,.inner::after{width:250%;left:-75%;bottom:-3.875rem;}}
            @media(min-width:25.0625rem) and (max-width:37.4375rem){#masthead::after,.inner::after{width:200%;left:-50%;bottom:-2.875rem;}}
            body.theme-dark #masthead,body.theme-dark #masthead::after,body.theme-dark .inner::after{background-color:#2c2a2a;box-shadow:none;}
            body.theme-sunset #masthead::after,body.theme-sunset .inner::after{background-color:#F8F1E4;}
            #post article{margin-top:-0.625rem;}#index{padding-top:0.375rem;}
        CSS;
         !!}
    @endif

    @if (is_url('post'))
        {!! 'div#comments{margin-top:0;} ' !!}
    @endif
    @if (!is_url('post') || Device::isPhone())
        {!! '#qr-box{background-color:transparent;}' !!}
    @endif
    @if (!(is_url('post') && !Device::isPhone() && (theme_options()->postQRCodeURL || theme_options()->rewardQRCodeURL)))
        {!! '.post-buttons,#qr-box{display:none;}#body-bottom{margin-top:0;}' !!}
    @endif

    @if (is_url('page','links'))
        {!!' #body .container{margin-top:3.125rem;}.row{margin-left:0;margin-right:0;}' !!}
    @endif

    @if (in_array($textAlign, ['left','center','right','justify']) || theme_options()->textAlign)
        @php
            $textAlign = $textAlign ?? theme_options()->textAlign;
        @endphp
        {!! <<<CSS
    .post-content p, .post-content blockquote, .post-content ul, .post-content ol, .post-content dl, .post-content table, .post-content pre
    {text-align: $textAlign;}
    CSS;
     !!}
    @endif

    @if (is_url('post','page'))
        {!! '#footer{padding:1.25rem 0;}' !!}
        @if ($content->fieldValue('contentWidth'))
            @php
                $contentWidth = intval(data_get($content , 'contentWidth')).'px';
            @endphp
            {!! "@media(min-width:62rem){.container{max-width:$contentWidth;}}"!!}
        @endif
    @endif


    @if (
    theme_options()->showBanner &&
    (
        $headTitle ||
        theme_options()->blogIntro ||
        (
            !is_url('index') &&
            ($mastheadTitle || $mastheadSubtitle)
        )
    )
)
        @unless (Utils::isTrue($disableDarkMask))
            {!! '.inner {background-color: rgba(0, 0, 0, 0.25);}' !!}
        @endunless
        {!! '#masthead {min-height: 12.5rem;}' !!}
    @else
        {!! '@media(max-width:40rem){#post article{margin-top:0.6rem;}}' !!}
    @endif

    @if (theme_options()->showBanner && is_url('page','about'))
        {!! '.blog-title{font-size:2.5rem;}' !!}
        @if (theme_options()->showBannerCurveStyle == 1)
            {!! '#masthead{min-height:21.875rem;}' !!}
        @else
            {!! '#masthead{min-height:18.75rem;}h1.blog-title{margin-bottom:-1.25rem;}' !!}
        @endif
    @endif

    @if (theme_options()->needHideToggleTOCBtn)
        {!! '@media(min-width:63rem){#toggle-menu-tree,h1>span.toc,h2>span.toc,h3>span.toc,h4>span.toc,h5>span.toc,h6>span.toc{display:none!important;}}' !!}
    @endif

    @if (theme_options()->showTOCAtLeft)
        {!! <<<CSS
        #post-menu{right:initial;left:-17.5rem;border-left:none;border-right:1px solid #f0f0f0;}
        body.theme-dark #post-menu{border-right:none;}
        a#toggle-menu-tree{left:0;margin-left:-5rem;text-align:right;}
        #wrap.display-menu-tree #toggle-menu-tree{margin-left:-1.5625rem;}
        #wrap.display-menu-tree #post-menu{transform:translateX(17.5rem);}
        body.display-menu-tree #footer{margin-left:17.5rem!important;}
        #wrap.display-menu-tree.display-nav #toggle-nav{transform:translateX(12.625rem);}
        CSS;
         !!}
    @endif
    {{-- 用户自定义追加 CSS --}}
    @if(is_url('slug' , 'detail'))
    {!! $content->fieldValue('css') !!}
    @endif
</style>