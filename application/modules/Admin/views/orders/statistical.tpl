{%include file="header.tpl"%}
    <body>
<style>
    .order-span {
        color: orangered;
        font-weight: bold;
    }
</style>
<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>.layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">筛选</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">支付渠道</label>
                            <div class="layui-input-block">
                                <select name="where[channel]">
                                    <option value="">不限</option>
                                    {%html_options options=$payChannelAll%}
                                </select>
                            </div>
                        </div>


                        <div class="layui-inline" style="width: 300px;">
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="aff" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>


                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">不限</option>
                                    {%html_options options=OrdersModel::PAY_STAT%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">范围</label>
                            {%html_between name="created_at"%}
                        </div>

                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md3">
                    <div class="layui-card">
                        <div class="layui-card-header">数据统计</div>
                        <div class="layui-card-body">
                            <div class="layadmin-backlog">
                                <div carousel-item>
                                    <ul id="pannel_ul" class="layui-row layui-col-space10">
                                        <li class="layui-col-xs12">
                                            <a class="layadmin-backlog-body">
                                                <h3>&nbsp;</h3>
                                                <p><cite></cite></p>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md9">
                    <div class="layui-card">
                        <div class="layui-card-header">金额统计</div>
                        <div class="layui-card-body">
                            <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade"
                                 lay-filter="LAY-index-dataview">
                                <div carousel-item id="pay-view">
                                    <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-card">
                        <div class="layui-card-header">订单量</div>
                        <div class="layui-card-body">
                            <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade"
                                 lay-filter="LAY-index-dataview">
                                <div carousel-item id="data-view">
                                    <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="div-html-st">
    <li class="layui-col-xs12">
        <a class="layadmin-backlog-body">
            <h3>支付总额</h3>
            <p><cite>{{d.allAmount||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>iOS支付总额</h3>
            <p><cite>{{d.iosAmount||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>Android支付总额</h3>
            <p><cite>{{d.androidAmount||0}}</cite></p>
        </a>
    </li>
    <hr style="background-color: #CCCCCC">
    <li class="layui-col-xs12">
        <a class="layadmin-backlog-body">
            <h3>订单总数</h3>
            <p><cite>{{d.order||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>iOS总数</h3>
            <p><cite>{{d.ios||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>iOS支付数</h3>
            <p><cite>{{d.iosFail||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>Android总数</h3>
            <p><cite>{{d.android||0}}</cite></p>
        </a>
    </li>
    <li class="layui-col-xs6">
        <a class="layadmin-backlog-body">
            <h3>Android支付数</h3>
            <p><cite>{{d.androidFail||0}}</cite></p>
        </a>
    </li>

</script>

<script>

    layui.config({
        base: '{%$smarty.const.LAY_UI_STATIC%}/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'console', 'element', 'jquery', 'table', 'laytpl', 'laydate', 'form'], function (index, sys, ajax) {
        var $ = layui.jquery,
            laytpl = layui.laytpl,
            form = layui.form,
            laydate = layui.laydate,
            admin = layui.admin;

        function executeChart(id, legendData, category, seriesData, data) {
            if (data !== false) {
                laytpl($('#div-html-st').html()).render(data, function (html) {
                    $('#pannel_ul').html(html);
                });
            }

            var echartObject = [], echart_config = [
                    {
                        title: {
                            //text: '数据统计'
                        },
                        legend: {
                            data: legendData
                        },
                        tooltip: {trigger: "axis"},
                        xAxis: [{
                            type: "category",
                            data: category
                        }],
                        yAxis: [{type: "value"}],
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        series: seriesData
                    }
                ],
                chartContainer = $(id).children("div"),
                chartRender = function (index) {
                    echartObject[index] = echarts.init(chartContainer[index], layui.echartsTheme);
                    echartObject[index].setOption(echart_config[index]);
                    admin.resize(function () {
                        echartObject[index].resize()
                    })
                };
            chartRender(0);
        }

        sys.echarts(function (admin, carousel, echarts) {
            admin.req({url: "{%url('chartAJax')%}"}).then(function (json) {
                if (json.code == 0) {
                    let data = json.data;
                    executeChart('#data-view', data.legendData, data.category, data.seriesData, data.total);
                    executeChart('#pay-view', data.legendDataAmount, data.categoryAmount, data.seriesDataAmount, false);
                }
            });
        });

        $('.x-date').each(function (key, item) {
            laydate.render({elem: item});
        });

        /**
         * 绑定搜索事件
         */
        form.on('submit(search)', function (data) {
            var where = {}, ary = data.field, k;
            for (k in ary) {
                if (ary.hasOwnProperty(k) && ary[k].length > 0) {
                    if (k.substring(k.length - 4) === 'Time' && /^\d{4}-\d{2}-\d{2}$/.test(ary[k])) {
                        ary[k] += " 00:00:00";
                    }
                    where[k] = ary[k];
                } else {
                    where[k] = "__undefined__"
                }
            }
            admin.req({url: "{%url('chartAJax')%}", "data": where}).then(function (json) {
                if (json.code == 0) {
                    let data = json.data;
                    executeChart('#data-view', data.legendData, data.category, data.seriesData, data.total);
                    executeChart('#pay-view', data.legendDataAmount, data.categoryAmount, data.seriesDataAmount, false);
                }
            });
            return false;
        });

    });


</script>
{%include file="fooler.tpl"%}