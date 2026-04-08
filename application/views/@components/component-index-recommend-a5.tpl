<!-- 首页站长推荐A5 -->
<div class="adaptation recommend-wrap">
    <div class="title">站长推荐</div>
    <div class="apps">
        {foreach name="recommend_a5_list" item="e"}
        <a href="{$e.url|default='#'}" rel="nofollow" class="app click_btn tjtagmanager" target="_blank" data-bid="{$e.bid|default=''}" data-aid="1" data-event="ad_click" data-page_key="btn_ads_5" data-page_name="首页-站长推荐-A5" data-ad_slot_key="btn_ads_5" data-ad_slot_name="首页-站长推荐-A5" data-ad_id="{$e.ad_id|default=''}" data-creative_id="" data-ad_type="btn_ads_5">
            <div class="icon">
                <img class="zximg" src="{$e.img|default=''}" z-image-loader-url="{$e.img|default=''}" alt="{$e.name|default=''}">
            </div>
            <div class="name">{$e.name|default=''}</div>
        </a>
        {/foreach}
    </div>
</div>
