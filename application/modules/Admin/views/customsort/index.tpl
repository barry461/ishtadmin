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
  

.layui-form .layui-input,
.layui-form .layui-textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
    background-color: #f9f9f9;
    font-size: 14px;
    font-family: Consolas, monospace;
    line-height: 1.8;
    transition: all 0.3s;
}


.layui-form .layui-textarea {
    min-height: 120px;
    resize: vertical;
}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">name</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">slug</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[slug]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
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
                           lay-data="{url:'{%url('listAjax')%}?orderBy[id]=asc', page:true, id:'test',toolbar:'#toolbar',limit:90}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id'}">ID</th>
                            <th lay-data="{field:'name'}">排序名称</th>
                            <!--<th lay-data="{field:'slug'}">标识</th>-->
                            <th lay-data="{field:'status',templet:'#tmp_status'}">显示状态</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="tmp_status">
                        <div style="margin: 0; line-height: 1.3;">
                            {{# if(d.status == '1') { }}
                            开启
                            {{# } else { }}
                            关闭
                            {{# } }}
                        </div>
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <!--<button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>-->

                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">
                                <i class="layui-icon layui-icon-edit"></i>修改</a>
                            <!--<a data-pk="{{=d.id}}" lay-event="del">删除</a>-->
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
                <label class="layui-form-label">排序名称：</label>
                <div class="layui-input-inline">
                    <input placeholder="name" name="name" maxlength="20"
                           value="{{=d.name }}" class="layui-input">

                </div>
            </div>
        </div>
        <!--<div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">标识：</label>
                <div class="layui-input-inline">
                    <input placeholder="slug" name="slug"  maxlength="30"
                           value="{{=d.slug }}" {{# if(d.id){ }}readonly{{#}}} class="layui-input" >

                </div>
            </div>
        </div>-->
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">显示状态：</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{=d.status }}">
                        {%html_options options=CustomSortModel::OPTION_STATUS%}
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
                            .area([1100 + 'px', document.body.offsetHeight + 'px'])
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
                let data = {'_pk': obj.data['mid']}
                data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    console.log(json);
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
