<?php
/**
 * @var ContentsModel $content
 * @var ContentsModel $prev
 * @var ContentsModel $next
 */
use tools\Markdown;
?>
@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@section('content')
    <div id="post" role="main">
        <article class="post page" itemscope itemtype="http://schema.org/BlogPosting" style="margin-bottom: 20px;">
            <div class="display-none" itemscope itemprop="author" itemtype="http://schema.org/Person">
                <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                <meta itemprop="url" content="{{ $author->url() }}"/>
            </div>

            <div class="display-none" itemscope itemprop="publisher" itemtype="http://schema.org/Organization">
                <meta itemprop="name" content="{{ $content->authorValue() }}"/>
                <div itemscope itemprop="logo" itemtype="http://schema.org/ImageObject">
                    <meta itemprop="url" content="{{ $gravatar_url }}">
                </div>
            </div>

            <meta itemprop="url mainEntityOfPage" content="{{ $content->url() }}"/>
            <meta itemprop="datePublished" content="{{ $content->created ? date('c', strtotime($content->created)) : '' }}">
            <meta itemprop="dateModified" content="{{ $content->modified ? date('c', strtotime($content->modified)) : '' }}">
            <meta itemprop="headline" content="{{ $content->title}}">
            <meta itemprop="image" content="{{ $content->fieldValue('banner') }}">

            @if ( !(theme_options()->showBanner && $headTitle) )
                <h1 class="post-title {{ post_title_class($content->title) }}" itemprop="name headline">
                    {!! $content->biaoqingTitle() !!}
                    @if(!empty($user) && $user->hasLogin())
                        <a class="superscript" href="{{ options('adminUrl') }}write-page.php?cid={{ $cid }}"
                           target="_blank">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a>
                    @endif
                </h1>
                <ul class="post-meta">
                    <li><a href="{{$author->url()}}">{!! $content->authorValue() !!}</a> •</li>
                    <li>
                        <time>{{$content->date(I18n::dateFormat())}}</time>
                    </li>
                    @if($content->viewNum > 0)
                        <li> • {{_mt('阅读: %d', $content->viewNum)}}</li>
                    @endif
                    {!! $content->getCategoryNamesString(', ' , true) !!}

                    @if(theme_options()->hideReadSettings && theme_options()->navbarStyle != 1)
                        <li> • <a href="javascript:void(0)" id="page-read-setting-toggle">阅读设置</a></li>
                    @endif
                    @if(!empty($user) && $user->hasLogin())
                        <li class="edit"> •
                            <a href="{{theme_options()->adminUrl}}write-post.php?cid={{$content->cid}}" target="_blank">编辑</a>
                        </li>
                    @endif
                </ul>
            @endif

            @if (!$content->fieldValue('hide_ads'))
                @include('widget.post_img_top')
            @endif

            <div class="post-content" itemprop="articleBody">
                @if (!$content->fieldValue('hide_ads'))
                    @include('widget.post_btn_top')
                @endif
                {!! Markdown::convert(options('before_append')) !!}
                {!! html_entity_decode($content->content) !!}
                    <div class="tags">
                        <div itemprop="keywords" class="keywords YkziYC">
                            @if($tags)
                            @foreach($tags as $tag)
                                <a href="{{$tag->url()}}">{{$tag->name}}</a>
                            @endforeach
                            @endif
                        </div>
                        <div class="modify-time">
                            {{ _mt('最后编辑于: %s', $content->modified ? date(I18n::dateFormat(), strtotime($content->modified)) : '') }}
                        </div>
                    </div>

                    @if(!$content->fieldValue('hide_notice'))
                        {!! replace_share(options('content_after')) !!}
                        @if($show_share_logo == '1')
                            <div id="a2a-share-widget" style="display: none;">
                                @include('widget.logo')
                            </div>
                            <script>
                            (function() {
                                function moveShareToNotice() {
                                    var shareWidget = document.getElementById('a2a-share-widget');
                                    if (!shareWidget) return;

                                    // 查找 class="content-tab-content selected" 的div
                                    var noticeContainer = document.querySelector('.content-tab-content.selected');
                                    if (!noticeContainer) {
                                        // 如果没找到，尝试查找只有 content-tab-content 的
                                        noticeContainer = document.querySelector('.content-tab-content');
                                    }

                                    if (noticeContainer) {
                                        shareWidget.style.display = 'block';
                                        noticeContainer.appendChild(shareWidget);
                                    } else {
                                        // 如果没找到，直接显示
                                        shareWidget.style.display = 'block';
                                    }
                                }

                                // 等待DOM加载完成后执行
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', moveShareToNotice);
                                } else {
                                    setTimeout(moveShareToNotice, 300);
                                }
                            })();
                            </script>
                        @endif
                    @endif
            </div>

            <div class="flash">点击分享给色友</div>
            <div class="bling" style="display:none;">
                <span class="bling1">复制成功，打开微信，发给色友</span>
            </div>
        </article>
    </div>
    {!! theme()->linkCss('/usr/plugins/DPlayer/assets/DPlayer.min.css' , ['v'=>2]) !!}
    {!! theme()->importJs('/usr/plugins/DPlayer/plugin/hls.min.js',['v'=>1]) !!}
    {!! theme()->importJs('/usr/plugins/DPlayer/assets/DPlayer.min.js',['v'=>20251103]) !!}
    {!! theme()->importJs('/usr/plugins/DPlayer/assets/player.js',['v'=>1]) !!}
@endsection

@section('body-bottom')
    <div class="container">
        {{-- 文章详情页底部追加内容 --}}
        @if(!empty($articleBottomContent))
            {!! $articleBottomContent !!}
        @endif

        @if($prev || $next)
        <div class="post-near">
            <nav>
                @if($prev)
                    <span class="prev">
                    <a href="{!! $prev->url() !!}" title="{{$prev->title}}">
                        <span class="post-near-span">
                            <span class="prev-t no-user-select color-main">上一篇: </span><br>
                            <span>{{$prev->title}}</span>
                        </span>
                    </a>
                </span>
                @endif
                @if($next)
                <span class="prev">
                    <a href="{!! $next->url() !!}" title="{{$next->title}}">
                        <span class="post-near-span">
                            <span class="prev-t no-user-select color-main">下一篇: </span><br>
                            <span>{{$next->title}}</span>
                        </span>
                    </a>
                </span>
                @endif
            </nav>
        </div>
        @endif

        @include('widget.article_bottom_ads')
        {{--@include("widget.txt_btn_ads")--}}
        @include("widget.comment")
    </div>
     
@endsection