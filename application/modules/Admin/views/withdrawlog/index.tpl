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
                <div class="layui-card-header liu-title">提现列表管理
                    <span style="color: orangered" class="layui-inline" id="total-id">
                        <p style="font-size: medium"></p>
                    </span>
                </div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">uuid</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[uuid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现订单号</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[cash_id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现回单号</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[third_id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现账号</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[account]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现姓名</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options options=WithdrawLogModel::STATUS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现方式</label>
                            <div class="layui-input-block">
                                <select name="where[type]">
                                    <option value="">全部</option>
                                    {%html_options options=WithdrawLogModel::TYPE%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现类型</label>
                            <div class="layui-input-block">
                                <select name="where[withdraw_from]">
                                    <option value="">全部</option>
                                    {%html_options options=WithdrawLogModel::WITHDRAW_FROM%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现时间</label>
                            <div class="layui-input-block">
                                {%html_between name="updated_at"%}
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
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:90, id:'test',toolbar:'#toolbar',done:table_load}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id',width: 80}">id</th>
                            <th lay-data="{field:'cash_id',templet:'#att1' }">提现订单号</th>
                            <th lay-data="{field:'account',templet:'#att2' }">提现账号</th>
                            <th lay-data="{field:'withdraw_from_str',width: 80}">来源</th>
                            <th lay-data="{field:'amount',width: 140,templet:'#att4' }">金额</th>
                            <th lay-data="{field:'status_str',width: 90}">提现状态</th>
                            <th lay-data="{field:'descp'}">状态说明</th>
                            <th lay-data="{field:'ip',templet:'#att3'}">属性</th>
                            <th lay-data="{fixed: 'right',width: 120 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="att1">
                        下单号：{{d.cash_id}}<br>
                        回单号：{{d.third_id}}
                    </script>
                    <script type="text/html" id="att2">
                        aff：{{d.aff}}<br>
                        uuid：{{d.uuid}}<br>
                        昵称：{{d.nickname_str}}
                        <hr>
                        收款账号/USDT地址：{{d.account}}<br>
                        收款姓名/USDT协议：{{d.name}}<br>
                        收款方式：{{d.type_str}}
                    </script>
                    <script type="text/html" id="att3">
                        创：{{d.created_at}}<br>
                        改：{{d.updated_at}}
                        <hr>
                        ip：{{d.ip}}<br>
                        地址：{{d.local}}
                    </script>
                    <script type="text/html" id="att4">
                        提现金额：{{d.amount}} <br>
                        实扣金币：{{d.coins}}
                    </script>


                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a href="javascript:void(0);" lay-event="incomeList" data-aff="{{=d.aff}}">收益详情</a> |
                            <a data-aff="{{=d.aff}}" lay-event="black">拉黑</a>  <br>
                            <a data-pk="{{=d.id}}" lay-event="viewer">查看</a> |
                            <a style="color: red" data-pk="{{=d.id}}" lay-event="withdraw">标记成功</a> <br>
                            <a data-pk="{{=d.id}}" lay-event="passWithdraw">通过</a> |
                            <a data-pk="{{=d.id}}" lay-event="noWithdraw">拒绝</a>
                            {%*                            <a data-pk="{{=d.id}}" lay-event="withdrawFreeze">冻结</a> |*%}
                            {%*                            <a data-pk="{{=d.id}}" lay-event="withdrawUnfreeze">解冻</a>*%}
                        </div>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" class="data-dialog" id="w_black">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">拉黑理由：</label>
                <div class="layui-input-inline">
                    <select name="reason" data-value="">
                        {%html_options options=WithdrawBlackModel::REASON_LIST%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">备注:</label>
            <div class="layui-input-block">
                <textarea rows="10" name="remark" class="layui-textarea"></textarea>
            </div>
        </div>
        <input type="hidden" name="aff" value="{{=d.aff}}">
    </form>
</script>



{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {


        let verify = {};

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'withdraw':
                    layer.confirm('确定要标记成功吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('withdraw')%}", {"id": $(that).data('pk')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                            })
                    });
                    break;
                case 'passWithdraw':
                    layer.confirm('确定要提现吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('pass_withdraw')%}", {"id": $(that).data('pk')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                            })
                    });
                    break;
                case 'noWithdraw':
                    _uuid = $(that).data('uuid');
                    top.layer.prompt({
                        formType: 2,
                        value: ' ',
                        title: '请输入拒绝的理由',
                        area: ['300px', '150px'] //自定义文本域宽高
                    }, function (value, index, elem) {
                        top.layer.close(index);
                        $.post("{%url('noWithdraw')%}", {"id": $(that).data('pk'), "reply": value})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                                table.reload('test');
                            })
                    });
                    break;
                case 'viewer':
                    $.post("{%url('viewer')%}", {"id": $(that).data('pk')})
                        .then(function (json) {
                            layer.open({
                                title: '查看'
                                , content: json.msg
                            });
                        })
                    break;
                case 'black':
                        lazy('#w_black')
                            .offset('auto')
                            .data(data)
                            .title('拉黑')
                            .area(['500px', '410px'])
                            .dialog(function (id, ele) {
                                let aff = $('input[name=aff]').val()
                                let reason = $('select[name=reason]').val()
                                let remark = $('textarea[name=remark]').val()
                                $.post("{%url('black')%}", {"aff": aff,"reason": reason,"remark": remark})
                                    .then(function (json) {
                                        layer.close(id);
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            // location.reload();
                                        }
                                    })
                            })
                            .laytpl(function () {
                                xx.renderSelect(data, $, form);
                            });
                    break;
                case 'incomeList':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('moneyincomelog/index')%}?aff='+data['aff'])
                        .area([`${ddd.clientWidth - 200}px` , `${ddd.clientHeight}px`])
                        .title(`收益详情`)
                        .start(function () {

                        })
                    break;
                case 'withdrawUnfreeze':
                    layer.confirm('确定要解冻吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('withdrawHandle')%}", {"id": $(that).data('pk'), "type": 2})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                                table.reload('test');
                            })
                    });
                    break;
                case 'withdrawFreeze':
                    _uuid = $(that).data('uuid');
                    top.layer.prompt({
                        formType: 2,
                        value: ' ',
                        title: '请输入冻结的理由',
                        area: ['300px', '150px'] //自定义文本域宽高
                    }, function (value, index, elem) {
                        top.layer.close(index);
                        $.post("{%url('withdrawHandle')%}", {"id": $(that).data('pk'), "reply": value, "type": 1})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                                table.reload('test');
                            })
                    });
                    break;
            }
        })

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