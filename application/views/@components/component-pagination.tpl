<!-- 分页组件 -->
<div id="van-pagination" class="xqbj-component-pagination-dynamic">
    <ul class="van-pagination__items">
        <li class="van-pagination__item van-pagination__item--prev pagination-disable">
            <a href="javascript:void(0);">上一页</a>
        </li>
       <!--
        <li class="van-pagination__item page-p">
            <span>第1/56789页</span>
        </li>
        -->
         <li class="van-pagination__item van-pagination__item--page current-pre" id="current-pre">
            <a href="?page=1" class="item text-nowrap-ellipsis" >1</a>
        </li>
        <li class="van-pagination__item van-pagination__item--page current van-pagination__item--active " id="current">
            <a href="?page=2" class="item text-nowrap-ellipsis" >2</a>
         </li>
        <li class="van-pagination__item van-pagination__item--page current-next" id="current-next">
            <a href="?page=3" class="item text-nowrap-ellipsis" >3</a>
         </li>
        <li class="van-pagination__item van-pagination__item--page current-more" id="current-more">
          <a href="?page=4" class="item text-nowrap-ellipsis">...</a>
        </li>
         <li class="van-pagination__item van-pagination__item--page total" id="total">
            <a href="?page=99" class="item text-nowrap-ellipsis" >99</a>
         </li>
        <li class="van-pagination__item to-page">
            <input type="number" class="to-input to-input-key go-page" value="2">
        </li>
        <li class="van-pagination__item to-btn-box to-page">
            <div id="onTopage" class="to-btn">跳转</div>
        </li>
        <li class="van-pagination__item van-pagination__item--next">
            <a href="javascript:void(0);">下一页</a>
        </li>
    </ul>
</div>
