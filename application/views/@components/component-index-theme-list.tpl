<!-- 影片主题 -->
<div class="col-lg-7 pb-3 pb-md-0">
    <div class="title-with-more">
        <div class="title-box">
            <h2 class="h3-md">
                影片主题
            </h2>
        </div>
        <div class="more">
            <a href="/theme">
                更多
                <svg aria-hidden="true" class="pl-1" height="20" width="20">
                    <use xlink:href="#icon-arrow-right">
                    </use>
                </svg>
            </a>
        </div>
    </div>
    <div class="row gutter-20">
        {foreach name="theme_list" item="e"}
        <div class="col-6">
            <div class="horizontal-img-box mb-3">
                <a href="{$e.url|default='#'}">
                    <div class="media">
                        <img class="rounded" src="__ROOT_PATH__/{$e.img|default=''}" width="50" alt="{$e.title|default=''}">
                        <div class="detail">
                            <h3 class="title">
                                {$e.title|default=''}
                            </h3>
                            <span>
                                {$e.count|default=''}
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        {/foreach}
    </div>
</div>
