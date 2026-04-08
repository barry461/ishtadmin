<!-- 详情页视频播放器 + 暂停播放广告D1 -->
<section class="pb-3 pb-e-lg-30 adaptation">
    <div tabindex="0" class="video-player-wrapper" data-hls-url="{:isset($data['mv_url']) && $data['mv_url'] ? $data['mv_url'] : 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8'}">
        <video playsinline data-poster="__IMAGE_DOMAIN__/upload/cloud/20240114/2024011409295120744.jpg" poster="__IMAGE_DOMAIN__/upload/cloud/20240114/2024011409295120744.jpg"></video>
    </div>
    {foreach name="video_detail_player_d1_list" item="e"}
    <div id="playerad{$e.index}" class="pause-controls">
        <div class="mask">
            <div class="ad">
                <a href="{$e.url|default='#'}" rel="nofollow" class="click_btn tjtagmanager" target="blank" data-bid="{$e.bid|default=''}" data-aid="1" data-event="ad_click" data-page_key="btn_ads_9" data-page_name="详情页-暂停播放-D1" data-ad_slot_key="btn_ads_9" data-ad_slot_name="详情页-暂停播放-D1" data-ad_id="{$e.ad_id|default=''}" data-creative_id="" data-ad_type="btn_ads_9" data-seen="true">
                    <img class="zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
                </a>
            </div>
            <div class="continue">
                <img class="zximg" src="__ROOT_PATH__/__base/images/p-aa.png" class="continueplayer">
            </div>
        </div>
    </div>
    {/foreach}
</section>
