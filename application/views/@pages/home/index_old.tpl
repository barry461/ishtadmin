<!-- 模板引用 start -->
{layout name="@layout/default-layout" /}
<!-- 模板引用 end -->

<div class="main-container homepagebox">

    <div id="home-main-content">

        <!-- banner start -->
        {include file="@components/component-home-banner" /}
        <!-- banner end -->
        

        <!-- 广告列表 start -->
        {include file="@components/component-ad-list" /}
        <!-- 广告列表 end -->


        <!-- 首页列表 start -->
        <div class="home-list-title ">
            <div class="title-left">
                <h2>中出</h2>
            </div>
            <div class="title-right">
                <a href="/home/sort/month_hot/" class="more-text">
                    <span>更多</span>
                    <img src="__ROOT_PATH__/__base/images/icon-arror-r.png" title="更多" alt="更多" />
                </a>
            </div>
        </div>
        <div class="slf-video-list-4">
            {include file="@components/component-video-card-list2" /}
        </div>
        <!-- 首页列表 end -->

        <!-- 影片主题 start -->
        {include file="@components/component-home-theme-list" /}
        <!-- 影片主题 end -->


        <!-- 首页列表 start -->
        <div class="home-list-title ">
            <div class="title-left">
                <h2>热门影片</h2>
            </div>
            <div class="title-right">
                <a href="/home/sort/hot/" class="more-text">
                    <span>更多</span>
                    <img src="__ROOT_PATH__/__base/images/icon-arror-r.png" title="更多" alt="更多" />
                </a>
            </div>
        </div>
        <div class="slf-video-list-4">
            {include file="@components/component-video-card-list2"/}
        </div>
        <!-- 首页列表 end -->

        <!-- 广告列表 start -->
        {include file="@components/component-ad-list" /}
        <!-- 广告列表 end -->

        <div class="home-list-title ">
            <div class="title-left">
                <h2>推荐</h2>
            </div>
        </div>
        <!-- 广告推荐 start -->
        {include file="@components/component-ad-icon-flex" /}
        <!-- 广告推荐 end -->
    </div>

</div>
