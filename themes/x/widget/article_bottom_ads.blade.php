@php
use AdvertModel as Advert;

/**  文章底部广告 */
$btn_bottom_ads = Advert::getAdsByPosition(Advert::POSITION_ARTICLE_BOTTOM_BTN, true);
$article_bottom_ads = Advert::getAdsByPosition(Advert::POSITION_ARTICLE_BOTTOM, true);
$text_bottom_ads = Advert::getAdsByPosition(Advert::POSITION_ARTICLE_BOTTOM_TEXT, true);

// var_dump($btn_bottom_ads, $article_bottom_ads, $text_bottom_ads);die();
// echo json_encode($text_bottom_ads, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
// die();

$adsByCategory = AdsCategoryModel::getAdsByCate($btn_bottom_ads);

@endphp

@if($article_bottom_ads)
    <div class="horizontal-banner">
        @foreach($article_bottom_ads as $key => $ad)

        <a data-type="ad_click" data-type-name="{{ $ad->title }}" data-id="{{ $ad->id ?? '' }}"
           href="{{ replace_share($ad->link)}}" target='_blank' @if(is_external_url($ad->link)) rel="sponsored nofollow" @endif
           data-track-impression="true"
           data-slot-key="article_bottom_banner"
           data-slot-name="文章底部横幅"
           data-ad-id="{{ $ad->id ?? '' }}"
           data-ad-type="banner"
           data-ad-idx="{{ $key }}"
        >
            <img class="lazy" src="{!! theme()->image(options('img_zwad')) !!}" data-src="{{$ad->img_url}}" alt="{{$ad->title}}" id="article-bottom-ads-{{$key}}">
{{--            <script type="text/javascript">--}}
{{--                loadImage("{{$ad->img_url}}", "article-bottom-ads-{{$key}}");--}}
{{--            </script>--}}
        </a>
        @endforeach
    </div>
@endif

@if(count($adsByCategory)>0)
 @foreach($adsByCategory as $key => $ads)
     @if(count($ads['ads'])>0)
    <div class="post-content" style="margin: 1rem 0">
        <div class="ads-title">{{ $ads['name'] }}</div>
        <div class="article-bottom-apps">
        @if(count($ads['ads'])>0)
        @foreach($ads['ads'] as $k => $ad)
                <a class="btn-app" data-type="ad_click" data-type-name="{{ $ad->title }}" data-id="{{ $ad->id ?? '' }}"
                   href="{{ replace_share($ad->link)}}" target="_blank" @if(is_external_url($ad->link)) rel="sponsored nofollow" @endif
                   data-track-impression="true"
                   data-slot-key="article_bottom_app"
                   data-slot-name="文章底部APP"
                   data-ad-id="{{ $ad->id ?? '' }}"
                   data-ad-type="app"
                   data-ad-idx="{{ $key }}-{{ $k }}"
                >
                    @if(!empty($ad->img_url))
                        <img src="{!! theme()->image(options('img_zwimg')) !!}" class="lazy" data-src="{{$ad['img_url']}}" alt="{{ $ad['title'] ?? '应用下载' }}" id="article-bottom-img-app-{{$k}}-{{$key}}">
                        <script>//loadImage("{{$ad->img_url}}", "article-bottom-img-app-{{$k}}-{{$key}}"); </script>
                    @else
                        <img src="{!! theme()->image(options('img_zwimg')) !!}" alt="应用下载">
                    @endif
                    <span>{{$ad->title}}</span>
                </a>
        @endforeach
        @endif
        </div>
    </div>
    @endif
 @endforeach
@endif

@if($text_bottom_ads)
    <div class="post-content" style="margin: 1rem 0">
        <div class="ads-title">{{ $ads->name }}</div>
        <div class="txt-apps">
        @foreach ($text_bottom_ads as $k => $ad)
                <a class="btn-app" data-type="ad_click" data-type-name="{{ $ad->title }}" data-id="{{ $ad->id ?? '' }}"
                   href="{{ replace_share($ad->link) }}" target="_blank" @if(is_external_url($ad->link)) rel="sponsored nofollow" @endif
                   data-track-impression="true"
                   data-slot-key="article_bottom_text"
                   data-slot-name="文章底部文字"
                   data-ad-id="{{ $ad->id ?? '' }}"
                   data-ad-type="text"
                   data-ad-idx="{{ $k }}"
                >
                    <span>{{ $ad->title }}</span>
                </a>
        @endforeach
        </div>
    </div>
    <div class="line"></div>
@endif
