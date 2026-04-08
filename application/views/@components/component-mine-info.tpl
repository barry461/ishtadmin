<section
    class="content-header"
    style="background: #10121d; background-image: linear-gradient(to bottom, #090812, #111520); padding-top: 0"
>
    <div class="container">
        <div class="profile-info title-with-avatar">
            <img class="avatar mr-3" src="__ROOT_PATH__/__base/images/avatar.svg" width="100" />
            <div class="title-box">
                <h3>elsemk</h3>
                <div class="inactive-color">加入于：2周前</div>
            </div>
        </div>
        <nav class="profile-nav">
            <ul>
                <li class="{:in_array(strtolower(request()->action()), ['mine', 'index']) ? 'active' : ''}">
                    <a href="/user/">
                        <svg aria-hidden="true" class="ml-2 ml-sm-0 mr-2" height="18" width="16">
                            <use xlink:href="#icon-heart-inline"></use>
                        </svg>
                        <span>影片收藏</span>
                    </a>
                </li>
                <li class="{:strtolower(request()->action()) == 'watchlater' ? 'active' : ''}">
                    <a href="/user/watchLater/">
                        <svg aria-hidden="true" class="ml-2 ml-sm-0 mr-2" height="18" width="16">
                            <use xlink:href="#icon-bookmark-inline"></use>
                        </svg>
                        <span>稍后观看</span>
                    </a>
                </li>
                <li class="{:strtolower(request()->action()) == 'actorlike' ? 'active' : ''}">
                    <a href="/user/actorLike/">
                        <svg aria-hidden="true" class="ml-2 ml-sm-0 mr-2" height="18" width="16">
                            <use xlink:href="#icon-heart-inline"></use>
                        </svg>
                        <span>订阅女优</span>
                        <span class="count" id="actorLikeCount">3</span>
                    </a>
                </li>
            </ul>
            <div style="position: relative">
                <a class="right bind_set_up" href="#" data-toggle="dropdown">
                    <svg aria-hidden="true" class="mr-2" height="18" width="16">
                        <use xlink:href="#icon-settings"></use>
                    </svg>
                    <span>设置</span>
                </a>
                <div
                    class="dropdown-menu dropdown-menu-right bind_show_up"
                    style="position: absolute; top: 110%; left: 0"
                >
                    <a class="dropdown-item bind_my_change_personal" href="javascript:;">个人资料</a>
                    <a class="dropdown-item bind_my_change_pass" href="javascript:;">更改密码</a>
                </div>
            </div>
        </nav>
    </div>
</section>
