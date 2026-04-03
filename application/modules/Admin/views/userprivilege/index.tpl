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
                                <input type="text" name="where[aff]" placeholder="请输入" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">产品</label>
                            <div class="layui-input-block">
                                <select name="where[product_id]">
                                    <option value="">全部</option>
                                    <option value="0">自定义</option>
                                    {%html_options options=$productArr%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">资源类型</label>
                            <div class="layui-input-block">
                                <select name="where[resource_type]">
                                    <option value="">全部</option>
                                    {%html_options options=ProductPrivilegeModel::RESOURCE_TYPE%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'aff'}">用户aff</th>
                            <th lay-data="{field:'product_id'}">产品ID</th>
                            <th lay-data="{field:'resource_type_str'}">资源类型</th>
                            <th lay-data="{field:'privilege_type_str'}">权限类型</th>
                            <th lay-data="{field:'value'}">数值</th>
                            <th lay-data="{field:'status_str'}">状态</th>
                            <th lay-data="{field:'expired_time'}">权限到期时间</th>
                            <th lay-data="{field:'created_at'}">创建时间</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
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
<script type="text/html" class="data-dialog" id="user-add-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">aff</label>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户aff" name="aff" value="{{=d.aff }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">会员到期</label>
                <div class="layui-input-inline">
                    <input placeholder="会员到期时间" name="expired_time" value="{{=d.expired_time_str }}" class="layui-input x-date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">产品ID</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="数值" name="product_id" value="0" readonly class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="display: none">
                <label class="layui-form-label">权限ID</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="有效期" name="privilege_id" value="0" readonly class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">资源类型</label>
                <div class="layui-input-inline">
                    <select name="resource_type" data-value="{{d.resource_type}}">
                        {%html_options options=ProductPrivilegeModel::RESOURCE_TYPE%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">权限类型</label>
                <div class="layui-input-inline">
                    <select name="privilege_type" id="" data-value="{{d.privilege_type}}">
                        {%html_options options=ProductPrivilegeModel::PRIVILEGE_TYPE%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">数值</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="数值" name="value" value="{{=d.value }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <select name="status" data-value="{{=d.status}}">
                        {%html_options options=UserPrivilegeModel::STATUS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>
<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">aff</label>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户aff" name="aff" value="{{=d.aff }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">权限到期</label>
                <div class="layui-input-inline">
                    <input placeholder="权限到期" name="expired_time" value="{{=d.expired_time_str }}" class="layui-input x-date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">产品ID</label>
                <div class="layui-input-inline">
                    {{# if(d.product_id==0) { }}
                        <input lay-verify="required" placeholder="数值" name="product_id" value="0"  readonly class="layui-input">
                    {{# } else { }}
                        <div class="layui-input-inline">
                            <select name="product_id" data-value="{{=d.product_id}}">
                                {%html_options options=$productArr%}
                            </select>
                        </div>
                    {{# } }}
                </div>
            </div>
            <div class="layui-inline" style="display: none">
                <label class="layui-form-label">权限ID</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="有效期" name="privilege_id" value="0" readonly class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">资源类型</label>
                <div class="layui-input-inline">
                    <select name="resource_type" data-value="{{d.resource_type}}">
                        {%html_options options=ProductPrivilegeModel::RESOURCE_TYPE%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">权限类型</label>
                <div class="layui-input-inline">
                    <select name="privilege_type" id="" data-value="{{d.privilege_type}}">
                        {%html_options options=ProductPrivilegeModel::PRIVILEGE_TYPE%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">数值</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="数值" name="value" value="{{=d.value }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <select name="status" data-value="{{=d.status}}">
                        {%html_options options=UserPrivilegeModel::STATUS%}
                    </select>
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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit','upload','jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {
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
                            renderDateInput();
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
                    lazy('#user-add-dialog')
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            renderDateInput();
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
         function renderDateInput(){
            $('.x-date-time').each(function (key, item) {
                layDate.render({elem: item, 'type': 'datetime'});
            });
            $('.x-date').each(function (key, item) {
                layDate.render({elem: item});
            });
        }

        renderDateInput();
        //渲染日期
        // $('.x-date-time').each(function (key, item) {
        //     layDate.render({elem: item, 'type': 'datetime'});
        // });
        // $('.x-date').each(function (key, item) {
        //     layDate.render({elem: item});
        // });
        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>