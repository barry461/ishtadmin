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
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">昵称</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[nickname]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">是否认证</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options options=MemberModel::AUTH_STATUS%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:90, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'aff'}">aff</th>
                            <th lay-data="{field:'nickname'}">昵称</th>
                            <th lay-data="{templet:'#clubs-attr',width: 178}">价格</th>
                            <th lay-data="{field:'status_str'}">是否认证</th>
                            <th lay-data="{field:'ban_post_str'}">是否封禁</th>
                            <th lay-data="{templet:'#income-attr',width: 188}">收益</th>
                            <th lay-data="{field:'income_money'}">收益余额</th>
                            <th lay-data="{templet:'#time-attr',width: 168}">时间</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>

                        <script type="text/html" id="income-attr">
                            月卡收益：{{d.month_income_str}} &nbsp;|&nbsp;
                            季卡收益：{{d.quarter_income_str}} <br>
                            年卡收益：{{d.year_income_str}} &nbsp;|&nbsp;
                            总收益：{{d.income_str}}
                        </script>

                        <script type="text/html" id="clubs-attr">
                            月卡价格：{{d.post_club_month}} &nbsp;<br>
                            季卡价格：{{d.post_club_quarter}}<br>
                            年卡价格：{{d.post_club_year}}&nbsp;
                        </script>

                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">

                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a href="javascript:void(0);" lay-event="post_list" data-aff="{{=d.aff}}">帖子</a> |
                            <a lay-event="edit">修改</a> |
                            {{#if(d.ban_post== 1){ }}
                                <a href="javascript:void(0);" lay-event="unban" data-pk="{{=d.id}}">解封</a> |
                            {{# } else { }}
                                <a href="javascript:void(0);" lay-event="ban" data-pk="{{=d.id}}">封禁</a> |
                            {{# } }}
                            {{#if(d.status== 0){ }}
                                <a href="javascript:void(0);" lay-event="creator" data-pk="{{=d.id}}">认证</a>
                            {{# } else { }}
                                <a href="javascript:void(0);" lay-event="uncreator" data-pk="{{=d.id}}">取消认证</a>
                            {{# } }}
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
                <label class="layui-form-label">月卡价格：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="帖子订阅月卡价格" name="post_club_month"
                           value="{{=d.post_club_month }}" class="layui-input">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">季卡价格：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="帖子订阅季卡价格" name="post_club_quarter"
                           value="{{=d.post_club_quarter }}" class="layui-input">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">年卡价格：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="帖子订阅年卡价格" name="post_club_year"
                           value="{{=d.post_club_year }}" class="layui-input">
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
                        case 'ban':
                            layer.confirm('确认禁言用户?', function (index) {
                                layer.close(index);
                                $.post("{%url('banInfo')%}", {"id": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            obj.update(json.data);
                                            let index = $(obj.tr).data('index')
                                            table.reload('test');
                                            // table.cache['test'][index] = json.data;
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'unban':
                            layer.confirm('确认解禁用户?', function (index) {
                                layer.close(index);
                                $.post("{%url('unbanInfo')%}", {"id": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            obj.update(json.data);
                                            let index = $(obj.tr).data('index')
                                            table.reload('test');
                                            // table.cache['test'][index] = json.data;
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'uncreator':
                            layer.confirm('确认要取消用户创作者身份吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('uncreator')%}", {"id": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            obj.update(json.data);
                                            let index = $(obj.tr).data('index')
                                            table.reload('test');
                                            // table.cache['test'][index] = json.data;
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'creator':
                            layer.confirm('确认要认证用户成为创作者吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('creator')%}", {"id": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            obj.update(json.data);
                                            let index = $(obj.tr).data('index')
                                            table.reload('test');
                                            // table.cache['test'][index] = json.data;
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'edit':
                            lazy('#user-edit-dialog')
                                .data(data)
                                .area([1100 + 'px', (document.body.offsetHeight - 200) + 'px'])
                                .dialog(function (id, ele) {
                                    dialogCallback(id, ele, obj)
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                    Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                                });
                            break;
                        case 'post_list':
                            ddd = document.documentElement;
                            lazy('')
                                .iframe('{%url('post/index')%}?aff='+data['aff'])
                                .area([`${ddd.clientWidth - 200}px` , `${ddd.clientHeight}px`])
                                .title(`数据管理`)
                                .start(function () {

                                })
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
