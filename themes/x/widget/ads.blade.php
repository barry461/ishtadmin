@php
    $adverts = AdvertModel::getAdsByPosition(AdvertModel::POSITION_HORIZONTAL_ADS);
    $footer_ads = AdvertModel::getAdsByPosition(AdvertModel::POSITION_WEBSITE_BOTTOM);
    if($footer_ads){
        $footer_ads = $footer_ads->toarray();
    }else{
        $footer_ads = [];
    }
   
    
@endphp
<div class="horizontal-banner">
    @foreach($adverts as $k=>$item)
        <a href="{{$item['link']}}" target='_blank'  @if(is_external_url($item['link'])) rel="sponsored nofollow" @endif>
            <img class="lazy" src="{!! theme()->image(options('img_zwad')) !!}" data-src="{{$item['img_url']}}" alt="{{$item['title']}}">
        </a>
    @endforeach
</div>
@if(count($footer_ads)>0)
<style>

    .footer-banner {
      display: none;
      width: 100vw;
      height: 12.5vw;
      position: relative;
      flex-shrink: 0;
      z-index: 111;
    }

    .footer-banner.hidden {
      display: none !important;
    }

    .footer-banner-inner {
      position: fixed;
      height: 12.5vw;
      width: 100vw;
      bottom: 0;
      left: 0;
    }

    .footer-banner-link {
      display: block;
      width: 100%;
      height: 100%;
      position: relative;
    }

    .footer-banner-img {
      display: block;
      width: 100%;
      height: auto;
    }

    .footer-banner-close {
      position: absolute;
      top: 0;
      right: 0;
      background-color: #000;
      padding: 4px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .footer-banner-icon {
      width: 4.4vw;
      height: 4.4vw;
      color: #f5f5f5;
    }

    @media (max-width:768px) {
      .footer-banner {
        display: block;
      }
    }
  </style>

<div class="footer-banner">
    <div class="footer-banner-inner">
      <a href="{{$footer_ads[0]['link']}}" class="footer-banner-link" rel="sponsored nofollow">
        <img class="footer-banner-img lazy"
          src="{!! theme()->image('banner.png') !!}" data-src="{{url_image($footer_ads[0]['img_url'])}}"
          alt="添加到主屏幕 - 快速打开{{ register('site.app_name') }}">
        <div class="footer-banner-close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="footer-banner-icon">
            <path
              d="M18.3 5.71a.996.996 0 0 0-1.41 0L12 10.59 7.11 5.7A.996.996 0 1 0 5.7 7.11L10.59 12 5.7 16.89a.996.996 0 1 0 1.41 1.41L12 13.41l4.89 4.89a.996.996 0 1 0 1.41-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"
              fill="currentColor" />
          </svg>
        </div>
      </a>
    </div>
  </div>
<script>
    const closeIcon = document.querySelector('.footer-banner-close')
    if (closeIcon) {
      closeIcon.addEventListener('click', function (event) {
        const bannerEl = document.querySelector('.footer-banner')
        bannerEl.classList.add('hidden')
        event.stopPropagation()
        event.preventDefault()
      })
    }
  </script>
  @endif