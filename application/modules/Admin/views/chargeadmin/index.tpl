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
                            <label class="layui-form-label">管理员</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[admin]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">用户aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[to_aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">type</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[type]" placeholder="请输入"
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
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'admin'}">操作员</th>
                            <th lay-data="{field:'to_aff', width: 280 }">用户aff</th>
                            <th lay-data="{field:'score'}">分数</th>
                            <th lay-data="{field:'score_type_str'}">分数类型</th>
                            <th lay-data="{field:'type_str'}">类型</th>
                            <th lay-data="{field:'des'}">备注</th>
                            <th lay-data="{field:'ip'}">IP</th>
                            <th lay-data="{field:'addtime'}">添加时间</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                上分
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
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
                <label class="layui-form-label">用户aff：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户aff" name="to_aff" value="{{=d.to_aff }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">分数：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="分数" name="score" value="{{=d.score }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">分数类型：</label>
                <div class="layui-input-inline">
                    <select name="score_type">
                        {%html_options options=ChargeAdminModel::SCORE_TYPE%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">类型：</label>
                <div class="layui-input-inline">
                    <select name="type">
                        {%html_options options=ChargeAdminModel::TYPE%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">备注：</label>
            <div class="layui-input-block">
                <textarea lay-verify="required" name="des" placeholder="请输入上下分理由" class=layui-textarea></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">用户日志说明：</label>
            <div class="layui-input-block">
                <textarea lay-verify="required" name="logDesc" placeholder="请输入，用户金币的日志说明，用户会看到" class=layui-textarea></textarea>
            </div>
        </div>


        <div class="layui-form-item layui-hide">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}


        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {
                case 'add':
                case 'sub':
                    lazy('#user-edit-dialog')
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
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

        form.verify(verify);
    })
</script>