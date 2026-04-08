<!-- 全新上市 -->
<section class="py-3 pb-e-lg-40">
    <div class="title-with-more">
        <div class="title-box">
            <h2 class="h3-md">
                全新上市
            </h2>
        </div>
        <div class="more">
            <a href="/release">
                更多
                <svg aria-hidden="true" class="pl-1" height="20" width="20">
                    <use xlink:href="#icon-arrow-right">
                    </use>
                </svg>
            </a>
        </div>
    </div>
    <div class="row gutter-20">
        {foreach name="release_new_list" item="e"}
        <div class="col-4 col-sm-3 col-lg-2">
            <div class="video-img-box">
                <div class="img-box cover-half">
                    <a href="{$e.url|default='#'}">
                        <img class="zximg" src="{$e.img|default=''}" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
                    </a>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</section>
