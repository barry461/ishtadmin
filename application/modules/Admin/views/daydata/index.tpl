{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>
<script>
    table_load = function (data) {
        $('#total-id>p').html(data.desc);
    }
</script>
<style>.layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理
                    <span style="color: orangered" class="layui-inline" id="total-id">
                        <p style="font-size: medium;font-weight: bold!important;"></p>
                    </span>
                </div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">统计时间</label>
                            <div class="layui-input-block">
                                {%html_between name="date"%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar',done:table_load}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width:'9%',align:'center'}">id</th>
                            <th lay-data="{field:'date',width:'9%',align:'center'}">日期</th>
                            <th lay-data="{field:'reg_total',width:'9%',align:'center'}">新增</th>
                            <th lay-data="{field:'active_total',width:'9%',align:'center'}">活跃</th>
                            <th lay-data="{field:'active_ios',width:'9%',align:'center'}">苹果活跃</th>
                            <th lay-data="{field:'active_android',width:'9%',align:'center'}">安卓活跃</th>
                            <th lay-data="{field:'active_web',width:'9%',align:'center'}">PWA活跃</th>
                            <th lay-data="{field:'visit_website',width:'9%',align:'center'}">官网访问数</th>
                            <th lay-data="{field:'down_and',width:'9%',align:'center'}">安卓下载数</th>
                            <th lay-data="{field:'down_ios',width:'9%',align:'center'}">苹果下载数</th>
                            <th lay-data="{field:'down_web',width:'9%',align:'center'}">PWA下载数</th>
                            <th lay-data="{field:'down_window',width:'10%',align:'center'}">window下载数</th>
                            <th lay-data="{field:'down_macos',width:'9%',align:'center'}">macOS下载数</th>
                            <th lay-data="{field:'down_total',width:'9%',align:'center'}">总点击数</th>
                            <th lay-data="{field:'down_rate',width:'9%',align:'center'}">官网点击率%</th>
                            <th lay-data="{field:'pay_total',width:'9%',align:'center'}">总充值</th>
                            <th lay-data="{field:'vip_total',width:'9%',align:'center'}">vip充值</th>
                            <th lay-data="{field:'pay_num',width:'9%',align:'center'}">成功订单数</th>
                            <th lay-data="{field:'coins_total',width:'9%',align:'center'}">金币充值</th>
                            <th lay-data="{field:'reg_pay_total',width:'9%',align:'center'}">新增充值</th>
                            <th lay-data="{field:'reg_pay_scale',width:'9%',align:'center'}">新增充值占比%</th>
                            <th lay-data="{field:'pay_success_scale',width:'9%',align:'center'}">支付成功率%</th>
                            <th lay-data="{field:'coins_consume_total',width:'9%',align:'center'}">金币消耗额</th>
                            <th lay-data="{field:'coins_consume_num',width:'9%',align:'center'}">金币消耗数</th>
                            <th lay-data="{field:'xx',width: '15%',templet:'#a1'}">网页主线(成功/失败/成功率)</th>
                            <th lay-data="{field:'xxx',width: '15%',templet:'#a2'}">网页备用(成功/失败/成功率)</th>
                            <th lay-data="{field:'created_at',width:'9%',align:'center'}">操作时间</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="a1">
    {{d.main_line_suc}} &nbsp;|&nbsp;
    {{d.main_line_fail}}  |
    {{d.main_line_rate}}%
</script>
<script type="text/html" id="a2">
    {{d.bk_line_suc}} &nbsp;|&nbsp;
    {{d.bk_line_fail}}  |
    {{d.bk_line_rate}}%
</script>

{%include file="fooler.tpl"%}
<script src="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui-xtree.js"></script>
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {
        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {

            }
        })

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {

            }
        });

        function dialogCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post("{%url('save')%}", from.serializeArray())
                .then(function (json) {
                    layer.close(id);
                    if (json.code) {
                        return Util.msgErr(json.msg);
                    }
                    if (typeof (obj) == "undefined") {
                        //添加
                        Util.msgOk(json.msg);
                        table.reload('test');
                    } else {
                        //修改
                        obj.update(json.data);
                        let index = $(obj.tr).data('index')
                        table.cache['test'][index] = json.data;
                        Util.msgOk(json.msg);
                        table.reload('test');
                    }
                })
        }

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
            table.reload('test', {
                where: where,
                page: {curr: 1}
            });
            return false;
        });

        //渲染日期
        $('.x-date-time').each(function (key, item) {
            layDate.render({elem: item, 'type': 'datetime'});
        });
        $('.x-date').each(function (key, item) {
            layDate.render({elem: item});
        });

        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>