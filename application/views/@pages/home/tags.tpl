{layout name="@layout/default-layout" /}
<div class="tags-detail-site" data-type-id="{$type_id}">
<section class="content-header">
    <div class="container">
        {include file="@components/component-ad-list-c1" /}
        <div class="title-with-avatar center">
            <div class="title-box">
                <h1 class="h3-md mb-1">
                    {$tag_name}
                    相关的AV在线观看
                </h1>
                <span class="inactive-color fs-2 mb-0">
                    {$tag_count}
                    部影片
                </span>
            </div>
        </div>
        <!-- 标签页添加简介 start -->
        <div class="tab_desc">
            <h2 class="h3-md">
                标签简介
            </h2>
            {$tag_desc}
        </div>
        <!-- 标签页添加简介 end -->
    </div>
</section>
{include file="@components/component-video-tags-list" /}
<div class="container ad-e2-bottom">
    {include file="@components/component-ad-list-E2" /}
</div>
</div>
