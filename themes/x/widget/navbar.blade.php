<!-- Fixed navbar -->
<nav id="navbar" class="navbar navbar-expand-md navbar-color fixed-top no-user-select">
    <div class="container-fluid">
        @php
            $logo = options('logo_url');
        @endphp
        <a class="navbar-brand {{ theme()->vClass(['text-brand'=>str_contains_list($logo  , '://')])  }}"
           data-type="navigation" data-type-name="Logo"
           href="{!! theme_options()->rootUrl() !!}"><img src="{{ $logo }}" alt="{{ register('site.app_name') }}" height="40">
        </a>
           
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <style>.blog-description {padding-bottom: .5rem;}</style>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                @if(theme_options()->navbarLogoUrl)
                    <li class="nav-item"><a class="nav-link" href="{{theme_options()->rootUrl()}}">{{$logo}}</a>
                    </li>
                @endif
                @foreach ($pc_navs as $item)
                    <li class="nav-item category-level-0 category-parent">
                        <a 
                        @if($item['type'] != 'page')
                        href="{{ url('category', ['slug' => $item['slug']]) }}" 
                        @else
                            href="{{ url('slug', ['slug' => $item['slug']]) }}"
                        @endif
                           class="nav-link {{ v_clz(['current'=>is_url('page', $item['slug'])]) }}"
                           data-type="navigation" data-type-name="{{ $item['title'] }}"
                           >{{ $item['title'] }}</a>
                    </li>
                @endforeach
                @switch(count($dropdown_navs))
                   @case(1)
                        @php
                            $item = $dropdown_navs->get(0);
                        @endphp
                        @if ($item)
                            <li class="nav-item">
                                <a class="{{ v_clz(['nav-link'=>true, 'current'=>is_url('page', $item['slug'])]) }}"
                                data-type="navigation" data-type-name="{{ $item['title'] }}"
                                href="{{ $item->url() }}" title="{{ $item['title'] }}">{{ $item['title'] }}</a>
                            </li>
                        @endif
                        @break

                    @default
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="more-menu-dropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多</a>
                            <ul class="dropdown-menu" aria-labelledby="more-menu-dropdown">
                                @foreach ($dropdown_navs as $item)
                                    <li class="dropdown-item">
                                        <a href="{{ $item->url() }}" target="_blank" title="{{ $item['title'] }}"
                                           data-type="navigation" data-type-name="{{ $item['title'] }}"
                                        >
                                            {{ $item['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                @endswitch

            </ul>

            <ul class="navbar-nav side-toolbar-list">
                <li class="navbar-search-container">
                    <button id="navbar-search" type="button" class="search-form-input" title="{{ _mt('搜索...') }}">
                        <i class="fa fa-search"></i>
                    </button>
                    <form class="search-form" action="/search" role="search">
                        <input type="text" name="s" required placeholder="{{ _mt('搜索...') }}" class="search">
                    </form>
                </li>

                {!! $toolbarItemsOutput !!}
            </ul>
        </div>
    </div>
</nav>
