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
    .layuiadmin-card-header-auto .layui-select-title input{
        width: 168px;
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
                            <label class="layui-form-label">ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">用户aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=MemberUpdateLogModel::STATUS_TIPS%}
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
                            <th lay-data="{field:'id',minWidth: 80}">id</th>
                            <th lay-data="{field:'aff',minWidth:259,templet:'#member-basis'}">用户</th>
                            <th lay-data="{field:'nickname',align:'center',minWidth:110}">昵称</th>
                            <th lay-data="{minWidth:210,templet:'#a2'}">头像</th>
                            <th lay-data="{field:'status_str',minWidth: 110}">状态</th>
                            <th lay-data="{field:'refuse_reason',minWidth: 168}">原因</th>
                            <th lay-data="{templet:'#time-attr',minWidth: 168}">时间</th>
                            <th lay-data="{fixed: 'right',minWidth: 250 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                        <script type="text/html" id="a2">
                            <div style="line-height: normal">
                                {{# if(d.thumb){ }}
                                <img style="display: inline-block;max-width: 107px;max-height: 107px;margin-bottom: 3px;" onclick="clickShowImage(this)" src="{{d.thumb}}">
                                {{# } }}
                            </div>
                        </script>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
                            <button class="layui-btn layui-btn-sm" lay-event="reviewSelect" data-pk="id">批量审核</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        {{# if(d.status == 0){ }}
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="review_one" data-pk="{{d.id}}">
                            <i class="layui-icon layui-icon-snowflake"></i>审核</a>
                        {{# } }}
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}"
                           lay-event="del">
                            <i class="layui-icon layui-icon-delete"></i>删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" class="data-dialog" id="reviewSelect">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">审核：</label>
            <div class="layui-input-inline">
                <select name="review_status" data-value="2">
                    <option value="{%MemberUpdateLogModel::STATUS_PASS%}">通过</option>
                    <option value="{%MemberUpdateLogModel::STATUS_REJECT%}">拒绝</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">原因：</label>
            <div class="layui-input-inline">
                <select name="review_reason" data-value="">
                    <option value=""></option>
                    {%html_options options=$reasons%}
                </select>
            </div>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {
        $ = layui.jquery;


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
                case 'review_one':
                    lazy('#reviewSelect')
                        .data(data)
                        .offset('auto')
                        .area(['637px','415px'])
                        .title('审核帖子')
                        .dialog(function (id, ele) {
                            let status = $('select[name=review_status]').val();
                            let reason = $('select[name=review_reason]').val();
                            $.post("{%url('review_select')%}", {'ids':$(that).data('pk'),"status":status,'reason':reason})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                        layer.close(id);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                        layer.close(id);
                                    }
                                })
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
                case 'reviewSelect':
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
                    lazy('#reviewSelect')
                        .data(data)
                        .offset('auto')
                        .area(['637px','415px'])
                        .title('审核帖子')
                        .dialog(function (id, ele) {
                            let status = $('select[name=review_status]').val();
                            let reason = $('select[name=review_reason]').val();
                            console.log(status, reason)
                            $.post("{%url('review_select')%}", {'ids':pkValAry.join(','),"status":status,'reason':reason})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                        layer.close(id);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                        layer.close(id);
                                    }
                                })
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
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