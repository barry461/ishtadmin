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
                            <label class="layui-form-label">聊天id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[girl_chat_id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">face</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[face]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">service</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[service]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">评论</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[comment]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" lay-search="" >
                                    <option value="">全部</option>
                                    {%html_options options=GirlChatCommentModel::STATUS %}
                                </select>

                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">拒绝原因</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[reject_reason]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">聊天时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created_at"%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">审核时间</label>
                            <div class="layui-input-block">
                                {%html_between name="updated_at"%}
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
                            <th lay-data="{field:'aff'}">aff</th>
                            <th lay-data="{field:'girl_chat_id'}">聊天id</th>
                            <th lay-data="{field:'face'}">face</th>
                            <th lay-data="{field:'service'}">service</th>
                            <th lay-data="{field:'comment'}">内容</th>
                            <th lay-data="{field:'status_str'}">状态</th>
                            <th lay-data="{field:'reject_reason'}">拒绝原因</th>
                            <th lay-data="{field:'created_at'}">创建时间</th>
                            <th lay-data="{field:'updated_at'}">更新时间</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            {{# if(d.status == 0){  }}
                            <a data-pk="{{=d.id}}" lay-event="accept">通过</a> |
                            <a data-pk="{{=d.id}}" lay-event="reject">拒绝</a> |
                            {{# } }}
                            <a data-pk="{{=d.id}}" lay-event="edit">修改</a> |
                            <a data-pk="{{=d.id}}" lay-event="del">删除</a>
                        </div>
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
                <label class="layui-form-label">aff：</label>
                <div class="layui-input-inline">

                    <input placeholder="aff" name="aff"
                           value="{{=d.aff }}" class="layui-input" disabled>

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">girl_chat_id：</label>
                <div class="layui-input-inline">

                    <input placeholder="girl_chat_id" name="girl_chat_id"
                           value="{{=d.girl_chat_id }}" class="layui-input" disabled>

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">face：</label>
                <div class="layui-input-inline">

                    <input placeholder="face" name="face"
                           value="{{=d.face }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">service：</label>
                <div class="layui-input-inline">

                    <input placeholder="service" name="service"
                           value="{{=d.service }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">内容：</label>
                <div class="layui-input-inline">

                    <input placeholder="comment" name="comment"
                           value="{{=d.comment }}" class="layui-input" disabled>

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">拒绝原因：</label>
                <div class="layui-input-inline">

                    <input placeholder="reject_reason" name="reject_reason"
                           value="{{=d.reject_reason }}" class="layui-input" disabled>

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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
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
                                .area([800 + 'px', '400px'])
                                .dialog(function (id, ele) {
                                    dialogCallback(id, ele, obj)
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                    Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                                });
                            break;


                        case 'accept':
                            layer.confirm('确定通过吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('accept')%}", {"_pk": $(that).data('pk')})
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

                        case 'reject':
                            layer.confirm('确定拒绝吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('reject')%}", {"_pk": $(that).data('pk')})
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
                })

            //监听头工具栏事件
            table.on('toolbar(table-toolbar)', function (obj) {
                let layEvent = obj.event,
                    checkStatus = table.checkStatus(obj.config.id),
                    data = checkStatus.data,
                    pkValAry = [],
                    pkName = $(this).data('pk');
                for (let i = 0; i < data.length; i++) {
                    if (typeof (data[i][pkName]) !== "undefined") {
                        pkValAry.push(data[i][pkName])
                    }
                }
                switch (layEvent) {
                    case 'add':
                        lazy('#user-edit-dialog')
                            .area([1100 + 'px', document.body.offsetHeight + 'px'])
                            .dialog(function (id, ele) {
                                dialogCallback(id, ele)
                            })
                            .laytpl(function () {
                                xx.renderSelect({}, $, form);
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            });
                        break;
                    case 'delSelect':
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