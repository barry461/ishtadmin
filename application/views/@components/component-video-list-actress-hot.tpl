<div id="list_models_models_list">
    <nav class="sorting-nav">
        <ul id="list_models_models_list_sort_list">
            <li{eq name="sort" value="name"} class="active"{/eq}>
                <a href="/actress/name/1">
                    名称顺序
                </a>
            </li>
            <li{eq name="sort" value="hot"} class="active"{/eq}>
                <a href="/actress/hot/1">
                    热度优先
                </a>
            </li>
            <li{eq name="sort" value="latest"} class="active"{/eq}>
                <a href="/actress/latest/1">
                    今日更新
                </a>
            </li>
            <li{eq name="sort" value="count"} class="active"{/eq}>
                <a href="/actress/count/1">
                    最多影片
                </a>
            </li>
        </ul>
    </nav>
    <div class="container">
        <section class="pb-3 pb-e-lg-40">
            <div class="row gutter-20">
                {foreach name="actress_list" item="e"}
                <div class="col-6 col-sm-4 col-lg-3">
                    <div class="horizontal-img-box ml-3 mb-3">
                        <a href="{$e.url|default='#'}">
                            <div class="media">
                                {if $e.is_text_avatar}
                                <div class="text-avatar text-avatar-md d-none d-md-flex">
                                    {$e.avatar_char|default=''}
                                </div>
                                {else /}
                                <div class="rounded-circle d-md-none">
                                    <img class="rounded-circle d-md-none" x-image-loader-url="{$e.img|default=''}" width="32" height="32" alt="{$e.name|default=''}">
                                </div>
                                <div class="rounded-circle d-none d-md-block">
                                    <img class="rounded-circle d-none d-md-block" x-image-loader-url="{$e.img|default=''}" width="80" height="80" alt="{$e.name|default=''}">
                                </div>
                                {/if}
                                <div class="detail">
                                    <h3 class="title">
                                        {$e.name|default=''}
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
        </section>
    </div>
</div>
