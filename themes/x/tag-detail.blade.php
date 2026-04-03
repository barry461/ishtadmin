@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@if(is_url('tag.detail'))
@section('header')
<header id="masthead" class="align-center align-middle no-banner-image"
    style="height: {{theme_options()->defaultBgHeight}};">
    <div class="blog-background"></div>
    <script type="text/javascript">
        var head = document.querySelector("#masthead");
                var bgHeight = getBgHeight(window.innerHeight, '20', '20');
                head.style.height = bgHeight + "px";
    </script>

    <div class="inner" style="padding-top: 88px; ">
        <div class="container">
            <h1 class="blog-title" style="">{{ $slugname }} </h1>
{{--            <h2 class="blog-description " style=""></h2>--}}
        </div>

    </div>

    <script>
        var navContainer = document.querySelector("#navbar");
                var headerContainer = document.querySelector("#masthead .inner");
                headerContainer.style.paddingTop = navContainer.offsetHeight + "px";
    </script>
</header>
@endsection
@endif
@section('lists')

<div id="archive" role="main">
    @foreach($lists as $key => $item)
    <article itemscope itemtype="http://schema.org/BlogPosting" class="">
        <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
            <meta itemprop="name" content="{{ $item->author->screenName }}" />
            <meta itemprop="url" content="{{ $item->url() }}" />
        </div>
        <div class="display-none" itemscope itemprop="publisher" itemtype="http://schema.org/Organization">
            <meta itemprop="name" content="{{ $item->author->screenName }}" />
            <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                <meta itemprop="url" content="{{ view()->image('51cg.png?s=50&r=G&d=') }}">
            </div>
        </div>
        <meta itemprop="url mainEntityOfPage" content="{{ $item->url() }}" />
        <meta itemprop="dateModified" content="{{ $item->date('c') }}">

        @php
        $isAd = $item->fieldValue('ads_field');
        $targetAttr = $isAd ? 'target="_blank"' : '';
        @endphp

        <a href="{{ $item->url() }}" {!! $targetAttr !!}>
            <div class="post-card">
                <div class="blog-background lazy-bg" data-bg="{{$item->fieldValue('banner')}}"></div>
                <div class="post-card-mask {{ $isAd ? 'post-card-ads' : '' }}">
                    <div class="post-card-container">
                        <h2 class="post-card-title" itemprop="headline">
                            @if(!$isAd)
                            {{ $item->title }}
                            @endif

                            @if($item->fieldValue('hotSearch'))
                            <div class="wrap"><span class="wraps">热搜 HOT</span></div>
                            @endif
                        </h2>

                        <div class="post-card-info">
                            @if(!$isAd)
                            <span itemprop="author" itemscope itemtype="http://schema.org/Person">{{
                                $item->author->screenName }} • </span>
                            <span itemprop="datePublished" content="{{ $item->date('c') }}">{{ $item->date('Y年m月d日') }}
                                • </span>
                            <span>{{ $item->getCategoryNamesString(' , ') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </article>
    @endforeach

    @include("widget.page")
    @include("widget.guide")
    @include("widget.ads")
</div>
</div>

@endsection