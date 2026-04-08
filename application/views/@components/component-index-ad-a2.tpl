<!-- 首页A2广告 -->
<div class="adaptation ad-wrap">
    {foreach name="ad_list_a2" item="e"}
    <a href="{$e.url|default='#'}" rel="nofollow" class="img click_btn" target="_blank">
        <img class="zximg" src="{$e.img|default=''}" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
    </a>
    {/foreach}
</div>
