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
                                <input type="text" name="where[id]" placeholder="请输入ID"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">账号</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[username]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">IP</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[ip]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">操作</label>
                            <div class="layui-input-block">
                                <select name="where[action]">
                                    <option value="">全部</option>
                                    {%html_options options=AdminLogModel::ACTION_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">关键词</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[log]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">操作时间</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[created_at]" placeholder="请输入"
                                       autocomplete="off" class="layui-input x-date">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">JSON查询</label>
                            <div class="layui-input-block">
                                <input type="text" name="json[context]" placeholder="'$.old._pk'=985315"
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
                            <th lay-data="{width:80,field:'id'}">id</th>
                            <th lay-data="{width:80,field:'username',align:'center'}">账户</th>
                            <th lay-data="{width:160,field:'ip',align:'center'}">IP</th>
                            <th lay-data="{width:80,field:'action_name',align:'center'}">操作</th>
                            <th lay-data="{width:207,field:'log'}">简介</th>
                            <th lay-data="{width:207,field:'referrer'}">URL</th>
                            <th lay-data="{width:207,field:'created_at',align:'center'}">时间</th>
                            <th lay-data="{fixed: 'right' ,align:'center', toolbar: '#operate-toolbar'}">操作</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info">
                            <i class="layui-icon layui-icon-eye"></i>查看详情</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    /* 方法2：自定义高亮样式 */
    #jsonPre {
        float: left;
        width: 836px;
        white-space: pre;
        margin: 0px auto 0px 30px;
        height: auto;
        outline: 1px solid #ccc;
        padding: 5px;
        overflow: auto;
    }

    .string {
        color: green;
    }

    .number {
        color: darkorange;
    }

    .boolean {
        color: blue;
    }

    .null {
        color: magenta;
    }

    .key {
        color: red;
    }
</style>

<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>数据操作详情信息</legend>
    </fieldset>
    <pre id="jsonPre"></pre>
</script>

{%include file="fooler.tpl"%}
<script>
    function parserJson(str) {
        // 设置缩进为2个空格
        str = JSON.stringify(JSON.parse(str), null, 3);
        str = str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        return str.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'info':
                    lazy('#user-edit-dialog')
                        .data(data)
                        .width(900)
                        .dialog(function (id, ele) {
                            layer.close(id);
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            $('#jsonPre').html(parserJson(data.context))
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

        $('.x-date').each(function (key, item) {
            layDate.render({elem: item});
        });
        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>