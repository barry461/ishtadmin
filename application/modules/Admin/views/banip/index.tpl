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
    }

    .layui-form-select .layui-select-title input {
        width: 168px;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                </div>

                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'ip',align:'center'}">IP</th>
                            <th lay-data="{fixed: 'right',width: 300 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add" data-pk="ip">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="ip">删除所选</button>
                            <button class="layui-btn layui-btn-sm" lay-event="sysWeb" data-pk="ip">同步WEB</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-ip="{{=d.ip}}" lay-event="del"><i
                                    class="layui-icon layui-icon-delete"></i>删除</a>
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
            <label class="layui-form-label">IP</label>
            <div class="layui-input-block">
                <input placeholder="IP" name="ip" value="{{d.ip || '' }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
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
                            $.post("{%url('delAll')%}", {"ips": $(that).data('ip')})
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
                }
            })

        function batSelectId(obj, that) {
            let checkStatus = table.checkStatus(obj.config.id),
                data = checkStatus.data,
                pkValAry = [],
                pkName = $(that).data('pk');
            for (let i = 0; i < data.length; i++) {
                if (typeof (data[i][pkName]) !== "undefined") {
                    pkValAry.push(data[i][pkName])
                }
            }
            return pkValAry
        }

        function dialogCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post("{%url('save')%}", from.serializeArray())
                .then(function (json) {
                    layer.close(id);
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg);
                        table.reload('test');
                    }
                })
        }

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            let layEvent = obj.event,
                data = table.checkStatus(obj.config.id).data,
                pkValAry = [];
            pkValAry = batSelectId(obj, this);
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
                case 'sysWeb':
                    layer.confirm('真的要同步吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('sysWeb')%}", {})
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
                case 'delSelect':
                    pkValAry = batSelectId(obj, this);
                    if (pkValAry.length === 0) {
                        return Util.msgErr('请先选择行');
                    }
                    layer.confirm('真的删除吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('delAll')%}", {"ips": pkValAry.join(',')})
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
            }
        });

        form.verify(verify);
    })
</script>