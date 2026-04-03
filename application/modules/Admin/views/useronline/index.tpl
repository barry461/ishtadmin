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
    .add{
        color: #0bc15f;
    }
    .sub{
        color: #cc0000;
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
                            <label class="layui-form-label">日期</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline" style="width:120px">
                                    <input name="between[date][from]" value="{%$start%}" type="text" placeholder="yyyy-mm-dd" autocomplete="off" class="layui-input x-date" lay-key="1">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width:120px">
                                    <input name="between[date][to]" value="{%$end%}" type="text" placeholder="yyyy-mm-dd" autocomplete="off" class="layui-input x-date" lay-key="2">
                                </div>
                            </div>

                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                            <button class="layui-btn layuiadmin-btn-useradmin contrast">
                                <i class="layui-icon layuiadmin-button-btn">上周数据对比</i>
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
                            <th lay-data="{field:'date',width:'100'}"></th>
                            <th lay-data="{field:'t0',templet:'#tmp-0',width:'62'}">00:00</th>
                            <th lay-data="{field:'t1',templet:'#tmp-1',width:'62'}">01:00</th>
                            <th lay-data="{field:'t2',templet:'#tmp-2',width:'62'}">02:00</th>
                            <th lay-data="{field:'t3',templet:'#tmp-3',width:'62'}">03:00</th>
                            <th lay-data="{field:'t4',templet:'#tmp-4',width:'62'}">04:00</th>
                            <th lay-data="{field:'t5',templet:'#tmp-5',width:'62'}">05:00</th>
                            <th lay-data="{field:'t6',templet:'#tmp-6',width:'62'}">06:00</th>
                            <th lay-data="{field:'t7',templet:'#tmp-7',width:'62'}">07:00</th>
                            <th lay-data="{field:'t8',templet:'#tmp-8',width:'62'}">08:00</th>
                            <th lay-data="{field:'t9',templet:'#tmp-9',width:'62'}">09:00</th>
                            <th lay-data="{field:'t10',templet:'#tmp-10',width:'62'}">10:00</th>
                            <th lay-data="{field:'t11',templet:'#tmp-11',width:'62'}">11:00</th>
                            <th lay-data="{field:'t12',templet:'#tmp-12',width:'62'}">12:00</th>
                            <th lay-data="{field:'t13',templet:'#tmp-13',width:'62'}">13:00</th>
                            <th lay-data="{field:'t14',templet:'#tmp-14',width:'62'}">14:00</th>
                            <th lay-data="{field:'t15',templet:'#tmp-15',width:'62'}">15:00</th>
                            <th lay-data="{field:'t16',templet:'#tmp-16',width:'62'}">16:00</th>
                            <th lay-data="{field:'t17',templet:'#tmp-17',width:'62'}">17:00</th>
                            <th lay-data="{field:'t18',templet:'#tmp-18',width:'62'}">18:00</th>
                            <th lay-data="{field:'t19',templet:'#tmp-19',width:'62'}">19:00</th>
                            <th lay-data="{field:'t20',templet:'#tmp-20',width:'62'}">20:00</th>
                            <th lay-data="{field:'t21',templet:'#tmp-21',width:'62'}">21:00</th>
                            <th lay-data="{field:'t22',templet:'#tmp-22',width:'62'}">22:00</th>
                            <th lay-data="{field:'t23',templet:'#tmp-23',width:'62'}">23:00</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="tmp-0">
                        <div style="line-height: normal">
                            {{=d.t0.number}}
                        </div>
                        <div class="showchange {{=d.t0.type}}">
                            {{=d.t0.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-1">
                        <div style="line-height: normal">
                            {{=d.t1.number}}
                        </div>
                        <div class="showchange {{=d.t1.type}}">
                            {{=d.t1.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-2">
                        <div style="line-height: normal">
                            {{=d.t2.number}}
                        </div>
                        <div class="showchange {{=d.t2.type}}">
                            {{=d.t2.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-3">
                        <div style="line-height: normal">
                            {{=d.t3.number}}
                        </div>
                        <div class="showchange {{=d.t3.type}}">
                            {{=d.t3.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-4">
                        <div style="line-height: normal">
                            {{=d.t4.number}}
                        </div>
                        <div class="showchange {{=d.t4.type}}">
                            {{=d.t4.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-5">
                        <div style="line-height: normal">
                            {{=d.t5.number}}
                        </div>
                        <div class="showchange {{=d.t5.type}}">
                            {{=d.t5.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-6">
                        <div style="line-height: normal">
                            {{=d.t6.number}}
                        </div>
                        <div class="showchange {{=d.t6.type}}">
                            {{=d.t6.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-7">
                        <div style="line-height: normal">
                            {{=d.t7.number}}
                        </div>
                        <div class="showchange {{=d.t7.type}}">
                            {{=d.t7.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-8">
                        <div style="line-height: normal">
                            {{=d.t8.number}}
                        </div>
                        <div class="showchange {{=d.t8.type}}">
                            {{=d.t8.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-9">
                        <div style="line-height: normal">
                            {{=d.t9.number}}
                        </div>
                        <div class="showchange {{=d.t9.type}}">
                            {{=d.t9.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-10">
                        <div style="line-height: normal">
                            {{=d.t10.number}}
                        </div>
                        <div class="showchange {{=d.t10.type}}">
                            {{=d.t10.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-11">
                        <div style="line-height: normal">
                            {{=d.t11.number}}
                        </div>
                        <div class="showchange {{=d.t11.type}}">
                            {{=d.t11.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-12">
                        <div style="line-height: normal">
                            {{=d.t12.number}}
                        </div>
                        <div class="showchange {{=d.t12.type}}">
                            {{=d.t12.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-13">
                        <div style="line-height: normal">
                            {{=d.t13.number}}
                        </div>
                        <div class="showchange {{=d.t13.type}}">
                            {{=d.t13.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-14">
                        <div style="line-height: normal">
                            {{=d.t14.number}}
                        </div>
                        <div class="showchange {{=d.t14.type}}">
                            {{=d.t14.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-15">
                        <div style="line-height: normal">
                            {{=d.t15.number}}
                        </div>
                        <div class="showchange {{=d.t15.type}}">
                            {{=d.t15.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-16">
                        <div style="line-height: normal">
                            {{=d.t16.number}}
                        </div>
                        <div class="showchange {{=d.t16.type}}">
                            {{=d.t16.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-17">
                        <div style="line-height: normal">
                            {{=d.t17.number}}
                        </div>
                        <div class="showchange {{=d.t17.type}}">
                            {{=d.t17.change}}
                        </div>
                    </script> <script type="text/html" id="tmp-18">
                        <div style="line-height: normal">
                            {{=d.t18.number}}
                        </div>
                        <div class="showchange {{=d.t18.type}}">
                            {{=d.t18.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-19">
                        <div style="line-height: normal">
                            {{=d.t19.number}}
                        </div>
                        <div class="showchange {{=d.t19.type}}">
                            {{=d.t19.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-20">
                        <div style="line-height: normal">
                            {{=d.t20.number}}
                        </div>
                        <div class="showchange {{=d.t20.type}}">
                            {{=d.t20.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-21">
                        <div style="line-height: normal">
                            {{=d.t21.number}}
                        </div>
                        <div class="showchange {{=d.t21.type}}">
                            {{=d.t21.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-22">
                        <div style="line-height: normal">
                            {{=d.t22.number}}
                        </div>
                        <div class="showchange {{=d.t22.type}}">
                            {{=d.t22.change}}
                        </div>
                    </script>
                    <script type="text/html" id="tmp-23">
                        <div style="line-height: normal">
                            {{=d.t23.number}}
                        </div>
                        <div class="showchange {{=d.t23.type}}">
                            {{=d.t23.change}}
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
            $('.contrast').click(function () {
                $('.showchange').toggle();
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
