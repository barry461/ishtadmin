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
                            <label class="layui-form-label">用户uid</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[uid]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">活动</label>
                            <div class="layui-input-block">
                                <select name="where[lottery_id]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=$lotteryAry%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">奖品id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[item_id]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',defaultToolbar:[{title:'选显',layEvent:'selected_view',icon:'layui-icon-circle'},'filter','print','exports'],limit:20,limits:[10,20,30,40,50,60,70,80,90],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'log_id',width:'10%',align:'center'}">id</th>
                            <th lay-data="{field:'lottery_name',width:'15%'}">活动</th>
                            <th lay-data="{field:'uid',width:'15%',align:'center'}">用户uid</th>
                            <th lay-data="{field:'nickname',width:'10%',align:'center'}">用户昵称</th>
                            <th lay-data="{field:'item_name',width:'10%',align:'center'}">奖品名称</th>
                            <th lay-data="{field:'giveaway_type_str',width:'10%',align:'center'}">奖品类型</th>
                            <th lay-data="{field:'giveaway_id',width:'10%',align:'center'}">奖品位置</th>
                            <th lay-data="{field:'giveaway_num',width:'10%',align:'center'}">奖品数量</th>
                            <th lay-data="{field:'created_at',width:'10%',align:'center'}">抽奖时间</th>
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.item_id}}"
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
        <img style="display: inline-block;max-height: 152px;max-width: 107px;margin-bottom: 3px;" onclick="clickShowImage(this)"
             src="{{=d.img_url}}">
    </div>
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

{%include file="fooler.tpl"%}
<script>
    var fl = false
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
                case 'replace':
                    layer.prompt({
                        formType: 3,
                        title: '批量替换'
                    }, function (value, index) {
                        let reqData = {"from": value,'to':$('#to').val()};
                        console.log(reqData)
                        $.post("{%url('batch_replace')%}", reqData)
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    layer.close(index);
                                    table.reload('test');
                                }
                            })
                    });
                    $(".layui-layer-content input").attr({'placeholder':'输入原网址'})
                    $(".layui-layer-content").append("<br/><input type='text' id='to' class='layui-input' placeholder='输入新网址'/>")
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
            }
        });

        // 监听单元格编辑
        table.on('edit(table-toolbar)', function (obj) {
            let data = {'_pk': obj.data['log_id']}
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