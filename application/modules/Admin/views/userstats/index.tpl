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
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">用户发帖统计</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">用户昵称</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[screenName]" placeholder="请输入昵称"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">发帖时间</label>
                            <div class="layui-input-block">
                                {%html_between name='created'%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:50, limits:[10,20,30,40,50,100], id:'test'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'uid',width:80}">用户ID</th>
                            <th lay-data="{field:'name',width:150}">用户名</th>
                            <th lay-data="{field:'screenName',width:150}">昵称</th>
                            <th lay-data="{field:'post_count',width:100}">发帖数</th>
                            <th lay-data="{field:'approved_comments',width:120}">评论通过数</th>
                            <th lay-data="{field:'total_comments',width:120}">真实评论数</th>
                            <th lay-data="{field:'pass_rate',width:100}">通过率</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'form', 'laydate', 'jquery'],
        function (table, form, layDate, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;

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
        })
</script>
</body>
</html>

