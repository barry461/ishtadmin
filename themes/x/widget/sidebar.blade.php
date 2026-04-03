<div class="h5_header">
    <div class="menu">导航</div>
    <a href="/" class="logo">
        <img src="{!! theme()->image(options('logo_url')) !!}" alt="{{ register('site.app_name') }}" />
    </a>
    <div class="search">
        {!! theme()->importImg('/usr/themes/Mirages/images/search_btn.png', '搜索') !!}
    </div>
</div>
<nav id="site-navigation" class="sidebar no-user-select" role="navigation">
    <div id="nav">
        <div class="author navbar-header">
            <a href="/">
                <img src="{!! theme()->image(options('logo_url')) !!}" alt="{{ register('site.app_name') }}" width="100" height="100"/>
            </a>
        </div>
        <div class="search-box navbar-header">
            <form class="form" id="search-form" action="/search"  role="search">
                <input id="search" type="text" name="s" required placeholder="{{_mt('搜索...')}}" class="search search-form-input">
                <button id="search_btn" type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <ul id="menu-menu-1" class="menu navbar-nav">
            <li class="menu-item"><a href="{{theme_options()->rootUrl()}}">{{_mt('首页')}}</a></li>
            <li>
                <a class="slide-toggle" style="display: none">{{_mt('分类')}}</a>
                <div class="category-list">
                    <ul class="list">
                        @foreach ($metas as $item)
                            <li class="category-level-0 category-parent"><a href="<?= $item->url() ?>" class=""><?= $item->title ?></a></li>
                        @endforeach
                    </ul>
                </div>
            </li>

            @foreach ($pages as $item)
                @if($item->fieldValue('segment_line_start'))
                    <ol style="height: 1px;width: 100%;margin: 0 auto;border-bottom: 1px #555 solid;"></ol>
                @endif
                <li class="menu-item">
                    <a class="{{$item->isPage() ? 'current' : ''}}" href="{{$item->url()}}"
                       title="{{$item['title']}}">{{$item['title']}}</a>
                </li>
                @if($item->fieldValue('segment_line_end'))
                    <ol style="height: 1px;width: 100%;margin: 0 auto;border-bottom: 1px #555 solid;"></ol>
                @endif
            @endforeach
        </ul>
    </div>
    @if (!($hideRssBarItem && $hideNightShiftBarItem && empty($toolbarItemsOutput)))
        <div id="nav-toolbar">
            <div class="side-toolbar">
                <ul class="side-toolbar-list">
                    @unless($hideRssBarItem)
                        <li>
                            <a id="side-toolbar-rss" href="{{ url('feed') }}" title="{{ _mt('RSS') }}">
                                <i class="fa fa-feed"></i>
                            </a>
                        </li>
                    @endunless

                    {{-- 直接输出 HTML --}}
                    {!! $toolbarItemsOutput !!}
                </ul>
            </div>
        </div>
    @endif
</nav>
