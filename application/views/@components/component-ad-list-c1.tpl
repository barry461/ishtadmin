<!-- C1广告 -->
<div class="slf-ad-list-type-a">
    {foreach name="ad_list_c1" item="e"}
    <a href="{$e.url|default='#'}" rel="nofollow" class="ad-item tjtagmanager" data-event="ad_click" data-ad_type="btn_ads_7" data-ad_position="热门影片-头部-C1" data-template_id="C1" data-bid="{$e.bid|default=''}" data-aid="1" data-page_key="btn_ads_7" data-page_name="热门影片-头部-C1" data-ad_slot_key="btn_ads_7" data-ad_slot_name="热门影片-头部-C1" data-ad_id="{$e.ad_id|default=''}" target="_blank">
        <div class="ad-image-box">
            <img class="zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
        </div>
    </a>
    {/foreach}
</div>
