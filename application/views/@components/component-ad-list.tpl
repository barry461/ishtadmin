<!-- 广告组件 -->
<div class="slf-ad-list-type-a">
    <a
        href="#"
        v-for="ad in 4"
        :key="`ad_list_type_a_${ad}`"
        class="ad-item tjtagmanager"
        data-event="ad_click"
        data-ad_type="banner"
        data-ad_position="content_banner"
        data-template_id="模版"
        target="_blank"
    >
        <div class="ad-image-box">
            <img
                x-image-loader-url="https://pic.tnirgpy.cn/hc237/uploads/default/other/2026-01-09/ae4616b25f0cdd252a8befee02049eee.gif"
            />
        </div>
    </a>
</div>
