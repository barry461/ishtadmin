{layout name="@layout/default-layout" /}
<div class="actress-detail-site">
<section class="content-header">
    <div class="container">
        {include file="@components/component-ad-list-c1" /}
        {include file="@components/component-actress-card" /}
    </div>
</section>
{include file="@components/component-video-list-actress-detail" /}
<!-- 分页 start -->
<div class="container">
    <nav class="pagination-container" role="navigation">
        {include file="@components/component-pagination-91" /}
    </nav>
</div>
<!-- 分页 end -->
</div>
