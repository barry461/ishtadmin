@php
    $btn_top_ads = AdvertModel::getAdsByPosition(AdvertModel::POSITION_ARTICLE_TOP_BTN, true);
    $text_top_ads = AdvertModel::getAdsByPosition(AdvertModel::POSITION_ARTICLE_TOP_TEXT, true);
@endphp

@if ($btn_top_ads)
    <div class="article-bottom-apps">
        @foreach ($btn_top_ads as $k => $ad)
            <a class="btn-app" data-type="ad_click" data-type-name="{{ $ad['title'] }}" data-id="{{ $ad['id'] ?? '' }}"
               href="{{ replace_share($ad['link']) }}" target="_blank" rel="sponsored nofollow"
               data-track-impression="true"
               data-slot-key="article_top_btn"
               data-slot-name="文章顶部按钮"
               data-ad-id="{{ $ad['id'] ?? '' }}"
               data-ad-type="button"
               data-ad-idx="{{ $k }}"
            >
                @if (!empty($ad['img_url']))
                    <img class="lazy" src="{!! theme()->image(options('img_zwimg')) !!}" data-src="{{ $ad['img_url'] }}" alt="{{ $ad['title'] ?? '应用下载' }}" id="article-top-app-{{ $k }}" no-zoom>
{{--                    <script>--}}
{{--                        loadImage("{{ $ad['img_url'] }}", "article-top-app-{{ $k }}");--}}
{{--                    </script>--}}
                @else
                    <img src="{!! theme()->image(options('img_zwimg')) !!}" alt="应用下载">
                @endif
                <span>{{ $ad['title'] }}</span>
            </a>
        @endforeach
    </div>
    <div class="line" style="margin:0 1rem 1rem 0"></div>
@endif

@if ($text_top_ads)
    <div class="txt-apps">
        @foreach ($text_top_ads as $k => $ad)
            <a class="btn-app" data-type="ad_click" data-type-name="{{ $ad['title'] }}" data-id="{{ $ad['id'] ?? '' }}"
               href="{{ replace_share($ad['link']) }}" target="_blank" rel="sponsored nofollow"
               data-track-impression="true"
               data-slot-key="article_top_text"
               data-slot-name="文章顶部文字"
               data-ad-id="{{ $ad['id'] ?? '' }}"
               data-ad-type="text"
               data-ad-idx="{{ $k }}"
            >
                <span>{{ $ad['title'] }}</span>
            </a>
        @endforeach
    </div>
@endif
