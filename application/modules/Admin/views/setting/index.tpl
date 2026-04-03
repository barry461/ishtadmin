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
                                <input type="text" name="search[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">配置名</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">配置key名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[var_name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">配置值</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[value]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">配置备注</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[remark]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">不限</option>
                                    {%html_options options=['yes'=>'yes' , 'no'=>'no']%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',limit:1000,limits:[10,20,30,40,50,60,70,80,90,1000],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type: 'checkbox',field:'id'}"></th>
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'title'}">配置名</th>
                            <th lay-data="{field:'var_name'}">配置key名称</th>
                            <th lay-data="{field:'value'}">配置值</th>
                            <th lay-data="{field:'remark'}">配置备注</th>
                            <th lay-data="{field:'status'}">状态</th>
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
                            <button class="layui-btn layui-btn-sm" lay-event="refresh">
                                刷新
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="clear">
                                缓存清理
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
            <div class="layui-inline">
                <label class="layui-form-label">配置名：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="配置名" name="title"
                           value="{{=d.title }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">配置key名称：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="配置key名称" name="var_name"
                       value="{{=d.var_name }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">配置值：</label>
            <div class="layui-input-block">
                {%html_textarea name='value' value='{{=d.value }}'%}
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">配置备注：</label>
            <div class="layui-input-block">
                {%html_textarea name='remark' value='{{=d.remark }}'%}
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">
                    {%html_radios options=SettingModel::STATUS name="status" %}
                </div>
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>
<script type="text/html" class="data-dialog" id="keylist-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>缓存列表</legend>
    </fieldset>
    <div class="layui-row" style="padding-left: 20px">
        <div class="layui-btn-container">
            {%foreach from=$key_list item=$name%}
                <button type="button" onclick="clearCached(this)" data-name="{%$name%}" class="layui-btn">{%$name%}</button>
            {%/foreach%}
        </div>
    </div>
</script>
{%include file="fooler.tpl"%}
<script>
    function clearCached(ele){}
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {
        clearCached = function (that){
            let id = layer.load(3, {shade:[0.5,'#fff']});
            layui.jquery.post("{%url('clear')%}", {"name": that.getAttribute('data-name')})
                .then(function (json) {
                    layer.close(id);
                    Util.msgOk('操作成功');
                })
        }

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
                        Util.msgOk(json.msg, function () {
                            obj.update(json.data);
                        });
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
                        .width(1200)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                        });
                    break;
            }
        })

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
                case 'refresh':
                    $.post("{%url('refresh')%}")
                        .then(function (json) {
                            Util.msgOk(json.msg, function () {
                                console.log(json);
                            });
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
                case 'clear':
                    lazy('#keylist-dialog')
                        .area(['900px',false])
                        .dialog(function (id, ele) { })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                        });
                    break;
            }
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