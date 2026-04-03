{%include file="header.tpl"%}
<body>

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
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">支付时间</label>
                            <div class="layui-input-block">
                                {%html_between name="updated_at" value=date("Y-m-d",TIMESTAMP)%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">产品类型</label>
                            <div class="layui-input-block">
                                <select name="where[order_type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=ProductModel::GOODS_TYPES%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">支付渠道</label>
                            <div class="layui-input-block">
                                <select name="where[channel]">
                                    <option value="">全部</option>
                                    {%html_options options=$payChannelAll%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                        <div class="layui-inline" id="stats-pos">

                        </div> 
                    </div>
                </div>

                <div class="layui-card-body">
                    <table id="demo" lay-filter="table-toolbar"></table>
                    <script type="text/html" id="toolbar">
                    </script>
                     <script type="text/html" id="stats-templet">
                        订单金额/实际支付:{{d.total}}/{{d.pay_total}}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {
        let tableIns = table.render({
            elem: '#demo',
            id:'test',
            "url": '{%url('listAjax')%}',
            "done":function (res) {
                $('#stats-pos').html(laytpl($('#stats-templet').html()).render(res.extend))
                return res;
            }
            ,page: true
            ,'toolbar':"#toolbar",
            'cols': [
               [
                   {type: 'checkbox'},
                   {field: 'channel', title: '支付渠道'},
                   {field: 'num', 'title': '交易笔数'},
                   {field: 'amount', 'title': '订单金额'},
                   {field: 'pay_amount', 'title': '实际金额'},
               ]
            ]
        })

        let verify = {}

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'edit':
                    lazy('#user-edit-dialog')
                        .data(data)
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                        });
                    break;
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
                        table.reload('test')
                    } else {
                        //修改
                        obj.update(json.data);
                        let index = $(obj.tr).data('index')
                        table.cache['test'][index] = json.data;
                        Util.msgOk(json.msg);
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
        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>