{{-- 
 * @var tools\LibCollection $nav_items
 * @var ContentsModel[] $pages
 * @var MetasModel[] $metas
 * @var Mirages $mirages
 * @var ContentsModel[] $lists
 * @var \website\DefaultView $this
 * @var $hideRssBarItem
 * @var $hideNightShiftBarItem
 * @var $toolbarItemsOutput
--}}

@extends('layouts.app')
@section("seo-head")
{!! $header ?? '' !!}
@endsection
@section('lists')

    <div class="container">
        <div class="row">
            <div id="archives" >
                <h1 id="archives-title">所有标签</h1>
                    <div id="archives-tags">
                        <h2>所有标签</h2>
                        @foreach($lists as $tag)
                            <a class="itags" href="{{ $tag->url() }}">{{ $tag['name'] }}</a>
                        @endforeach
                    </div>
                </div>
                      
                @include('widget.page')
                @include('widget.guide')
                @include('widget.ads')
            </div>
        </div>  
    </div>
@endsection