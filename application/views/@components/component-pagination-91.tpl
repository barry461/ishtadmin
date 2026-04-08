<!-- 分页组件：有 $pagination 时动态渲染，否则静态占位-->
{if condition="isset($pagination) && !empty($pagination)"}
<ul class="pagination">
    {volist name="pagination.pages" id="p"}
    <li class="page-item">
        {if condition='$p.active'}
        <span class="page-link active disabled">{$p.label}</span>
        {else /}
        <a class="page-link" href="{$p.url}">{$p.label}</a>
        {/if}
    </li>
    {/volist}
</ul>
{else /}
<ul class="pagination">
    <li class="page-item">
        <span class="page-link active disabled">01</span>
    </li>
    <li class="page-item">
        <a class="page-link" href="?page=2">02</a>
    </li>
    <li class="page-item">
        <a class="page-link" href="?page=3">03</a>
    </li>
    <li class="page-item">
        <a class="page-link" href="?page=99">最后»</a>
    </li>
</ul>
{/if}
