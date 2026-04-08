
<!-- 视频留言列表组件 -->
<section class="ajw-video-list-grid-ly">
    <article class="video-card-item"
        aria-label="视频详情" itemscope 
        itemtype="https://schema.org/NewsArticle" 
        v-for="video in 8" :key="`video_card_${video}`"
    >
        <a href="/home/filmReviewDetail" class="video-card-link">
            <h3 class="video-card-title">060825_01 韩国妹子深喉口交震动棒</h3>
            
            <div class="video-card-body">
                <div class="video-card-image-box">
                    <img x-image-loader-url="https://pic.jkvgqc.cn/upload_01/upload/20251209/2025120916273190980.jpeg" alt="视频封面"/>
                </div>
                
                <div class="video-card-info-col">
                    <div class="info-item time">
                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-time@3x-light"></use></svg>
                        <span>1小时前</span>
                    </div>
                    <div class="info-item comments">
                         <svg class="icon" aria-hidden="true"><use xlink:href="#icon-comm@3x-light"></use></svg>
                         <span>0</span>
                    </div>
                    <div class="info-item views">
                         <svg class="icon" aria-hidden="true"><use xlink:href="#icon-view@3x-light"></use></svg>
                         <span>62757</span>
                    </div>
                </div>
            </div>

            <div class="video-card-divider"></div>

            <div class="video-card-message">
                <span class="label">留言:</span>
                <span>ggggggggggggggggggggggggg</span>
            </div>
        </a>
    </article>
</section>
