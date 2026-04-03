{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>
    .layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }

</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">用户管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">用户ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[uid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">用户名</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">用户类型</label>
                            <div class="layui-input-block">
                                <select name="where[group]">
                                    <option value="">全部</option>
                                    <option value="remote_user">远程系统用户</option>
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">注册时间</label>
                            <div class="layui-input-block">
                                {%html_between name='created'%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test', toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'uid',width:80}">用户ID</th>
                            <th lay-data="{field:'name',width:120}">用户名</th>
                            <th lay-data="{field:'screenName',width:150}">昵称</th>
                            <th lay-data="{field:'group',width:100,templet:'#role-tpl'}">用户类型</th>
                            <th lay-data="{fixed:'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                        <script type="text/html" id="role-tpl">
                            远程系统用户
                        </script>

                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加用户
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a href="javascript:;" style="color: #1AB394" lay-event="edit">修改</a> |
                        <a href="javascript:;" style="color: #1AB394" data-pk="{{=d.uid}}" lay-event="pwd">密码</a> |
                        <a href="javascript:;" data-pk="{{=d.uid}}" lay-event="del">删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>用户信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户名" name="name" value="{{=d.name }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">昵称</label>
                <div class="layui-input-inline">
                    <input placeholder="昵称" name="screenName" value="{{=d.screenName }}"
                           class="layui-input">
                </div>
            </div>
        </div>
      
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户类型</label>
                <div class="layui-input-inline">
                    <input type="text" value="远程系统用户" class="layui-input" readonly>
                    <input type="hidden" name="group" value="remote_user">
                </div>
            </div>

        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">SEO标题</label>
            <div class="layui-input-block">
                <input placeholder="SEO标题" name="seo_title" value="{{=d.seo_title }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">SEO描述</label>
            <div class="layui-input-block">
                {%html_textarea name='seo_description' value='{{d.seo_description }}'%}
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">SEO关键词</label>
            <div class="layui-input-block">
                <input placeholder="SEO关键词" name="seo_keywords" value="{{=d.seo_keywords }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.uid}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>

<!-- 添加用户对话框 -->
<script type="text/html" class="data-dialog" id="user-add-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>添加用户</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-add">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="请输入用户名" name="name" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">昵称</label>
                <div class="layui-input-inline">
                    <input placeholder="请输入昵称" name="screenName" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">密码</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="请输入密码" name="password" type="password" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">确认密码</label>
                <div class="layui-input-inline">
                    <input lay-verify="required|confirmPassword" placeholder="请再次输入密码" name="confirmPassword" type="password" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">网站</label>
            <div class="layui-input-block">
                <input placeholder="请输入网站地址" name="url" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <button class="layui-btn submit" lay-submit="" lay-filter="add"></button>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {
            confirmPassword: function(value, item) {
                var password = $('input[name="password"]').val();
                if (value !== password) {
                    return '两次输入的密码不一致';
                }
            }
        }
            table.on('tool(table-toolbar)', function (obj) {
                //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                var data = obj.data,
                    layEvent = obj.event,
                    that = this;
                switch (layEvent) {
                    case 'pwd':
                        top.layer.prompt({title: '修改密码'}, function (value, index) {
                            top.layer.close(index);
                            $.post("{%url('change_pwd')%}", {"uid": $(that).data('pk'), "pwd":value})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                    }
                                })
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
                    lazy('#user-add-dialog')
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, null, "{%url('save')%}")
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

        function dialogCallback(id, ele, obj, saveUrl) {
            let from = $(ele).find('form')
            $.post(saveUrl || "{%url('save')%}", from.serializeArray())
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
        function renderDateInput() {
            $('.x-date-time').each(function (key, item) {
                layDate.render({elem: item, 'type': 'datetime'});
            });
            $('.x-date').each(function (key, item) {
                layDate.render({elem: item});
            });
        }

        renderDateInput();

        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>