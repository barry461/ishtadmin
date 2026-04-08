<div class="home-banner">
    <div class="swiper banner-swiper" data-swiper-autoplay="2000">
        <div class="swiper-wrapper">
            {foreach name="__LISTS__" item="e" key="i" }
                <div class="swiper-slide">
                    <a href="#" class="slide-box tjtagmanager"
                        data-event="ad_click"
                        data-ad_type="banner"
                        data-ad_position="content_banner"
                        data-template_id="模版"
                        target="_blank">
                        <img
                           x-image-loader-url="__IMAGE_DOMAIN__/upload_01/upload/20260211/2026021118222294366.jpeg" />
                    </a>
                </div>
            {/foreach}
        </div>
    </div>
</div>