<?php
/**
 * @var PageNavigator $PageNavigator
 */
?>
@php
    $PageNavigator  = $PageNavigator ?? null;
    $currentPage  = $currentPage ?? null;
    $totalPage  = $totalPage ?? null;

@endphp
@if($PageNavigator && $totalPage && $currentPage)
<div class="page-nav">
    <div class="page-jump" >
        <form id="pageForm" method="get">
            <span class="page-info"> {{$currentPage}}/{{$totalPage}} </span>
            <input type="number" id="pageNum" min="1" max="{{$totalPage}}" data-total-page="{{$totalPage}}">
            <button id="submitBtn" data-href="{{$PageNavigator->baseUrl}}">跳转</button>
        </form>
    </div>
    {!! $PageNavigator->render() !!}
</div>
@endif