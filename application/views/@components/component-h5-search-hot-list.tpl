<div class="search-hot-box">
    <h3>热门搜索</h3>
    <div class="search-hot-list">
        {foreach name="h5_hot_search_list" item="e"}
        <a href="{$e.url|default='#'}">
            {if $e.rank <= 3}
            <div><img src="/__base/images/h5/rank{$e.rank}@3x.png" alt="排名{$e.rank}"></div>
            {else /}
            <div>{$e.rank}</div>
            {/if}
            <p>{$e.title|default=''}</p>
            <img src="/__base/images/h5/icon-fire.png" class="icon-hot" alt="热门">
            <span>{$e.views|default=''}</span>
        </a>
        {/foreach}
    </div>
</div>
