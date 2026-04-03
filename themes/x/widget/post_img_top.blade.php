@php
    $article_top_ads = AdvertModel::getAdsByPosition(AdvertModel::POSITION_ARTICLE_TOP, true);
@endphp

@if (!empty($article_top_ads))
    <div style="margin-bottom: .5rem;width: 100%">
        <div class="horizontal-banner">
            @foreach ($article_top_ads as $k => $ad)

                <a data-type="ad_click" data-type-name="{{ $ad['title'] }}" data-id="{{ $ad['id'] ?? '' }}"
                   href="{{ replace_share($ad['link']) }}" target="_blank" @if(is_external_url($ad['link'])) rel="sponsored nofollow" @endif
                   data-track-impression="true"
                   data-slot-key="article_top_banner"
                   data-slot-name="文章顶部横幅"
                   data-ad-id="{{ $ad['id'] ?? '' }}"
                   data-ad-type="banner"
                   data-ad-idx="{{ $k }}"
                >
                    <img class="lazy" data-src="{{ $ad['img_url'] }}" src="{!! theme()->image(options('img_zwad')) !!}" 
                         id="article-top-banner-{{ $k }}" alt="{{ $ad['title'] }}" no-zoom
                    >
<?php /*
                    <script>
                        loadImage("{{ $ad['img_url'] }}", "article-top-banner-{{ $k }}");
                    </script>
 */?>
                </a>
            @endforeach
        </div>
    </div>
@endif
