{%include file="header.tpl"%}


<style>
    .layui-form.user-edit-dialog .layui-input-block {
        margin-right: 30px
    }
</style>
<div class="layui-card layadmin-header">
    <div class="layui-breadcrumb" lay-filter="breadcrumb">
        <a lay-href="">主页</a>
        <a><cite>组件</cite></a>
        <a><cite>数据表格</cite></a>
        <a><cite>开启头部工具栏</cite></a>
    </div>
</div>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url("listAjax")%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type: 'checkbox'}">ID</th>
                            <th lay-data="{field:'id'}">ID</th>
                            <th lay-data="{field:'name'}">名字</th>
                            <th lay-data="{field:'icon'}">图标</th>
                            <th lay-data="{field:'controller'}">控制器</th>
                            <th lay-data="{field:'action'}">方法</th>
                            <th lay-data="{field:'sort'}">排序</th>
                            <th lay-data="{fixed: 'right', align:'center', toolbar: '#operate-toolbar'}">操作</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">
                                删除选择
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit" lay-form="user-edit-dialog">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}" lay-event="del">
                            <i class="layui-icon layui-icon-delete"></i>删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" id="user-edit-dialog" data-h="680" data-w="800" layer-dialog="确认,取消">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form" action="" lay-filter="form-save">
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">菜单名称：</label>
                <div class="layui-input-inline">
                    <input placeholder="菜单名称" name="name" value="{{=d.name }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">图标：</label>
                <div class="layui-input-inline">
                    <input placeholder="菜单名称" name="icon" value="{{=d.icon }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">控制器：</label>
                <div class="layui-input-inline">
                    <input placeholder="控制器" name="controller" value="{{=d.controller }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">方法：</label>
                <div class="layui-input-inline">
                    <input placeholder="方法" name="action" value="{{=d.action }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">args：</label>
                <div class="layui-input-inline">
                    <input placeholder="方法" name="args" value="{{=d.args }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <label class="layui-form-label">上级分类：</label>
            <div class="layui-input-block">
                <select name="p_id" data-value="{{=d.p_id}}">
                    {%foreach PermissionModel::getTreeArrayData(0) as $key=>$item%}
                        <option value="{%$key%}">{%$item%}</option>
                    {%/foreach%}
                </select>
            </div>
        </div>
        <div class="layui-form-item" style="width: 600px;">
            <div class="layui-inline">
                <label class="layui-form-label">排序：</label>
                <div class="layui-input-inline">
                    <input type="number" placeholder="排序值，数值越大越靠前" name="sort" value="{{=d.sort || 0}}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>


{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'element', 'laydate', 'form', 'layedit'], function (table, laytpl, form, lazy) {


        function dialogCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post('{%url('save')%}', from.serializeArray())
                .then(function (json) {
                    layer.close(id);
                    if (json.code) {
                        return Util.msgErr(json.msg);
                    }
                    if (typeof (obj) == "undefined") {
                        Util.msgOk(json.msg);
                        table.reload('test')
                    } else {
                        Util.msgOk(json.msg, function () {
                            obj.update(json.data);
                        });
                    }
                })
        }


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
                        .width(1200)
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
                case 'add':
                    lazy('#user-edit-dialog')
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
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
            }
        });
    })
</script>