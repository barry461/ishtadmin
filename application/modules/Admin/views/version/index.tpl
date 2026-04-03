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
                            <label class="layui-form-label">渠道标识</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[channel]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">强新</label>
                            <div class="layui-input-block">
                                <select name="where[must]">
                                    <option value="">全部</option>
                                    {%html_options options=VersionModel::MUST%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options options=VersionModel::STATUS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">公告状态</label>
                            <div class="layui-input-block">
                                <select name="where[mstatus]">
                                    <option value="">全部</option>
                                    {%html_options options=VersionModel::MSTATUS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">域名跟随</label>
                            <div class="layui-input-block">
                                <select name="where[custom]">
                                    <option value="">全部</option>
                                    {%html_options options=VersionModel::CUSTOM_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">创建时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created_at"%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',limit:90,limits:[10,20,30,40,50,60,70,80,90,100,1000],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'version'}">版本号</th>
                            <th lay-data="{field:'type'}">型号</th>
                            <th lay-data="{field:'apk'}">下载连接</th>
                            <th lay-data="{field:'tips'}">更新说明</th>
                            <th lay-data="{field:'must'}">强更</th>
                            <th lay-data="{field:'message'}">系统维护公告</th>
                            <th lay-data="{field:'created_at'}">创建时间</th>
                            <th lay-data="{field:'status_str'}">状态</th>
                            <th lay-data="{field:'custom_str'}">域名跟随</th>
                            <th lay-data="{field:'mstatus',type:'enum',value:{%json_str(VersionModel::MSTATUS)%}}">系统公告</th>
                            <th lay-data="{field:'channel'}">渠道标识</th>
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


<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">版本号：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="版本号" name="version"
                       value="{{=d.version }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">型号：</label>
            <div class="layui-input-block">
                <select name="type" data-value="{{=d.type }}">
                    {%html_options options=VersionModel::TYPE%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">下载连接：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="下载连接" name="apk"
                       value="{{=d.apk }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">更新说明：</label>
            <div class="layui-input-block">
                <textarea name="tips" class="layui-textarea" rows="6">{{=d.tips }}</textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">更新状态：</label>
            <div class="layui-input-block">
                <select name="must" data-value="{{=d.must }}">
                    {%html_options options=VersionModel::MUST%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">状态：</label>
            <div class="layui-input-inline">
                <select name="status" data-value="{{=d.status }}">
                    {%html_options options=VersionModel::STATUS%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">域名跟随：</label>
            <div class="layui-input-inline">
                <select name="custom" data-value="{{=d.custom }}">
                    {%html_options options=VersionModel::CUSTOM_TIPS%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">系统维护公告：</label>
            <div class="layui-input-block">
                <textarea name="message" class="layui-textarea" rows="6">{{=d.message }}</textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">系统公告状态：</label>
            <div class="layui-input-block">
                <select name="mstatus" data-value="{{=d.mstatus }}">
                    {%html_options options=VersionModel::MSTATUS%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">渠道标识：</label>
            <div class="layui-input-block">
                <input placeholder="渠道标识" name="channel"
                       value="{{=d.channel }}" class="layui-input">
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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

        function dialogCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post("{%url('save')%}", from.serializeArray())
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

        //渲染组建
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