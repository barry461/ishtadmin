<!-- 详情页右侧竖排视频列表 -->
<div class="col right-sidebar">
    <div class="row gutter-20" id="loading_more_data">
        {foreach name="video_detail_right_sidebar_list" item="e"}
        <div class="col-6 col-sm-4 col-lg-12 order-{$e.order}">
            <div class="video-img-box mb-e-20">
                <div class="img-box cover-md bind_video_img">
                    {if $e.type == 'ad'}
                    <a href="{$e.url|default='#'}" target="_blank" data-event="ad_click" data-page_key="article_right_ads" data-page_name="详情页右侧广告" data-ad_slot_key="article_right_ads_0" data-ad_slot_name="{$e.title|default=''}" data-ad_id="0" data-creative_id="" data-ad_type="article_right_ads">
                        <img class="zximg" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
                        <div class="absolute-bottom-left" data-id="0"></div>
                        <div class="absolute-bottom-right">
                            <span class="label">广告</span>
                        </div>
                    </a>
                    {else}
                    <a href="{$e.url|default='#'}" rel="follow">
                        <img class="zximg" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
                        <div class="absolute-bottom-left" data-id="{$e.data_id|default=''}">
                            {notempty name="e.chinese"}
                            <span class="chinese">{$e.chinese}</span>
                            {/notempty}
                            {notempty name="e.uncensored"}
                            <span class="uncensored">{$e.uncensored}</span>
                            {/notempty}
                        </div>
                        <div class="absolute-bottom-right">
                            <span class="label">{$e.duration|default=''}</span>
                        </div>
                    </a>
                    {/if}
                </div>
                <div class="detail">
                    <h3 class="title">
                        <a href="{$e.url|default='#'}">{$e.title|default=''}</a>
                    </h3>
                    <p class="sub-title">
                        {if $e.type == 'video'}
                        <svg aria-hidden="true" class="mr-1" height="15" width="15">
                            <use xlink:href="#icon-eye"></use>
                        </svg>
                        {$e.views|default='0'}
                        <svg aria-hidden="true" class="ml-3 mr-1" height="13" width="13">
                            <use xlink:href="#icon-heart-inline"></use>
                        </svg>
                        {$e.likes|default='0'}
                        {/if}
                    </p>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</div>
