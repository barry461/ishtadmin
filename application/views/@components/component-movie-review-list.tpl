
<!-- AV影评列表组件 -->
<section class="mv-review-grid">
    <article class="mv-review-card"
        v-for="(item, index) in reviewList" :key="`review_${index}`"
        itemscope itemtype="https://schema.org/NewsArticle"
    >
        <a :href="item.url || '/home/filmReviewDetail'" class="mv-review-card-link" target="_blank">
            <div class="mv-review-card-cover">
                <img :z-image-loader-url="item.cover" :alt="item.title" />
            </div>
            <div class="mv-review-card-body">
                <h3 class="mv-review-card-title" itemprop="headline" v-text="item.title"></h3>
                <p class="mv-review-card-desc" v-text="item.desc"></p>
            </div>
        </a>
    </article>
</section>
