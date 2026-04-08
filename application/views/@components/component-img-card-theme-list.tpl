<div id="list_categories_categories_list">
    <nav class="sorting-nav">
        <ul id="list_categories_categories_list_sort_list">
            <li class="active">
                <a href="/cn/theme/sort">
                    预设排序
                </a>
            </li>
            <li>
                <a href="/cn/theme/check_num">
                    热度优先
                </a>
            </li>
            <li>
                <a href="/cn/theme/count">
                    最多影片
                </a>
            </li>
        </ul>
    </nav>
    <div class="container container-small">
        <section class="pb-3 pb-e-lg-40">
            <div class="row gutter-20">
                {foreach name="theme_list" item="theme"}
                <div class="col-6 col-sm-4 col-lg-3">
                    <div class="video-img-box mb-e-20">
                        <div class="img-box">
                            <a href="{$theme.url|default='#'}">
                                <div class="overlay">
                                </div>
                                <img src="__ROOT_PATH__/{$theme.img|default=''}" alt="{$theme.title|default=''}">
                                <div class="absolute-center">
                                    <h3>
                                        {$theme.title|default=''}
                                    </h3>
                                    <span class="label">
                                        {$theme.count|default=''}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
        </section>
    </div>
</div>
