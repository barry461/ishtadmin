<div id="popup-window" class="hidden">
    <div class="van-overlay">
        {foreach name="popup_ad_list" item="e"}
        <div id="{$e.id|default='advertise-1'}" class="advertise-box hidden">
            <div class="adaptation popup-form">
                <div class="close-img on-popup-close">
                    <img class="close-image" src="__ROOT_PATH__/__base/images/av-5.png">
                </div>
                <div class="popup-img">
                    <a href="{$e.url|default='#'}" target="_blank">
                        <img class="zximg" x-image-loader-url="{$e.img|default=''}" src="">
                    </a>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</div>
