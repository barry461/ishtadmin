<?php
/**
 * @var ContentsModel[] $lists
 */
?>
@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@section('lists')

<div class="container">
    <div class="row">
        <div id="index" role="main">
            <div id="archives" style="padding-bottom: 1rem;">
                <h1 id="archives-title">{{ $screenName }} 的文章</h1>
                @foreach($lists as $key => $item)
                    <article itemscope itemtype="http://schema.org/BlogPosting" class="">
                        <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
                            <meta itemprop="name" content="{{ $item->authorValue() }}" />
                            <meta itemprop="url" content="{{ $item->url() }}" />
                        </div>
                        <div class="display-none" itemscope itemprop="publisher" itemtype="http://schema.org/Organization">
                            <meta itemprop="name" content="{{ $item->authorValue() }}" />
                            <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                                <meta itemprop="url" content="{{ theme()->image('51cg.png?s=50&r=G&d=') }}">
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
                                @if($item->fieldValue('banner'))
                                    <div class="blog-background lazy-bg" data-bg="{{$item->fieldValue('banner')}}"></div>
                                @endif
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
                                                <span itemprop="author" itemscope itemtype="http://schema.org/Person">{{ $item->author->screenName }} • </span> 
                                                <span itemprop="datePublished" content="{{ $item->date('c') }}">{{ $item->date('Y年m月d日') }} • </span>
                                                <span>{{ category($item->relationships) }}</span>
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
    </div>
</div>

@endsection