<?php
/**
 * 后台文章编辑 - 前台预览页
 *
 * 变量：
 * @var string $title  文章标题
 * @var string $html   已渲染好的正文 HTML
 */
?>
@extends('layouts.app')

@section("seo-head")
    {!! $header ?? '' !!}
@endsection

@section('content')
    <div id="post" role="main">
        <article class="post page" style="margin-bottom: 20px;">
            <h1 class="post-title">
                {{ $title ?: '文章预览' }}
            </h1>
            <div class="post-content" itemprop="articleBody">
                {!! $html !!}
            </div>
        </article>
    </div>
@endsection

