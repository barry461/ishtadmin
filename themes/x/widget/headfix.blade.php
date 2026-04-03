@php
    $pageIs = function (...$args) { return call_user_func_array([$this, 'is'], $args); };
    $hasValue = function ($v) { return \Utils::hasValue($v); };
    $isTrue = function ($v) { return \Utils::isTrue($v); };
@endphp

<style>
    {{-- 页面布局类调整 --}}
    @if ($pageIs('@index', '@archive', '@category'))
        @media(max-width: 767px) {
        body.card #index, body.card #archive { padding: 4rem 3rem 3.5rem; }
        body.card .container { max-width: 710px; }
        body.card article .post-card-mask { background-color: rgba(0,0,0,.3); height: 12.5rem; }
    }
    @media (max-width: 336px) {
        body.card article .post-card-mask { height: 10.5rem; }
    }
    @endif

    {{-- Banner 弯曲样式 --}}
    @if (Mirages::$options->showBanner && Mirages::$options->showBannerCurveStyle == 1 && !defined('FULL_BANNER_DISPLAY'))
        #masthead::after, .inner::after {
        content: '';
        width: 150%;
        height: 4.375rem;
        background: #fff;
        left: -25%;
        bottom: -1.875rem;
        border-radius: 100%;
        position: absolute;
        z-index: -1;
    }
    body.theme-dark #masthead::after,
    body.theme-dark .inner::after {
        background-color: #2c2a2a;
    }
    @endif

    {{-- 单页内容宽度 --}}
    @if ($pageIs('post') || $pageIs('page'))
        #footer { padding: 1.25rem 0; }
    @if ($hasValue($this->fields->contentWidth))
@media(min-width: 62rem) {
        .container {
            max-width: {{ is_numeric($this->fields->contentWidth) ? $this->fields->contentWidth.'px' : $this->fields->contentWidth }};
        }
    }
    @endif
    @endif

    {{-- TOC 左侧布局 --}}
    @if (Mirages::$options->showTOCAtLeft)
        #post-menu { left: -17.5rem; right: initial; border-left: none; border-right: 1px solid #f0f0f0; }
    a#toggle-menu-tree { left: 0; right: initial; margin-left: -5rem; }
    @endif

    {{-- 文字对齐方式 --}}
    @if (in_array($this->fields->textAlign, ['left', 'center', 'right', 'justify']) || Mirages::$options->textAlign)
        .post-content p, .post-content blockquote, .post-content ul, .post-content ol {
        text-align: {{ $this->fields->textAlign ?? Mirages::$options->textAlign }};
    }
    @endif

    {{-- Banner 背景遮罩 --}}
    @if (
        Mirages::$options->showBanner &&
        ($isTrue($this->fields->headTitle) ||
        (intval($this->fields->headTitle) >= 0 && Mirages::$options->headTitle__isTrue) ||
        Mirages::$options->blogIntro__hasValue ||
        (!$this->is('index') && ($hasValue($this->fields->mastheadTitle) || $hasValue($this->fields->mastheadSubtitle))))
    )
        @unless($isTrue($this->fields->disableDarkMask))
        .inner {
        background-color: rgba(0, 0, 0, 0.25);
    }
    @endunless
        #masthead { min-height: 12.5rem; }
    @else
        @media screen and (max-width: 40rem) {
        #post article { margin-top: 2.6rem; }
    }
    @endif

</style>

{{-- 用户自定义 CSS --}}
@if ($hasValue($this->fields->css))
    <style>
        {!! $this->fields->css !!}
    </style>
@endif
