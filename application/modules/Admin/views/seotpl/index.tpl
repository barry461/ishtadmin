{%include file="header.tpl"%}
<link rel="stylesheet" href="/static/backend/codemirror/css/codemirror.css">
<!-- CodeMirror 核心 -->
<script src="/static/backend/codemirror/js/codemirror.js"></script>
<!-- HTML 高亮支持 -->
<script src="/static/backend/codemirror/js/xml.js"></script>
<script src="/static/backend/codemirror/js/javascript.js"></script>
<script src="/static/backend/codemirror/js/css.js"></script>
<script src="/static/backend/codemirror/js/htmlmixed.js"></script>

<!-- 注释插件 -->
<script src="/static/backend/codemirror/js/comment.js"></script>
<script src="/static/backend/codemirror/js/sublime.js"></script>
<style>
    .CodeMirror {
        height: 300px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }
</style>
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
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[desc]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">KEY</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[key]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">值</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[val]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">配置</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[config]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">备注</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[mark]" placeholder="请输入"
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type: 'checkbox'}"></th>
                            <th lay-data="{field:'id'}">ID</th>
                            <th lay-data="{field:'desc'}">名称</th>
                            <th lay-data="{field:'key'}">KEY</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="attr-value">
                        <pre style="min-width: 300px;max-height: 100px"> {{=d.value}}</pre>
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
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
                <label class="layui-form-label">名称：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="名称" name="desc"
                           value="{{=d.desc ||"" }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">KEY：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="KEY" name="key"
                       value="{{=d.key }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">值：</label>
            <div class="layui-input-block">
                <textarea id="editor1" rows="20" name="val" class="layui-textarea">{{d.val || '' }}</textarea>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">配置：</label>
            <div class="layui-input-block">
                <textarea id="editor2" rows="15" name="config" class="layui-textarea" placeholder="SEO变量配置，格式如：{TITLE} = 页面标题 - {BRAND}">{{d.config || '' }}</textarea>
                <div class="layui-form-mid layui-word-aux">用于定义SEO变量模板，格式：{变量名} = 值</div>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">备注：</label>
            <div class="layui-input-block">
                <textarea id="editor3" rows="8" name="mark" class="layui-textarea" placeholder="纯文本备注说明">{{d.mark || '' }}</textarea>
                <div class="layui-form-mid layui-word-aux">纯文本说明，不影响实际功能</div>
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
    var editor1 = undefined;
    var editor2 = undefined;
    var editor3 = undefined;

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
                            Util.msgOk(json.msg, function () {
                                obj.update(json.data);
                            });
                        }
                    })
            }

        function clearCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post("{%url('clear')%}", from.serializeArray())
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
                            editor1!=undefined && editor1.save();
                            editor2!=undefined && editor2.save();
                            editor3!=undefined && editor3.save();
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            editor1 = CodeMirror.fromTextArea(document.getElementById('editor1'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
                            editor2 = CodeMirror.fromTextArea(document.getElementById('editor2'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
                            editor3 = CodeMirror.fromTextArea(document.getElementById('editor3'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
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
                        .width(1200)
                        .dialog(function (id, ele) {
                            editor1!=undefined && editor1.save();
                            editor2!=undefined && editor2.save();
                            editor3!=undefined && editor3.save();
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            editor1 = CodeMirror.fromTextArea(document.getElementById('editor1'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
                            editor2 = CodeMirror.fromTextArea(document.getElementById('editor2'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
                            editor3 = CodeMirror.fromTextArea(document.getElementById('editor3'), {
                              mode: 'htmlmixed',
                              theme: 'default',
                              lineNumbers: true,
                              indentWithTabs: true,
                              indentUnit: 2,
                              smartIndent: true,
                              keyMap: 'default',
                              extraKeys: {
                                Tab: function(cm) {
                                  if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                  } else {
                                    cm.replaceSelection("  ", "end");
                                  }
                                },
                                "Shift-Tab": function(cm) {
                                  cm.indentSelection("subtract");
                                },
                                "Ctrl-/": "toggleComment"
                              }
                            });
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