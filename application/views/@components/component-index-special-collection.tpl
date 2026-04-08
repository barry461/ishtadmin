<!-- 专题合集 -->
<div class="col-lg-5 d-none d-lg-block">
    <div class="title-with-more">
        <div class="title-box">
            <h2 class="h3-md">
                专题合集
            </h2>
        </div>
    </div>
    <div class="row gutter-20">
        {foreach name="special_collection_list" item="e"}
        <div class="col-6">
            <a class="card {$e.bg_class|default='bg-pink'} text-light" href="{$e.url|default='#'}">
                <img class="overlay-image" src="__ROOT_PATH__/__base/images/card-overlay.png" alt="遮罩">
                <div class="card-body with-icon-title">
                    <div class="icon-title">
                        <svg aria-hidden="true" height="24" width="24">
                            <use xlink:href="#{$e.icon|default='icon-fire'}">
                            </use>
                        </svg>
                    </div>
                    <div>
                        <h3 class="mb-3">
                            {$e.title|default=''}
                        </h3>
                        <span class="text-white">
                            {$e.desc|default=''}
                        </span>
                    </div>
                </div>
            </a>
        </div>
        {/foreach}
    </div>
</div>
