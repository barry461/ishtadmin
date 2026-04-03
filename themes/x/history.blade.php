@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@section('lists')
<style>
  .brick a {
    display: inline-block;
    line-height: 12.6; 
}
</style>
<div class="container">
    <div class="row">
        <div id="archives" style="padding-bottom: 1rem;">
            <h1 id="archives-title">往期内容</h1>
            @if(count($taglist) > 0)
                <div id="archives-tags">
                    <div>
                        <h2 style="display: inline-block">标签云</h2>
                        <a href="{{ url('tag.list') }}" style="font-size: 20px">更多&gt;&gt;</a>
                    </div>
                    @foreach($taglist as $tag)
                        <a class="itags" href="{{ $tag->url() }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
            <div id="archives-content">
                <br>
                @foreach($lists as $date => $group)
                    <div class="archive-title" id="archives-{{ $date }}">
                        <div class="archives" data-date="{{ $date }}">
                            @php $first = $group->first(); @endphp
                            <h2>{{ $first && $first->created ? date('Y 年 m 月', strtotime($first->created)) : '' }}</h2>
                            @foreach($group as $item)
                                <div class="brick">
                                    <a href="{{ $item->url() }}">
                                        <span class="time">{{ $item->created ? date('m-d', strtotime($item->created)) : '' }}</span> {{ $item->title }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @include('widget.page')
            </div>
            @include("widget.guide")
            @include("widget.ads")
        </div>
    </div>
</div>
@endsection