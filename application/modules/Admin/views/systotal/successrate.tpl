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
                            <label class="layui-form-label">日期</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[date]" autocomplete="off" value="{%$currDate%}" class="layui-input" id="search-date">
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
                           lay-data="{url:'{%url('listAjax')%}',where:{'search[date]':'{%$currDate%}'}, limit:200,limits:[10,20,30,40,50,60,70,80,90,100,200], page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id',align:'center'}">id</th>
                            <th lay-data="{field:'name',align:'center'}">键名</th>
                            <th lay-data="{field:'value',align:'center',sort:true}">统计</th>
                            <th lay-data="{field:'date',align:'center'}">日期</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="sysData"
                                    data-pk="id">同步当天实时数据
                            </button>
                        </div>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

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
                    case 'sysData':
                        layer.confirm('确认发起数据同步请求?', function (index) {
                            layer.close(index);
                            $.post("{%url('sys_data')%}", {})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
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
                // where['search[name]'] = "visit:";

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
            //渲染单个日期
            layDate.render({
                elem: '#search-date'
            });
            form.verify(verify);
            layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
        })
</script>
