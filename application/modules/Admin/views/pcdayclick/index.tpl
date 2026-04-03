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
                <div class="layui-card-header">管理
                    <span style="color: orangered" class="layui-inline" id="total-id">
                        <p style="font-size: medium;font-weight: bold!important;"></p>
                    </span>
                </div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入ID"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">日期</label>
                            <div class="layui-input-block">
                                {%html_between name="date"%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-block">
                                <select name="where[type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PcDayClickModel::TYPE_TIPS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">统计ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[record_id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">统计位置</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[record_position]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">统计名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[record_name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">跳转地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[record_url]" placeholder="请输入"
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:90,limits:[10,20,30,40,50,60,70,80,90,100,1000],id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',minWidth:80}">id</th>
                            <th lay-data="{field:'date',minWidth:102,align:'center'}">日期</th>
                            <th lay-data="{field:'type_str',minWidth:102,align:'center'}">统计类型</th>
                            <th lay-data="{field:'record_id',minWidth:102,align:'center'}">统计ID</th>
                            <th lay-data="{field:'record_position',minWidth:164,align:'center'}">统计位置</th>
                            <th lay-data="{templet:'#photolist',minWidth:120}">统计图标</th>
                            <th lay-data="{field:'record_name',minWidth:102,align:'center'}">统计名称</th>
                            <th lay-data="{field:'click_num',minWidth:102,align:'center',sort:true}">点击量</th>
                            <th lay-data="{field:'record_url',minWidth:290,align:'left',sort:true}">跳转地址</th>
                            <th lay-data="{field:'created_at',minWidth:157,align:'center'}">操作时间</th>
                        </tr>
                        </thead>
                        <script type="text/html" id="photolist">
                            <div style="line-height: normal">
                                <img style="display: inline-block;width: 25px;height: 25px;margin-bottom: 3px;" onclick="clickShowImage(this)"
                                     src="{{d.record_thumb||''}}">
                            </div>
                        </script>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{%include file="fooler.tpl"%}
<script src="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui-xtree.js"></script>
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {
        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {

            }
        })

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {

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
                        table.reload('test');
                    } else {
                        //修改
                        obj.update(json.data);
                        let index = $(obj.tr).data('index')
                        table.cache['test'][index] = json.data;
                        Util.msgOk(json.msg);
                        table.reload('test');
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

        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>