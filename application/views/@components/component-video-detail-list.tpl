<!-- 猜你喜歡 + 右侧竖排视频列表 -->
            <section class="pb-3 pb-e-lg-40">
                <div class="title-with-more">
                    <div class="title-box">
            <h2 class="h3-md">猜你喜歡</h2>
                    </div>
                </div>
                <div class="row gutter-20" id="guess_u_like">
        {foreach name="video_detail_guess_u_like_list" item="e"}
        <div class="col-6 col-sm-4 col-xl-3 order-{$e.order}">
                        <div class="video-img-box mb-e-20">
                            <div class="img-box cover-md bind_video_img">
                    {if $e.type == 'ad'}
                    <a href="{$e.url|default='#'}" target="_blank" rel="nofollow">
                        <img class="zximg" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
                        <div class="absolute-bottom-left" data-id="0"></div>
                                    <div class="absolute-bottom-right">
                            <span class="label">广告</span>
                                    </div>
                                </a>
                    {else}
                    <a href="{$e.url|default='#'}" rel="follow">
                        <img class="zximg" z-image-loader-url="{$e.img|default=''}" alt="{$e.alt|default=''}" src="">
                        <div class="absolute-bottom-left" data-id="{$e.data_id|default=''}">
                            {notempty name="e.chinese"}
                            <span class="chinese">{$e.chinese}</span>
                            {/notempty}
                            {notempty name="e.uncensored"}
                            <span class="uncensored">{$e.uncensored}</span>
                            {/notempty}
                                    </div>
                                    <div class="absolute-bottom-right">
                            <span class="label">{$e.duration|default=''}</span>
                                    </div>
                                </a>
                    {/if}
                            </div>
                            <div class="detail">
                                <h6 class="title">
                        <a href="{$e.url|default='#'}">{$e.title|default=''}</a>
                                </h6>
                                <p class="sub-title">
                        {if $e.type == 'video'}
                                    <svg aria-hidden="true" class="mr-1" height="15" width="15">
                            <use xlink:href="#icon-eye"></use>
                                    </svg>
                        {$e.views|default='0'}
                                    <svg aria-hidden="true" class="ml-3 mr-1" height="13" width="13">
                            <use xlink:href="#icon-heart-inline"></use>
                                    </svg>
                        {$e.likes|default='0'}
                        {/if}
                                </p>
                            </div>
                        </div>
                    </div>
        {/foreach}
                </div>
            </section>
        </div>
{include file="@components/component-video-detail-right-sidebar" /}
