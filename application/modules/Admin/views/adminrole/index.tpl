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
                            <th lay-data="{field:'role_id', width:80, sort: true}">ID</th>
                            <th lay-data="{field:'role_name', width:200}">名字</th>
                            <th lay-data="{field:'role_action_ids'}">权限</th>
                            <th lay-data="{fixed: 'right', align:'center', toolbar: '#operate-toolbar'}">操作</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"
                           lay-form="user-edit-dialog">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.role_id}}" lay-event="del">
                            <i class="layui-icon layui-icon-delete"></i>删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" id="user-edit-dialog" data-h="780" data-w="900" layer-dialog="确认,取消"
        data-option="{title:false,closeBtn:false,shadeClose:true,anim:3,offset:'rt',full:false}">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form" action="" lay-filter="form-save">

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">角色名称：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="角色名称" name="role_name"
                           value="{{=d.role_name }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">角色规则：</label>
            <div class="layui-input-block">
                <div id="adminrole_div" class="xtree_contianer" style="margin-bottom: 30px"></div>
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.role_id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>
<script src="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui-xtree.js"></script>
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'element', 'laydate', 'form', 'layedit'], function (table, laytpl, form, lazy) {
        var menudata = [];
        $.ajax("{%url('adminMenu/treeList')%}", {"dataType": "json", 'async': false})
            .then(function (json) {
                return json.data;
            })
            .then(function (data) {
                menudata = data;
            })


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
                        obj.update(json.data);
                        let index = $(obj.tr).data('index')
                        table.cache['test'][index] = json.data;
                        Util.msgOk(json.msg);
                    }
                })
        }

        function getTreeMenuData(ruleArray) {
            let treeJson = [];
            for (let i = 0, item, data; i < menudata.length; i++) {
                item = menudata[i]
                data = {
                    "title": item.name,
                    "value": item.id,
                    "inputname": 'rule[]',
                    "checked": in_array(item.id, ruleArray),
                    "data": [],
                };
                if (typeof (item.children) !== "undefined") {
                    for (let j = 0, _item; j < item.children.length; j++) {
                        _item = item.children[j];
                        data.data.push({
                            "title": _item.name,
                            "value": _item.id,
                            "inputname": 'rule[]',
                            "checked": in_array(_item.id, ruleArray),
                            "data": [],
                        })
                    }
                }
                treeJson.push(data);
            }
            return treeJson;
        }

        table.on('tool(table-toolbar)', function (obj) { //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data, //获得当前行数据
                layEvent = obj.event,//获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                tr = obj.tr, //获得当前行 tr 的 DOM 对象（如果有的话）
                that = this;
            switch (layEvent) {
                case 'edit':
                    lazy('#user-edit-dialog')
                        .data(data)
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj);
                        })
                        .laytpl(function () {
                            new layuiXtree({elem: 'adminrole_div', form: form, data: getTreeMenuData(data.rule.split(','))});
                            xx.renderSelect(data, $, form);
                        });
                    break;
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
            }
        });

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) { //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var layEvent = obj.event;//获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            switch (layEvent) {
                case 'add':
                    lazy('#user-edit-dialog')
                        .data({})
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            new layuiXtree({elem: 'adminrole_div', form: form, data: getTreeMenuData([])});
                            xx.renderSelect({}, $, form);
                        });
                    break;
            }
        });
    });
</script>
{%include file="fooler.tpl"%}