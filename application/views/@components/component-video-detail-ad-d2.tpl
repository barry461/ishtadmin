<!-- 详情页底部D2 -->
<div class="adaptation ad-wrap">
    {foreach name="video_detail_ad_d2_list" item="e"}
    <a href="{$e.url|default='#'}" rel="nofollow" class="img click_btn tjtagmanager" target="_blank" data-bid="{$e.bid|default=''}" data-aid="1" data-event="ad_click" data-page_key="btn_ads_10" data-page_name="详情页-底部-D2" data-ad_slot_key="btn_ads_10" data-ad_slot_name="详情页-底部-D2" data-ad_id="{$e.ad_id|default=''}" data-creative_id="" data-ad_type="btn_ads_10" data-seen="true">
        <img class="zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
    </a>
    {/foreach}
</div>


