@extends('layouts.app')

@section("seo-head")
{!! $header ?? '' !!}
@endsection
@if(is_url('search','search.page') && !is_mobile())
    @section('header')
        <header id="masthead" class="align-center align-middle no-banner-image" style="height: {{theme_options()->defaultBgHeight}};">
            <div class="blog-background"></div>
            <script type="text/javascript">
                var head = document.querySelector("#masthead");
                var bgHeight = getBgHeight(window.innerHeight, '20', '20');
                head.style.height = bgHeight + "px";
                var banner = "{{ $banner }}" + getImageAddon("-1", window.screen.availWidth, window.screen.availHeight);
                loadBackgroundImage(banner ,head.querySelector(".blog-background") )
            </script>
          
            <div class="inner" style="padding-top: 88px; ">
                <div class="container">
                    <h1 class="blog-title" style=""> 包含关键字 {{ $keyword }} 的文章 </h1>
                    <!-- <h2 class="blog-description " style=""></h2> -->
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
    <div id="index" role="main" >
        
    @foreach($lists as $key => $item)
        <article itemscope itemtype="http://schema.org/BlogPosting">
            {{-- Author --}}
            <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
                <meta itemprop="name" content="{{ $item->author->screenName }}"/>
                <meta itemprop="url" content="{{ $item->url() }}"/>
            </div>

            {{-- Publisher --}}
            <div class="display-none" itemscope itemprop="publisher"
                 itemtype="http://schema.org/Organization">
                <meta itemprop="name" content="{{ $item->author->screenName }}"/>
                <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                    {!! view()->itemprop('51cg.png?s=50&amp;r=G&amp;d=') !!}
                </div>
            </div>  
    
            <meta itemprop="url mainEntityOfPage" content="{{ $item->url() }}"/>
            <meta itemprop="dateModified" content="{{ $item->date('c') }}">

            @php 
                $isAd = $item->fieldValue('ads_field');
                $cardId = 'post-card-' . $item->cid;
                $targetAttr = $isAd ? 'target="_blank"' : '';
                $bannerUrl = $item->fieldValue('banner');
            @endphp

            <a href="{{ $item->url() }}" {!! $targetAttr !!}
               data-type="keyword_click"
               data-keyword="{{ $keyword }}"
               data-id="{{ $item->cid }}"
               data-type-key="article"
               data-type-name="文章"
               data-position="{{ $key }}"
            >
                <div class="post-card lazy-bg" id="{{ $cardId }}" data-bg="{{$bannerUrl}}">
                    <div class="blog-background"></div>
                    <div class="post-card-mask {{ $isAd ? 'post-card-ads' : '' }}">
                        <div class="post-card-container">
                            <h2 class="post-card-title"
                                itemprop="headline">{{ $isAd?'':$item->title }}
                                @if($item->fieldValue('hotSearch'))
                                    <div class="wrap"><span class="wraps">热搜 HOT</span></div>
                                @endif</h2>
                            @unless($isAd)
                                <div class="post-card-info">
                                    <span itemprop="author" itemscope itemtype="http://schema.org/Person"> {{ $item->author->screenName }} • </span>
                                    <span itemprop="datePublished" content="{{ $item->date('c') }}"> {{ $item->date('Y年m月d日') }} •</span>
                                   
                                    <span>{{ $item->getCategoryNamesString(' , ') }}</span>
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>
            </a>
        </article>
    @endforeach
    </div>
    @include("widget.page")
    @include("widget.guide")
    @include("widget.ads")
    @if(is_url('home'))
        @include("widget.pop_ads")
    @endif
@endsection