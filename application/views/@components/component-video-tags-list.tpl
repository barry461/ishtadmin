<div id="list_videos_common_videos_list">
    <nav class="sorting-nav theme-detail-sort-nav">
        <ul id="list_videos_common_videos_list_sort_list" class="sorting-nav-list">
            <li{eq name="sort" value="hot"} class="active"{/eq}>
                <a href="/tags/{$type_id}/hot/1">
                    近期最佳
                </a>
            </li>
            <li{eq name="sort" value="latest"} class="active"{/eq}>
                <a href="/tags/{$type_id}/latest/1">
                    今日更新
                </a>
            </li>
            <li{eq name="sort" value="watch"} class="active"{/eq}>
                <a href="/tags/{$type_id}/watch/1">
                    最多观看
                </a>
            </li>
            <li{eq name="sort" value="favorite"} class="active"{/eq}>
                <a href="/tags/{$type_id}/favorite/1">
                    最高收藏
                </a>
            </li>
        </ul>
    </nav>
    <div class="container">
        <section class="pb-3 pb-e-lg-40">
            <div class="row gutter-20">
                {foreach name="video_list" item="e"}
                <div class="col-6 col-sm-4 col-lg-3">
                    <div class="video-img-box mb-e-20">
                        <div class="img-box cover-md bind_video_img">
                            <a href="{$e.url|default='#'}"{if $e.type == 'ad'} target="_blank" rel="nofollow"{/if}>
                                <img class="zximg" x-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}">
                                {if $e.type == 'ad'}
                                <div class="absolute-bottom-right">
                                    <span class="label">广告</span>
                                </div>
                                {else /}
                                {if (isset($e.chinese) && $e.chinese) || (isset($e.uncensored) && $e.uncensored)}
                                <div class="absolute-bottom-left">
                                    {if isset($e.uncensored) && $e.uncensored}
                                    <span class="uncensored">{$e.uncensored}</span>
                                    {/if}
                                    {if isset($e.chinese) && $e.chinese}
                                    <span class="chinese">{$e.chinese}</span>
                                    {/if}
                                </div>
                                {/if}
                                <div class="absolute-bottom-right">
                                    <span class="label">{$e.duration|default=''}</span>
                                </div>
                                {/if}
                            </a>
                        </div>
                        <div class="detail">
                            <h3 class="title">
                                <a href="{$e.url|default='#'}"{if $e.type == 'ad'} target="_blank"{/if}>
                                    {$e.title|default=''}
                                </a>
                            </h3>
                            {if $e.type != 'ad' && isset($e.views)}
                            <p class="sub-title">
                                <svg aria-hidden="true" class="mr-1" height="15" width="15">
                                    <use xlink:href="#icon-eye"></use>
                                </svg>
                                {$e.views}
                                <svg aria-hidden="true" class="ml-3 mr-1" height="13" width="13">
                                    <use xlink:href="#icon-heart-inline"></use>
                                </svg>
                                {$e.likes|default=''}
                            </p>
                            {/if}
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
        </section>
    </div>
<!-- 分页 start -->
<div class="container">
    <nav class="pagination-container" role="navigation">
        {include file="@components/component-pagination-91" /}
    </nav>
</div>
<!-- 分页 end -->
</div>
