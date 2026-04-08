<div id="list_videos_common_videos_list">
    <nav class="sorting-nav">
        <ul id="list_videos_common_videos_list_sort_list">
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
                                <div class="absolute-bottom-left" data-id="0"></div>
                                <div class="absolute-bottom-right">
                                    <span class="label">广告</span>
                                </div>
                                {else /}
                                <div class="absolute-bottom-left" data-id="{$e.data_id|default=''}">
                                    {if isset($e.uncensored) && $e.uncensored}
                                    <span class="uncensored">{$e.uncensored}</span>
                                    {/if}
                                    {if isset($e.chinese) && $e.chinese}
                                    <span class="chinese">{$e.chinese}</span>
                                    {/if}
                                </div>
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

            {include file="@components/component-pagination-91" /}
        </section>
    </div>
</div>
