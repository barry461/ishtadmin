<!-- 热门影片 -->
<section class="pb-3 pb-e-lg-40">
    <div class="title-with-more">
        <div class="title-box">
            <h2 class="h3-md">
                热门影片
            </h2>
        </div>
        <div class="more">
            <a href="/popular">
                更多
                <svg aria-hidden="true" class="pl-1" height="20" width="20">
                    <use xlink:href="#icon-arrow-right">
                    </use>
                </svg>
            </a>
        </div>
    </div>
    <div class="row gutter-20">
        {foreach name="popular_list" item="e"}
        <div class="col-6 col-sm-4 col-lg-3">
            <div class="video-img-box mb-e-20">
                <div class="img-box cover-md bind_video_img">
                    <a href="{$e.url|default='#'}">
                        <img class="zximg" src="{$e.img|default=''}" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
                        <div class="absolute-bottom-left">
                            {notempty name="e.chinese"}
                            <span class="chinese">{$e.chinese}</span>
                            {/notempty}
                        </div>
                        <div class="absolute-bottom-right">
                            <span class="label">{$e.duration|default=''}</span>
                        </div>
                    </a>
                </div>
                <div class="detail">
                    <h3 class="title">
                        <a href="{$e.url|default='#'}">{$e.title|default=''}</a>
                    </h3>
                    <p class="sub-title">
                        <svg aria-hidden="true" class="mr-1" height="15" width="15">
                            <use xlink:href="#icon-eye"></use>
                        </svg>
                        {$e.views|default='0'}
                        <svg aria-hidden="true" class="ml-3 mr-1" height="13" width="13">
                            <use xlink:href="#icon-heart-inline"></use>
                        </svg>
                        {$e.likes|default='0'}
                    </p>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</section>
