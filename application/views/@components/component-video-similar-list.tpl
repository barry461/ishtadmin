<!-- 相似推荐列表 -->
<div class="slf-similar-list">
    <div class="list-content">
        <div class="recomment-content-wrap">
            <div class="recomment-list-title">人气推荐</div>
            <div class="slf-video-list-1">{include file="@components/component-video-card-list2" /}</div>
        </div>
    </div>
    <van-overlay :show="similarListLoading" class-name="loading-mask" z-index="9999999">
        <div class="loading-wrap">
            <van-loading color="#fff" type="spinner" size="50">加载中...</van-loading>
        </div>
    </van-overlay>
</div>
