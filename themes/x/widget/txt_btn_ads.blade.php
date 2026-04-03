@php
use AdvertModel as Advert;

$article_bottom_ads = Advert::getAdsByPosition(Advert::POSITION_ARTICLE_BOTTOM, true);
$text_bottom_ads = Advert::getAdsByPosition(Advert::POSITION_ARTICLE_BOTTOM_TEXT, true);
@endphp
@if($article_bottom_ads)
    <div class="horizontal-banner">
        @foreach($article_bottom_ads as $key => $ad)
        <a href="{{ replace_share($ad->link)}}" target='_blank' @if(is_external_url($ad->link)) rel="sponsored nofollow" @endif>
            <img class="lazy" src="{!! theme()->image(options('img_zwad')) !!}" data-src="{{$ad->img_url}}" alt="{{$ad->title}}" id="article-bottom-ads-{{$key}}">
        </a>
        @endforeach
    </div>
@endif
<div class="post-content" style="margin: 1rem 0"></div>
<div class="post-content" style="margin: 1rem 0">
     @if($text_bottom_ads)
    <div class="ads-title">必备精品 </div>
   
    <div class="txt-apps">
        @foreach ($text_bottom_ads as $ad)
        <a class="btn-app" href="{{ replace_share($ad->link)}}" target="_blank" @if(is_external_url($ad->link)) rel="sponsored nofollow" @endif>
            <span>{{$ad->title}}</span>
        </a>
        @endforeach
    </div>
    @endif
</div>