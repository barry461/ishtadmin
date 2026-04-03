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
                                <input type="text" name="where[id]" placeholder="请输入" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">支付类型</label>
                            <div class="layui-input-block">
                                <select name="where[pay_type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options= AiTaskModel::PAY_TYPE%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options= AiTaskModel::STATUS_TIPS%}
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
                           lay-data="{url:'{%url('listAjax')%}',defaultToolbar:[{title:'选显',layEvent:'selected_view',icon:'layui-icon-circle'},'filter','print','exports'], page:true, id:'test',limit:20,limits:[10,20,30,40,50,60,70,80,90,100],toolbar:'#toolbar',done:table_load}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width:'6%',align:'center'}">id</th>
                            <th lay-data="{field:'aff',width:'10%'}">用户</th>
                            <th lay-data="{field:'times',width:'10%'}">重试次数</th>
                            <th lay-data="{templet:'#photolist',width:'13%'}">原图</th>
                            <th lay-data="{templet:'#photolist2',width:'13%'}">效果图</th>
                            <th lay-data="{field:'status_str',width:'13%',align:'center'}">状态</th>
                            <th lay-data="{field:'pay_type_str',width:'8%',align:'center'}">支付类型</th>
                            <th lay-data="{field:'created_at',width:'14%'}">创建时间</th>
                            <th lay-data="{field:'updated_at',width:'14%'}">修改时间</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </tr>
                        </thead>
                    </table>

                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="batRetry" data-pk="id">批量重试</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        {{#if(d.refunded == 0){ }}
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}"
                           lay-event="retry">
                            <i class="layui-icon layui-icon-delete"></i>重试</a>
                        {{# } }}
                        {{#if(d.status== 3 && d.refunded == 0){ }}
                        <a class="layui-btn layui-btn-warm layui-btn-xs" data-pk="{{=d.id}}"
                           lay-event="retund">
                            <i class="layui-icon layui-icon-delete"></i>退款</a>
                        {{# } }}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    table_load = function () {
        //动态监听表头高度变化，冻结行跟着改变高度
        $(".layui-table-header  tr").resize(function () {
            $(".layui-table-header  tr").each(function (index, val) {
                $($(".layui-table-fixed .layui-table-header table tr")[index]).height($(val).height());
            });
        });
        //初始化高度，使得冻结行表头高度一致
        $(".layui-table-header  tr").each(function (index, val) {
            $($(".layui-table-fixed .layui-table-header table tr")[index]).height($(val).height());
        });
        //动态监听表体高度变化，冻结行跟着改变高度
        $(".layui-table-body  tr").resize(function () {
            $(".layui-table-body  tr").each(function (index, val) {
                $($(".layui-table-fixed .layui-table-body table tr")[index]).height($(val).height());
            });
        });
        //初始化高度，使得冻结行表体高度一致
        $(".layui-table-body  tr").each(function (index, val) {
            $($(".layui-table-fixed .layui-table-body table tr")[index]).height($(val).height());
        });
    }
</script>

<script type="text/html" id="photolist">
    <div style="line-height: normal">
        <img style="display: inline-block;max-width: 107px;margin-bottom: 3px;max-height:152px;" onclick="clickShowImage(this)"
             src="{{=d.media_url}}">
    </div>
</script>
<script type="text/html" id="photolist2">
    <div style="line-height: normal">
        <img style="display: inline-block;max-width: 107px;margin-bottom: 3px;max-height:152px;" onclick="clickShowImage(this)"
             src="{{=d.media2_url}}">
    </div>
</script>

{%include file="fooler.tpl"%}
<script>
    var fl = false;
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

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
                case 'retry':
                    $.post("{%url('aiDraw')%}", {"_pk": $(that).data('pk')})
                        .then(function (json) {
                            if (json.code) {
                                Util.msgErr(json.msg);
                            } else {
                                Util.msgOk(json.msg);
                                table.reload('test');
                            }
                        })
                    break;
                case 'retund':
                    $.post("{%url('retund')%}", {"_pk": $(that).data('pk')})
                        .then(function (json) {
                            if (json.code) {
                                Util.msgErr(json.msg);
                            } else {
                                Util.msgOk(json.msg);
                                table.reload('test');
                            }
                        })
                    break;
            }
        })

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {
                case 'batRetry':
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
                    layer.confirm('真的批量重试吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('batRetry')%}", {"ids": pkValAry.join(',')})
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
                case 'add':
                    lazy('#user-edit-dialog')
                        .width(`${document.body.clientWidth-300}px`)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                        });
                    break;
                case 'selected_view':
                    fl = !fl;
                    layui.each(obj.config.cols, function (i1, item1) {
                        layui.each(item1, function (i2, item2) {
                            console.log(item2)
                            let ishide = false
                            if (fl){
                                // 隐藏其他字段
                                if(item2.field === undefined || item2.field === 'url'){
                                    ishide = false
                                }else{
                                    ishide = true
                                }
                            }
                            let hide = item2.hide
                            item2.hide = ishide
                            if (hide !== item2.hide) {
                                if(item2.hide){
                                    $('th[data-field="'+item2.field+'"]').hide()
                                    $('td[data-field="'+item2.field+'"]').hide()
                                }else{
                                    $('th[data-field="'+item2.field+'"]').show()
                                    $('td[data-field="'+item2.field+'"]').show()
                                }
                            }
                            table.resize();
                        })
                    })
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