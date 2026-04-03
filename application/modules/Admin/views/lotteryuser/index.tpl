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
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">活动</label>
                            <div class="layui-input-block">
                                <select name="where[lottery_id]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=$lotterys%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',defaultToolbar:[{title:'选显',layEvent:'selected_view',icon:'layui-icon-circle'},'filter','print','exports'],limit:20,limits:[10,20,30,40,50,60,70,80,90],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id',width:'6%',align:'center'}">id</th>
                            <th lay-data="{field:'lottery_name',width:'20%',align:'center'}">活动</th>
                            <th lay-data="{field:'aff',width:'14%',align:'center'}">aff</th>
                            <th lay-data="{field:'val',width:'20%',align:'center'}">抽奖次数</th>
                            <th lay-data="{field:'total',width:'20%',align:'center'}">总抽奖次数</th>
                            <th lay-data="{field:'created_at',width:'10%',align:'center'}">创建时间</th>
                            <th lay-data="{field:'updated_at',width:'10%',align:'center'}">更新时间</th>
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="addTimes" data-pk="id">添加次数</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}"
                           lay-event="del">
                            <i class="layui-icon layui-icon-delete"></i>删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="photolist">
    <div style="line-height: normal">
        <img style="display: inline-block;max-height: 152px;max-width: 107px;margin-bottom: 3px;" onclick="clickShowImage(this)"
             src="{{=d.img_url}}">
    </div>
</script>
<script type="text/html" class="data-dialog" id="addTimes">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">活动：</label>
            <div class="layui-input-inline">
                <select name="lottery_id" data-value="">
                    {%html_options options=$lotterys%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">用户aff：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="用户aff" name="aff" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">次数(负数是减)：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="次数(负数是减)" name="val" class="layui-input">
            </div>
        </div>
    </form>
</script>
<style>
    .size_tip {
        color: red;
        display: inline-block;
        height: 39px;
        line-height: 40px;
        font-size: 20px;
        font-weight: bold;
    }
</style>

{%include file="fooler.tpl"%}
<script>
    var fl = false
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'del':
                    layer.confirm('真的删除吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('del')%}", {"_pk": $(that).data('pk')})
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
                case 'edit':
                    lazy('#user-edit-dialog')
                        .data(data)
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                        });
                    break;
            }
        })

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {
                case 'add':
                    lazy('#user-edit-dialog')
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                        });
                    break;
                case 'delSelect':
                    var checkStatus = table.checkStatus(obj.config.id),
                        data = checkStatus.data,
                        pkValAry = [],
                        pkName = $(this).data('pk');
                    for (var i = 0; i < data.length; i++) {
                        if (typeof (data[i][pkName]) !== "undefined") {
                            pkValAry.push(data[i][pkName])
                        }
                    }
                    if (pkValAry.length === 0) {
                        return Util.msgErr('请先选择行');
                    }
                    layer.confirm('真的删除吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('delAll')%}", {"value": pkValAry.join(',')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    table.reload('test');
                                }
                            })
                    });
                    break;
                case 'addTimes':
                    lazy('#addTimes')
                        // .data(data)
                        .offset('auto')
                        .area(['637px','415px'])
                        .title('增加/减少次数')
                        .dialog(function (id, ele) {
                            let lottery_id = $('select[name=lottery_id]').val();
                            let aff = $('input[name=aff]').val();
                            let val = $('input[name=val]').val();
                            $.post("{%url('addLotteryTimes')%}", {'lottery_id':lottery_id,'aff':aff,'val':val})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                        layer.close(id);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                        layer.close(id);
                                    }
                                })
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                        });
                    break;
            }
        });

        // 监听单元格编辑
        table.on('edit(table-toolbar)', function (obj) {
            let data = {'_pk': obj.data['id']}
                data[obj.field] = obj.value;
            $.post("{%url('save')%}", data).then(function (json) {
                layer.msg(json.msg);
            });
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
                        table.reload('test')
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