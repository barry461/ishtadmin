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
                                <input type="text" name="where[id]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">位置</label>
                            <div class="layui-input-block">
                                <select name="where[pos]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=NoticeModel::POS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">公告类型</label>
                            <div class="layui-input-block">
                                <select name="where[type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=NoticeModel::TYPE%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">跳转地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[url]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=NoticeModel::STATUS%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',defaultToolbar:[{title:'选显',layEvent:'selected_view',icon:'layui-icon-circle'},'filter','print','exports'],limit:1000,limits:[10,20,30,40,50,60,70,80,90,1000],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width:'6%',align:'center'}">id</th>
                            <th lay-data="{field:'title',width:'10%'}">标题</th>
                            <th lay-data="{field:'pos_str',width:'8%',align:'center'}">位置</th>
                            <th lay-data="{field:'sort',width:'8%',align:'center',edit:true,sort:true}">排序</th>
                            <th lay-data="{field:'content',width:'8%',align:'center'}">内容</th>
                            <th lay-data="{templet:'#photolist',width:'13%'}">图片</th>
                            <th lay-data="{field:'type_str',width:'8%',align:'center'}">公告类型</th>
                            <th lay-data="{field:'url',width:'13%'}">跳转地址</th>
                            <th lay-data="{field:'clicked',width:'8%',align:'center','sort':true}">点击量</th>
                            <th lay-data="{field:'status_str',width:'8%',align:'center'}">状态</th>
                            <th lay-data="{field:'created_at',width:'15%',templet:'#show-time'}">投放时间</th>
                            <th lay-data="{field:'created_at',width:'14.5%',templet:'#attr2'}">创建时间</th>
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
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="replace" data-pk="id">替换</button>
                        </div>
                    </script>
                    <script type="text/html" id="show-time">
                        开始：{{d.start_at}}<br>
                        结束：{{d.end_at}}
                    </script>
                    <script type="text/html" id="attr2">
                        创：{{d.created_at}}<br>
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
<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">标题</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="标题" name="title" value="{{=d.title }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片</label>
                <div class="layui-input-inline">
                    {%html_upload name='img_url' src='img_url' value='img_url'%}
                </div>
                <div class="size_tip">610 X 680</div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片宽</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" name="width" value="{{d.width||305 }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">图片高</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" name="height" value="{{d.height||340 }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">投放开始：</label>
                <div class="layui-input-inline">
                    <input placeholder="投放开始时间" name="start_at" value="{{=d.start_at }}"
                           class="layui-input x-date-time">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">投放结束：</label>
                <div class="layui-input-inline">
                    <input placeholder="投放结束时间" name="end_at" value="{{=d.end_at }}"
                           class="layui-input x-date-time">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">内容</label>
            <div class="layui-input-block">
                <textarea name="content" class="layui-textarea">{{=d.content }}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-inline">
                    <select name="type" data-value="{{=d.type }}">
                        {%html_options options=NoticeModel::TYPE%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">路由</label>
                <div class="layui-input-inline">
                    <select name="router" data-value="{{=d.router}}" lay-filter="router_list">
                        {%html_options options=FlutterRouterModel::router_list()%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跳转地址</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="url" name="url" value="{{=d.url }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{=d.status }}">
                        {%html_options options=NoticeModel::STATUS%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">位置</label>
                <div class="layui-input-inline">
                    <select name="pos" data-value="{{=d.pos }}">
                        {%html_options options=NoticeModel::POS%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">可见状态</label>
                <div class="layui-input-inline">
                    <select name="visible_type" data-value="{{=d.visible_type }}">
                        {%html_options options=NoticeModel::VISIBLE_TYPE%}
                    </select>
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-inline">
                    <input name="sort" value="{{d.sort||0 }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
                <p>1. 外部跳转，直接填写跳转的url；</p>
                <p>2. 内部跳转。需要选择对应的路由，然后在跳转目标，填写跳转的参数 <br>
                    假设路由是 app/:id/:type，我们需要跳转到 id=123,type为222的路径，那么目标应该填写 <span style="color: red">/:123/:111</span>
                    <br>
                    如果参考路由只有一个参数，可以省略 <span style="color: red">/:</span>
                </p>
                <p>3. 当前参考参数：<span id="shili" style="color: red;"></span></p>
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
    var fl = false
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

            form.on('select(router_list)', function (data) {
                let pos = data.value.indexOf("/:")
                if (pos !== -1) {
                    $('#shili').html(data.value.substr(pos));
                } else {
                    $('#shili').html('当前路由没有参数');
                }
            });

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