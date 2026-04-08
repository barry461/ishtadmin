{layout name="@layout/default-layout" /}
<div class="search-result-site" data-keywords="{$keywords}">
<section class="content-header" style="padding-top: 0;">
                <div class="container">
                    <div class="title-with-avatar center">
                                                <div class="title-box">
                            <h2 class="h3-md mb-1">1</h2>
                            <span class="inactive-color fs-2 mb-0">396 部影片</span>
                        </div>
                    </div>
                </div>
            </section>
<div id="list_videos_common_videos_list">
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
</div>
