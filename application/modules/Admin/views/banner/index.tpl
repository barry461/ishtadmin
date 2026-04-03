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
                            <label class="layui-form-label">ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[name]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=BannerModel::STATUS%}
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
                            <th lay-data="{field:'name'}">名称</th>
                            <th lay-data="{templet:'#photolist',width:'6%'}">banner图</th>
                            <th lay-data="{templet:'#att1',width:'7%'}">宽 X 高</th>
                            <th lay-data="{field:'type_str'}">跳转类型</th>
                            <th lay-data="{field:'router'}">路由</th>
                            <th lay-data="{field:'url_str'}">跳转链接</th>
                            <th lay-data="{field:'status_str',width:'5.6%'}">上下架</th>
                            <th lay-data="{field:'created_at',width:'14%',templet:'#show-time'}">投放时间</th>
                            <th lay-data="{field:'created_at',width:'13.3%',templet:'#attr2'}">创建时间</th>
                            <th lay-data="{fixed: 'right',width: '20%' ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="att1">
                        {{d.img_width}} X {{d.img_height}}
                    </script>
                    <script type="text/html" id="show-time">
                        开始：{{d.start_at}}<br>
                        结束：{{d.end_at}}
                    </script>
                    <script type="text/html" id="attr2">
                        创：{{d.created_at}}<br>
                        改：{{d.updated_at}}
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
                            <button class="layui-btn layui-btn-sm" lay-event="batJoinElement" data-pk="id">批量加入组件
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a href="javascript:;" style="color: #0a6aa1" lay-event="joinElement" data-id="{{d.id}}">
                            加入组件</a>&nbsp;&nbsp;
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
        <img style="display: inline-block;width: 50px;height: 25px;margin-bottom: 3px;" onclick="clickShowImage(this)"
             src="{{=d.img_url}}">
    </div>
</script>
<script type="text/html" class="data-dialog" id="elementSelectList">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">选择组件</label>
            <div class="layui-input-inline">
                <select name="value" id="element-list">
                    {%html_options options=$elementArr%}
                </select>
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
<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="名称" name="name" value="{{=d.name }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">banner图片</label>
                <div class="layui-input-inline">
                    {%html_upload name='img_url' src='img_url' value='img_url'%}
                </div>
                <div class="size_tip">700 X 300</div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片宽度</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="图片宽度" name="img_width" value="{{d.img_width||700 }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">图片高度</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="图片高度" name="img_height" value="{{d.img_height||300 }}"
                           class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">投放开始：</label>
                <div class="layui-input-inline">
                    <input placeholder="投放开始时间" name="start_at" value="{{=d.start_at }}"
                           class="layui-input x-date-time">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">投放结束：</label>
                <div class="layui-input-inline">
                    <input placeholder="投放结束时间" name="end_at" value="{{=d.end_at }}"
                           class="layui-input x-date-time">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跳转类型</label>
            <div class="layui-input-block">
                <select name="type" data-value="{{=d.type}}">
                    {%html_options options=BannerModel::TYPE%}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">内部路由</label>
            <div class="layui-input-block">
                <select name="router" data-value="{{=d.router}}" lay-filter="router_list">
                    {%html_options options=FlutterRouterModel::router_list()%}
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">目标</label>
            <div class="layui-input-block">
                <input placeholder="url" name="url" value="{{=d.url }}" id="mubiao" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">上下架</label>
            <div class="layui-input-block">
                <select name="status" data-value="{{=d.status}}">
                    {%html_options options=BannerModel::STATUS%}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
                <p>1. 外部跳转，直接填写跳转的url；</p>
                <p>2. 内部跳转。需要选择对应的路由，然后在跳转目标，填写跳转的参数 <br>
                    假设路由是 app/:id/:type，我们需要跳转到 id=123,type为222的路径，那么目标应该填写 <span style="color: red">/:123/:111</span>
                    <br>
                    如果参考路由只有一个参数，可以省略 <span style="color: red">/:</span>
                </p>
                <p>3. 当前参考参数：<span id="shili" style="color: red;"></span></p>
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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        form.on('select(router_list)', function (data) {
            let pos = data.value.indexOf("/:")
            if (pos !== -1) {
                $('#shili').html(data.value.substr(pos));
            } else {
                $('#shili').html('当前路由没有参数');
            }
        });

        function join(data, obj) {
            $.post("{%url('joinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg, location.reload);
                    }
                })
        }

        function batJoin(data, obj) {
            $.post("{%url('batJoinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg, location.reload);
                    }
                })
        }

        let verify = {}

            table.on('tool(table-toolbar)', function (obj) {
                //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                var data = obj.data,
                    layEvent = obj.event,
                    that = this;
                switch (layEvent) {
                    case 'joinElement':
                        _id = $(that).data('id');
                        lazy('#elementSelectList')
                            .offset('auto')
                            .data(data)
                            .title('快速回复')
                            .area(['500px', '300px'])
                            .dialog(function (id, ele) {
                                join({"id":_id , "element_id":$('#element-list').val()} , obj);
                                layer.close(id);
                            })
                            .laytpl(function () {
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
                case 'batJoinElement':
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
                    lazy('#elementSelectList')
                        .offset('auto')
                        .data(data)
                        .title('批量加入组件')
                        .area(['500px', '300px'])
                        .dialog(function (id, ele) {
                            batJoin({"id":pkValAry.join(','), "element_id":$('#element-list').val()} , obj);
                            layer.close(id);
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
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