{layout name="@layout/default-layout" /}
<div class="popular-site">
<section class="content-header">
    <div class="container">
        {include file="@components/component-ad-list-c1" /}
        <div class="title-with-avatar center popular-header">
            <div class="title-box">
                <h1 class="h3-md mb-1 popular-header-title">
                    热门影片
                </h1>
                <span class="inactive-color fs-2 mb-0 popular-header-count">
                    {$video_count|default='28224'} 部影片
                </span>
            </div>
        </div>
        <nav class="sorting-nav popular-sort-nav">
            <ul id="list_videos_common_videos_list_sort_list" class="sorting-nav-list">
                <li{eq name="sort" value="all"} class="active"{/eq}>
                    <a href="/popular/all/1">所有时间热门</a>
                </li>
                <li{eq name="sort" value="month"} class="active"{/eq}>
                    <a href="/popular/month/1">本月热门</a>
                </li>
                <li{eq name="sort" value="week"} class="active"{/eq}>
                    <a href="/popular/week/1">本周热门</a>
                </li>
                <li{eq name="sort" value="today"} class="active"{/eq}>
                    <a href="/popular/today/1">今日热门</a>
                </li>
            </ul>
        </nav>
    </div>
</section>
{include file="@components/component-video-popular-list" /}
<div class="container ad-e2-bottom">
    {include file="@components/component-ad-list-E2" /}
</div>
</div>