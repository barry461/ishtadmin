<!-- 精品推荐D5 -->
<section class="adaptation">
    <div class="fine-wrap">
        <div class="title">精品推荐</div>
        <div class="list">
            {foreach name="article_fine_recommend_list" item="e"}
            <a href="{$e.url|default='#'}"{if isset($e.rel) && $e.rel === ''}{else} rel="nofollow"{/if} class="fine tjtagmanager" data-event="ad_click" data-ad_type="banner" data-ad_position="content_fine_recommend" data-template_id="D5" data-bid="{$e.bid|default=''}" data-aid="{$e.aid|default='1'}" data-ad_id="{$e.ad_id|default=''}" target="_blank">
                <img class="img zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.name|default=''}">
                <span class="name">{$e.name|default=''}</span>
                <img class="icon" src="__ROOT_PATH__/__base/images/icon.png" alt="{$e.name|default=''}">
            </a>
            {/foreach}
        </div>
    </div>
</section>
