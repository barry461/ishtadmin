<!-- 精品推荐D5 -->
<div class="fine-wrap">
    <div class="title">精品推荐</div>
    <div class="list">
        {foreach name="video_detail_recommend_d5_list" item="e"}
        <a href="{$e.url|default='#'}"{if isset($e.rel) && $e.rel === ''}{else} rel="nofollow"{/if} class="fine click_btn tjtagmanager" target="_blank" data-bid="{$e.bid|default=''}" data-aid="{$e.aid|default='1'}" data-event="ad_click" data-page_key="btn_ads_13" data-page_name="详情页-精品推荐-D5" data-ad_slot_key="btn_ads_13" data-ad_slot_name="详情页-精品推荐-D5" data-ad_id="{$e.ad_id|default=''}" data-creative_id="" data-ad_type="btn_ads_13" data-seen="true">
            <img class="img zximg" z-image-loader-url="{$e.img|default=''}" alt="{$e.name|default=''}" src="">
            <span class="name">{$e.name|default=''}</span>
            <img class="icon" src="__ROOT_PATH__/__base/images/icon.png" alt="{$e.name|default=''}">
        </a>
        {/foreach}
    </div>
</div>
