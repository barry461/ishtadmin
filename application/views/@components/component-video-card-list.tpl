
<!-- 视频列表组件 -->
<section class="slf-video-list-grid">
    <article class="video-card-item"
        aria-label="视频详情" itemscope itemtype="https://schema.org/NewsArticle" v-for="video in 13" :key="`video_card_${video}`">
        <a href="/home/detail" class="video-card-link">
            <div class="video-card-image-box">
                <div class="video-card-image">
                    <img x-image-loader-url="https://pic.jkvgqc.cn/upload_01/upload/20251209/2025120916273190980.jpeg" />
                    <div class="video-card-detail">
                        <span></span>
                        <div class="video-card-time">1:00:00</div>
                    </div>
                </div>
            </div>
            <div class="video-card-info">
                <h3 class="video-card-title text-line-ellipsis-1" itemprop="headline">
                    视频标题视频标题视频标题视频标题视频标题视频标题视频标题视频标题视频标题视频标题</h3>
            </div>
        </a>
    </article>
</section>
{include file="components/component-pagination" /}
