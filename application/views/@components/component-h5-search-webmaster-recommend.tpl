<div class="search-apps-box">
    <h3>站长推荐</h3>
    <div class="search-apps-list">
        {foreach name="h5_webmaster_recommend_list" item="e"}
        <a class="search-apps-item tjtagmanager" href="{$e.url|default='#'}"{if isset($e.rel_nofollow) && $e.rel_nofollow} rel="nofollow"{/if} data-event="ad_click" data-page_key="btn_ads_5" data-page_name="首页-站长推荐-A5" data-ad_slot_key="btn_ads_5" data-ad_slot_name="首页-站长推荐-A5" data-ad_id="{$e.ad_id|default=''}" data-creative_id="" data-ad_type="btn_ads_5">
            <div class="app-logo">
                <img z-image-loader-url="{$e.img|default=''}" alt="{$e.name|default=''}" class="">
            </div>
            <span>{$e.name|default=''}</span>
        </a>
        {/foreach}
    </div>
</div>
