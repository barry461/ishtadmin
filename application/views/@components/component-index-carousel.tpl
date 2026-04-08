<!-- 精选轮播 Banner -->
<div class="jable-carousel jable-animate overflow-h" data-animation="slideRight" data-animation-item=".item" data-auto-width="no" data-dots="no" data-loop="yes" data-center="yes" data-items-responsive="0:2|992:4">
    <div class="gutter-20 gutter-xl-30 pb-3">
        <div class="owl-carousel advertise-owl-carousel">
            {foreach name="carousel_list" item="e"}
            <div class="item">
                <div class="video-img-box">
                    <div class="img-box">
                        <a href="{$e.url|default='#'}">
                            <img class="zximg" src="{$e.img|default=''}" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
                            <div class="ribbon-top-left">
                                精選
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
</div>
