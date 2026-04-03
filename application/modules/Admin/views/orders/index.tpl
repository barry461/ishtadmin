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

<style>
    .layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header liu-title">订单列表管理
                    <span style="color: orangered" class="layui-inline" id="total-id">
                        <p style="font-size: medium"></p>
                    </span>
                </div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">邀请码</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff_code]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">uuid</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[uuid]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">产品ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[product_id]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <!-- <div class="layui-inline">
                             <label class="layui-form-label">三方订号</label>
                             <div class="layui-input-block">
                                 <input type="text" name="where[app_order]" placeholder="请输入" autocomplete="off"
                                        class="layui-input">
                             </div>
                         </div>-->
                        <div class="layui-inline">
                            <label class="layui-form-label">单号</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[order_id]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">订单金额</label>
                            <div class="layui-input-block">
                                {%html_between name="amount" bgText="请输入，分"%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">支付时间</label>
                            <div class="layui-input-block">
                                {%html_between name="updated_at" value=date("Y-m-d",TIMESTAMP)%}
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">设备</label>
                            <div class="layui-input-block">
                                <select name="where[oauth_type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=OrdersModel::OAUTH_DEVICE%}
                                </select>
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
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=OrdersModel::PAY_STAT%}
                                </select>
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
                            <!-- <th lay-data="{type:'checkbox'}"></th>-->
                            <th lay-data="{field:'id',width: 80}">id</th>
                            <th lay-data="{field:'product_id',width: 80}">产品id</th>
                            <th lay-data="{field:'uuid'}">用户uuid</th>
                            <th lay-data="{field:'descp'}">订单信息</th>
                            <th lay-data="{field:'order_id'}">订单号</th>
                            <th lay-data="{field:'app_order'}">回单号</th>
                            <th lay-data="{field:'channel',width: 80}">支付渠道</th>
                            <th lay-data="{field:'oauth_type',width: 80}">设备</th>
                            <th lay-data="{field:'amount_yuan',width: 90}">订单金额</th>
                            <th lay-data="{field:'pay_amount_yuan',width: 90}">实付金额</th>
                            <th lay-data="{field:'status','type':'enum',width: 100,'value':{%json_str(OrdersModel::PAY_STAT)%}}">
                                状态
                            </th>
                            <th lay-data="{field:'updated_at',templet:'#time-attr'}">时间</th>
                            <!--<th lay-data="{field:'build_id'}">代理</th>-->
                            <!--<th lay-data="{field:'pay_type_sdk'}">类型</th>-->
                            <th lay-data="{fixed: 'right',width: 140 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户uuid标识：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.uuid }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">product_id：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.product_id }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">唯一订单号：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.order_id }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">第三方订单号：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.app_order }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">我方对订单简单说明：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.descp }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">订单类型 同产品类型：</label>
                <div class="layui-input-inline">
                    <input disabled name="order_type" value="{{=d.order_type }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">订单金额，单位分：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.amount_yuan }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">实付金额：</label>
                <div class="layui-input-inline">
                    <input disabled placeholder="实付金额" value="{{=d.pay_amount_yuan }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">支付方式：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.payway }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">订单状态:</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{=d.status||0}}">
                        {%html_options options=OrdersModel::PAY_STAT%}
                    </select>
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">支付接口返回的状态说明：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.msg }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">订单支付渠道：</label>
                <div class="layui-input-inline">
                    <select data-value="{{=d.channel}}">
                        <option value="">全部</option>
                        {%html_options options=$payChannelAll%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">渠道标识：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.build_id }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">创建时间：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.created_at }}" class="layui-input">

                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">更新时间：</label>
                <div class="layui-input-inline">
                    <input disabled value="{{=d.updated_at }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">online线上充值/agent代理充值：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="online线上充值/agent代理充值" name="pay_type"
                           value="{{=d.pay_type }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">上传成功截图：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="上传成功截图" name="desc_img"
                           value="{{=d.desc_img }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">赠送钻石：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="赠送钻石" name="gift_diamond"
                           value="{{=d.gift_diamond }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">商品快照：</label>
                <div class="layui-input-inline">

                    <input placeholder="商品快照" name="goods_info"
                           value="{{=d.goods_info }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>
<script type="text/html" id="search-total">
    <p style="font-size: medium">
        找到：{{=d.count}}，条数据, 成功支付条数:{{=d.payedCount}}, 成功率:{{=d.payedRate}}%, 订单金额: {{=d.orderTotal}}, 成功支付金额:
        {{=d.payedTotal}}
    </p>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {

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