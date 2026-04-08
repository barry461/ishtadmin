<!-- 模板引用 start -->
{layout name="@layout/default-layout" /}
<!-- 模板引用 end -->

<div class="main-container homepagebox">
    <div id="home-main-content" class="tags-detail-site" data-type-id="{$type_id}">

        <!-- E1 广告 start -->
        {include file="@components/component-ad-list" /}
        <!-- E1 广告 end -->

        <!-- 页面标题 start -->
        <div class="tags-detail-header">
            <h1 class="tags-detail-title">{{tagInfo.name}} 相关的AV在线观看</h1>
            <span class="tags-detail-count">{{tagInfo.total}} 部影片</span>
        </div>
        <!-- 页面标题 end -->

        <!-- 标签简介 start -->
        <div class="tags-detail-desc" v-if="tagInfo.desc">
            <h2 class="tags-detail-desc-label">标签简介</h2>
            <span class="tags-detail-desc-text">{{tagInfo.desc}}</span>
        </div>
        <!-- 标签简介 end -->

        <!-- 排序标签 start -->
        <nav class="sorting-nav">
            <ul class="sorting-nav-list">
                <li v-for="tab in sortTabs" :key="tab.key"
                    :class="{ active: currentSort === tab.key }">
                    <a href="javascript:void(0)" @click.prevent="currentSort = tab.key">{{tab.label}}</a>
                </li>
            </ul>
        </nav>
        <!-- 排序标签 end -->

        <!-- 视频列表 start -->
        <div class="slf-video-list-4">
            <section class="slf-video-list-grid">
                <article class="video-card-item"
                         v-for="video in videoList" :key="video.code"
                         aria-label="视频详情" itemscope itemtype="https://schema.org/VideoObject">
                    <a :href="'/videos/' + video.code" class="video-card-link">
                        <div class="video-card-image-box">
                            <div class="video-card-image">
                                <img :z-image-loader-url="video.cover" :alt="video.title" />
                                <div class="video-card-detail">
                                    <span v-if="video.subtitle" class="video-card-tag">{{video.subtitle}}</span>
                                    <div class="video-card-time">{{video.duration}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="video-card-info">
                            <h3 class="video-card-title text-line-ellipsis-1" itemprop="headline">{{video.title}}</h3>
                            <p class="video-card-meta">
                                <span class="meta-views">{{video.views}}</span>
                                <span class="meta-likes">{{video.likes}}</span>
                            </p>
                        </div>
                    </a>
                </article>
            </section>
        </div>
        <!-- 视频列表 end -->

        <!-- 分页 start -->
        {include file="@components/component-pagination" /}
        <!-- 分页 end -->

        <!-- E2 广告 start -->
        {include file="@components/component-ad-list" /}
        <!-- E2 广告 end -->

    </div>
</div>
