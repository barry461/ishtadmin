<div class="post-content" style="margin: 1rem 0">
    @foreach ($appList as $k1 => $listval)
        <div class="ads-title">{{ $k1 }}</div>
        
        <div class="article-bottom-apps">
            @foreach ($listval as $k2 => $item)
                <a class="btn-app" href="{{ replace_share($item->url) }}" target="_blank" @if(is_external_url($item->link)) rel="sponsored nofollow" @endif>
                    <img class="lazy" src="{{ $item->thumb }}"
                         data-src="{{ $item->thumb }}" 
                         alt="添加到主屏幕 - 快速打开{{ register('site.app_name') }}" 
                         id="article-bottom-img-app-{{ $k1 }}-{{ $k2 }}">
{{--                    <script>--}}
{{--                        loadImage("{{ $item->thumb }}", "article-bottom-img-app-{{ $k1 }}-{{ $k2 }}");--}}
{{--                    </script>--}}
                    <span>{{ $item->name }}</span>
                </a>
            @endforeach
            
        </div>
    @endforeach
</div>