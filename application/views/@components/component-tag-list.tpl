<!-- 标签组件 -->
<div class="ajw-tag-box">
    <div class="ajw-tags-list">
        <a class="tag-item" href="/home/tagDetail/标签名称111"
         v-for="(ele, index) in 20" :key="index"
        >
            标签{{index}}
        </a>
    </div>
    <div class="ajw-tag-all">
        <a href="/home/tagPage">显示所有标签</a>
    </div>
</div>
