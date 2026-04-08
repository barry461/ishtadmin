<!-- E2广告 -->
<div class="slf-ad-list-type-a">
    {foreach name="ad_list_e2" item="e"}
    <a href="{$e.url|default='#'}" rel="nofollow" class="ad-item tjtagmanager" data-event="ad_click" data-ad_type="banner" data-ad_position="content_banner" data-template_id="E2" target="_blank">
        <div class="ad-image-box">
            <img class="zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
        </div>
    </a>
    {/foreach}
</div>
