{layout name="@layout/default-layout" /}

{include file="@components/component-index-popup-ad" /}

{include file="@components/component-index-carousel" /}
<div class="container">
    <!-- 首页A2广告 -->
        <section class="py-3 pb-e-lg-40">
        {include file="@components/component-index-ad-a2" /}
        <!-- 今日更新 -->
        <div class="title-with-more title-today-update">
            <div class="title-box">
                <h2 class="h3-md">
                    今日更新
                </h2>
            </div>
            <div class="more">
                <a href="/new/1">
                    更多
                    <svg aria-hidden="true" class="pl-1" height="20" width="20">
                        <use xlink:href="#icon-arrow-right">
                        </use>
                    </svg>
                </a>
            </div>
        </div>
        {include file="@components/component-index-video-list-today" /}
    </section>
    <!-- 中文字幕 -->
    <section class="pb-3 pb-e-lg-40">
        <div class="title-with-more">
            <div class="title-box">
                <h2 class="h3-md">
                    中文字幕
                </h2>
            </div>
            <div class="more">
                <a href="/theme/detail/3/hot">
                    更多
                    <svg aria-hidden="true" class="pl-1" height="20" width="20">
                        <use xlink:href="#icon-arrow-right">
                        </use>
                    </svg>
                </a>
            </div>
        </div>
        {include file="@components/component-index-video-list-chinese" /}
    </section>
    <!-- 巨乳 -->
    <section class="pb-3 pb-e-lg-40">
        <div class="title-with-more">
            <div class="title-box">
                <h2 class="h3-md">
                    巨乳
                </h2>
            </div>
            <div class="more">
                <a href="/tags/170/hot">
                    更多
                    <svg aria-hidden="true" class="pl-1" height="20" width="20">
                        <use xlink:href="#icon-arrow-right">
                        </use>
                    </svg>
                </a>
            </div>
        </div>
        {include file="@components/component-index-video-list-busty" /}
    </section>
    <!-- 女优 波多野结衣 -->
    <section class="pb-3 pb-e-lg-40">
        <div class="title-with-more">
            <div class="title-box">
                <h2 class="h3-md">
                    波多野结衣
                </h2>
            </div>
            <div class="more">
                <a href="/actress/detail/3080/hot">
                    更多
                    <svg aria-hidden="true" class="pl-1" height="20" width="20">
                        <use xlink:href="#icon-arrow-right">
                        </use>
                    </svg>
                </a>
            </div>
        </div>
        {include file="@components/component-index-video-list-actress" /}
    </section>
    {include file="@components/component-index-release-new" /}
    <!-- 影片主题 + 专题合集 -->
    <section class="pb-3 pb-e-lg-40">
        <div class="row">
            {include file="@components/component-index-theme-list" /}
            {include file="@components/component-index-special-collection" /}
        </div>
    </section>
    {include file="@components/component-index-popular-videos" /}
    <!-- 他们在看 + A4广告 -->
    <section class="pb-3 pb-e-lg-40">
        {include file="@components/component-index-watching" /}
