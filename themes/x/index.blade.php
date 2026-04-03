@extends('layouts.app')

@section("seo-head")
{!! $header ?? '' !!}
@endsection

@section('header')
    <header id="masthead" class="align-center align-middle no-banner-image" style="height: {{theme_options()->defaultBgHeight}};">
        <div class="blog-background"></div>
        <script type="text/javascript">
            var head = document.querySelector("#masthead");
            var bgHeight = getBgHeight(window.innerHeight, '20', '20');
            //head.style.height = bgHeight + "px"; // 停用代码 by q
        </script>
        @php
            $showTitle = true;
            // 优先使用后台变量配置，每个字段独立fallback
            // 标题：变量 > 分类名 > 站点名
            if (!empty($homeHeaderTitle)) {
                $categoryName = $homeHeaderTitle;
            } elseif (!empty($meta)) {
                $categoryName = $meta->name;
            } else {
                $categoryName = options('title', '');
            }
            
            // 描述：变量 > 分类描述 > 站点描述
            if (!empty($homeHeaderDescription)) {
                $cateDescription = $homeHeaderDescription;
            } elseif (!empty($meta)) {
                $cateDescription = $meta->description;
            } else {
                $cateDescription = options('siteDes', '');
            }
            
            if(empty($categoryName) && empty($cateDescription)){
                $showTitle = false;
            }
        @endphp

        @if($showTitle)
            <div class="inner" style="padding-top: 0px; ">
                <div class="container">
                    <h1 class="blog-title" style=""> {{ $categoryName }} </h1>
                    <h2 class="blog-description " style="">{{ $cateDescription }}</h2>
                </div>

            </div>
        @endif
        @if(is_url('category','category.page') )
        <script>
            var navContainer = document.querySelector("#navbar");
            var headerContainer = document.querySelector("#masthead .inner");
            headerContainer.style.paddingTop = (navContainer.offsetHeight>30 ? navContainer.offsetHeight-30 :navContainer.offsetHeight) + "px";
        </script>
        @endif
    </header>
@endsection

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
                $isAd = $item->fieldValue('ads_field') || $item->fieldValue('hide_list_title') || $item->fieldValue('hide_list_author');
                $targetAttr = $isAd ? 'target="_blank"' : '';
                $hide_list_author_cate = (empty($item->fieldValue('hide_list_author_cate')));
            @endphp

            <a href="{{ $item->url() }}" {!! $targetAttr !!}>
                <div class="post-card">
                    <img class="blog-background" z-image-loader-url="{{$item->fieldValue('banner')}}" alt="" />
                    <div class="post-card-mask {{ $isAd ? 'post-card-ads' : '' }}">
                        <div class="post-card-container">
                            <h2 class="post-card-title"
                                itemprop="headline">{{ $isAd?'':$item->title }}
                                @if($item->fieldValue('hotSearch'))
                                    <div class="wrap"><span class="wraps">热搜 HOT</span></div>
                                @endif</h2>
                            @unless($isAd)
                                <div class="post-card-info">
                                   @if($hide_list_author_cate)
                                        <span itemprop="author" itemscope itemtype="http://schema.org/Person"> {{ $item->author->screenName }} • </span>
                                        <span itemprop="datePublished" content="{{ $item->date('c') }}"> {{ $item->date('Y年m月d日') }} •</span>

                                        <span>{{ $item->getCategoryNamesString(' , ') }}</span>
                                   @endif
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