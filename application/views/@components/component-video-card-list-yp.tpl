
<!-- 视频列表组件 -->
<section class="slf-video-list-grid-yp">
    <article class="video-card-item"
        aria-label="视频详情" itemscope 
        itemtype="https://schema.org/NewsArticle" 
        v-for="video in 8" :key="`video_card_${video}`"
    >
        <a href="/home/filmReviewDetail" class="video-card-link">
            <div class="video-card-image-box">
                <div class="video-card-image">
                    <img x-image-loader-url="https://pic.jkvgqc.cn/upload_01/upload/20251209/2025120916273190980.jpeg" />
                </div>
            </div>
            <h3 class="video-card-title text-line-ellipsis-1">
                视频标题视频标题视频标题视频标题视频标题视频标题视频标题
            </h3>
            <p class="video-card-p">所得到的当时说的水电费多少是的是的是的是的但是是多少嘟嘟嘟嘟嘟嘟弹道导弹哒哒哒哒哒哒哒哒哒哒哒哒吨吨吨吨吨的的的的的</p>
        </a>
    </article>
</section>
