<?php
/**
 * @var ContentsModel $content
 */

?>
@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@section('content')
    <div id="archive" role="main">
        @if (theme_options()->useCardView)
            <article itemscope itemtype="http://schema.org/BlogPosting"
                     class="{{ Utils::isTrue($content->fieldValue('disableDarkMask')) ? 'no-mask' : '' }}">
                <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
                    <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                    <meta itemprop="url" content="{{ $content->authorValue('url') }}"/>
                </div>
                <div class="display-none" itemscope itemprop="publisher" itemtype="http://schema.org/Organization">
                    <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                    <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                        <meta itemprop="url" content="{{ $gravatar_url }}">
                    </div>
                </div>
                <meta itemprop="url mainEntityOfPage" content="{{ $content->url() }}"/>
                <meta itemprop="dateModified" content="{{ $content->date('c') }}">
                @php
                    $style = "";
                    $banner = false;
                    $bannerPosition = "";
                    if ($content->fieldValue('banner')) {
                        $banner = Utils::randomBanner($content->fieldValue('banner'));
                    } else {
                        if (theme_options()->enableLoadFirstImageFromArticle) {
                            $banner = Utils::loadFirstImageFromArticle($content->content);
                        }
                        if ($banner === false) {
                            $banner = Utils::loadDefaultThumbnailForArticle($content->cid);
                        }
                    }
                    if (!Utils::hasValue($banner)) {
                        $bgArray = Utils::randomBackgroundColor($content->cid);
                        if (is_array($bgArray) && !empty($bgArray)) {
                            $bg = join(',', $bgArray);
                            $style = "style=\"background: {$bgArray[0]};background: -webkit-linear-gradient(90deg, {$bg}); background: linear-gradient(90deg, {$bg});\"";
                        }
                    } else {
                        list($banner , $bannerPosition) = Utils::getBannerPosition($banner);
                    }
                @endphp
                <a href="{{ $content->url() }}" @if ($outjump) target="_blank" @endif>
                    <div class="post-card" id="post-card-{{ $content->cid }}" {!! $style !!}>
                        @if ($banner)
                            @if (theme_options()->enableLazyLoad)
                                <div class="blog-background"></div>
                                <div class="lazyload-container"></div>
                                <script type="text/javascript">registLoadBanner();</script>
                                <meta itemprop="image" content="{{ $banner }}">
                                <meta itemprop="thumbnailUrl" content="{{ $banner }}">
                                <img alt="{{ $content->title }}" src="{{ $banner }}" style="display: none"
                                     onload="javascript:loadBanner(this, '{{ $banner }}', '{{ $bannerPosition }}', document.querySelector('#post-card-{{ $content->cid }}'), '-1', document.querySelector('#post-card-{{ $content->cid }}').offsetWidth, document.querySelector('#post-card-{{ $content->cid }}').offsetHeight)">
                            @else
                                <div class="blog-background"></div>
                                <script type="text/javascript">
                                    loadBannerDirect('{{ $banner }}', '{{ $bannerPosition }}', document.querySelector('#post-card-{{ $content->cid }}'), '-1', document.querySelector('#post-card-{{ $content->cid }}').offsetWidth, document.querySelector('#post-card-{{ $content->cid }}').offsetHeight);
                                </script>
                            @endif
                        @endif
                        <div class="post-card-mask {{ $is_ads ? 'post-card-ads' : '' }}">
                            <div class="post-card-container">
                                @unless ($content->fieldValue('hide_list_title'))
                                    <h2 class="post-card-title"
                                        itemprop="headline">{{ $content->biaoqingTitle() }}</h2>
                                @endunless
                                <div class="post-card-info">
                                    @unless ($content->fieldValue('hide_list_author'))
                                        @if (theme_options()->userNum > 1)
                                            <span itemprop="author" itemscope itemtype="http://schema.org/Person">{{ $content->authorValue() }} • </span>
                                        @endif
                                        <span itemprop="datePublished" content="{{ $content->date('c') }}">{{ $content->date(I18n::dateFormat()) }} • </span>
                                        <span>{!! $content->getCategoryNamesString(', ') !!}</span>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @else
            <article itemscope itemtype="http://schema.org/BlogPosting">
                <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
                    <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                    <meta itemprop="url" content="{{ $content->authorValue('url') }}"/>
                </div>
                <div class="display-none" itemscope itemprop="publisher" itemtype="http://schema.org/Organization">
                    <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                    <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                        <meta itemprop="url" content="{{ $gravatar_url }}">
                    </div>
                </div>
                <meta itemprop="url mainEntityOfPage" content="{{ $content->url() }}"/>
                <meta itemprop="dateModified" content="{{ $content->date('c') }}">
                <div class="post">
                    <a href="{{ $content->url() }}">
                        <h1 class="post-title" itemprop="headline">{{ $content->biaoqingTitle() }}</h1>
                    </a>
                    @unless($content->fieldValue('hide_post_info'))
                        <div class="post-info">
                            <span itemprop="datePublished" content="{{ $content->date('c') }}">{{ $content->date(I18n::dateFormat()) }} • </span>
                            <span class="comments">
                            <a href="{{$content->url()}}#disqus_thread" data-disqus-identifier="{{$content->url()->getPath()}}">{{_mt('评论')}}
                            </a>
                        </span>
                        </div>
                    @endunless
                    <div class="post-content" itemprop="description">
                        {!! $content->content !!}
                        @unless($content->fieldValue('hide_notice'))
                            {!! replace_share(options('content_after')) !!}
                        @endunless
                    </div>
                </div>
            </article>

            <div class="flash">点击分享给色友</div>
            <div class="bling" style="display:none;">
                <span class="bling1">复制成功，打开微信，发给色友</span>
            </div>
        @endif

        {{--        @include('component.jump_page')--}}
    </div>
    
@endsection
