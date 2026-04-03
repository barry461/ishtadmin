<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理后台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/style/admin.css" media="all">
</head>
<body>

<div class="layui-row layui-col-space5">
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="layui-card-header">数据统计</div>
            <div class="layui-card-body">

                <div class="layadmin-backlog">
                    <div carousel-item>
                        <ul id="pannel_ul" class="layui-row layui-col-space5">
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>总留存(1天/3天/7天)</h3>
                                    <p><cite>{%$data.keep_1day%}/{%$data.keep_3day%}/{%$data.keep_7day%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>渠道留存(1天/3天/7天)</h3>
                                    <p><cite>{%$data.ckeep_1day%}/{%$data.ckeep_3day%}/{%$data.ckeep_7day%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日留存</h3>
                                    <p><cite>{%$data.session%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日注册</h3>
                                    <p><cite>{%$data.todayReg%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日活跃</h3>
                                    <p><cite>{%$data.huoyue%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日邀请注册</h3>
                                    <p><cite>{%$data.channel_reg%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日vip充值</h3>
                                    <p><cite>{%$data.pay_vip%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日金币充值</h3>
                                    <p><cite>{%$data.pay_coin%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日总充值</h3>
                                    <p><cite>{%$data.pay_total%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>今日充值成功率</h3>
                                    <p><cite>{%$data.pay_percent%}%</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>落地页访问(总数/渠道)</h3>
                                    <p><cite>{%$data.self_reg%}/{%$data.share_reg%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>邀请码填写(self/渠道)</h3>
                                    <p><cite>{%$data.invited_self%}/{%$data.invited_channel%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>新用户(订单/邀请)</h3>
                                    <p><cite>{%$data.newer_order%}/{%$data.newer_invited%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>下载量(安卓)</h3>
                                    <p><cite>{%$data.down_and%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>下载量(PWA)</h3>
                                    <p><cite>{%$data.down_web%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>下载量(IOS)</h3>
                                    <p><cite>{%$data.down_ios%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>下载量(window)</h3>
                                    <p><cite>{%$data.down_window%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>下载量(macOS)</h3>
                                    <p><cite>{%$data.down_macos%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>官网访问量/总下载量</h3>
                                    <p><cite>{%$data.self_reg%}/{%$data.down_total%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>官网点击率</h3>
                                    <p><cite>{%$data.down_rate%}%</cite></p>
                                </a>
                            </li>

                            <!-- <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>{%$data.mDomain%}点击(成功/失败)</h3>
                                    <p><cite>{%$data.mSucc%}/{%$data.mError%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>{%$data.mDomain%}成功率</h3>
                                    <p><cite>{%$data.mRate%}%</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>{%$data.bDomain%}点击(成功/失败)</h3>
                                    <p><cite>{%$data.bSucc%}/{%$data.bError%}</cite></p>
                                </a>
                            </li>
                            <li class="layui-col-xs2">
                                <a class="layadmin-backlog-body">
                                    <h3>{%$data.bDomain%}成功率</h3>
                                    <p><cite>{%$data.bRate%}%</cite></p>
                                </a>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-col-md12">
        <div class="layui-card-header">
            数据概览
        </div>

        <div style="width: 100%; height:450px;background:#ffffff;padding: 30px" id="LAY-index-dataview">
            <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
        </div>
    </div
</div>


<script src="{%$smarty.const.LAY_UI_STATIC%}/layuiadmin/layui/layui.js"></script>
<script type="text/javascript" src="/static/backend/echarts.min.js" charset="utf-8"></script>
<script type="text/javascript" src="/static/backend/jquery.min.js" charset="utf-8"></script>
<script>
    function executeChart(id , legendData, category, seriesData, title) {
        let echart_config = {
            title: {
                text: title,
                color: '##FF5722',
                fontStyle: "italic"
            },
            legend: {
                data:  legendData
            },
            tooltip: {trigger: "axis"},
            xAxis: [{
                type: "category",
                data: category
            }],
            yAxis: [{type: "value"}],
            toolbox: {
                feature: {
                    saveAsImage: {
                        name: "KS-DATA-V"
                    }
                }
            },
            series:  seriesData
        };
        var chart = echarts.init(document.getElementById(id));
        chart.setOption(echart_config);
    }

    function getChartAjax() {
        $.ajax({
            url: "{%url('chartAJax2')%}",
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.code) {
                    let data = res.data;
                    executeChart( 'LAY-index-dataview' ,data.legendData, data.category, data.seriesData, data.title);
                }
            }
        });
    }

    $(function () {
        getChartAjax();
    });


</script>


</body>
</html>