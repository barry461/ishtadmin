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
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[pname]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">产品状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=ProductModel::STATUS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-block">
                                <select name="where[type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=ProductModel::GOODS_TYPES%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">vip等级</label>
                            <div class="layui-input-block">
                                <select name="where[vip_level]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=ProductModel::VIP_LEVEL%}
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
                            <th lay-data="{field:'id',width: 80}">id</th>
                            {%*<th lay-data="{field:'type',type:'enum',value:{%json_encode(ProductModel::GOODS_TYPES)%}}">类型</th>*%}
                            <th lay-data="{field:'pname',width: '15.6%',templet:'#att2'}">产品名称</th>
                            {%*<th lay-data="{field:'img_url' , templet:'#img_url',width: 110}">图片1/图片2</th>
                            <th lay-data="{field:'second_img_url' , templet:'#second_img_url',width: 110}">图片2</th>*%}
                            <th lay-data="{field:'price_yuan', templet:'#att1'}">价格</th>

{%*                            <th lay-data="{field:'discount'}">折扣</th>*%}
{%*                            <th lay-data="{field:'promo_expire_time'}">折扣过期时间</th>*%}
                            <th lay-data="{field:'valid_date',width: '14%', templet:'#att3'}">权益</th>

                            {%* <th lay-data="{field:'status',type:'enum',value:{%json_encode(ProductModel::STATUS)%}}">产品状态</th>*%}
                            <th lay-data="{field:'sort_order',edit:true,width: 80}">排序</th>
                            <th lay-data="{field:'show_more_str',width: 80}">卡型</th>
                            <th lay-data="{field:'updated_at',templet:'#time-attr',width: 170}">更新时间</th>
                            {%*<th lay-data="{field:'vip_level',type:'enum',value:{%json_encode(ProductModel::VIP_LEVEL)%}}">vip等级</th>*%}
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>

                    <script type="text/html" id="att3">
                        vip天数：{{d.valid_date}} <br>
                        币：{{d.coins}} <br>
                        赠送币：{{d.free_coins}} <br>
                        金币视频免费看天数：{{d.free_view_day}}
                    </script>

                    <script type="text/html" id="att1">
                        价格: {{d.price_yuan}}<br>
                        推广价: {{d.promo_price_yuan}}<br>
                    </script>

                    <script type="text/html" id="att2">
                        <h2>{{d.pname}}</h2>
                        <p style="white-space: pre-wrap">{{d.description}}</p>
                    </script>
                    <script type="text/html" id="img_url">
                        <img src="{{d.second_img_url}}" onclick="clickShowImage(this)" style="width: 80px;height: 30px">
                        <br>
                        <img src="{{d.img_url}}" onclick="clickShowImage(this)" style="width: 80px;height: 30px">
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a href="javascript:;" style="color: #0a6aa1" lay-event="map" data-id="{{d.id}}">
                            权益管理</a>&nbsp;|
                        <a href="javascript:;" style="color: #0a6aa1" lay-event="payway" data-id="{{d.id}}">
                            支付网关</a>&nbsp;<br>
                        <a href="javascript:;" style="color: #0a6aa1" lay-event="qx" data-id="{{d.id}}">
                            权限管理</a>&nbsp;<br>
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
            <label class="layui-form-label">产品名称</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="产品名称" name="pname" value="{{=d.pname }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">红标赠送提示</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="红标赠送提示" name="give_tip" value="{{=d.give_tip }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片1</label>
                <div class="layui-input-inline">
                    {%html_upload src="img_url" value="img" name="img"%}
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片2</label>
                <div class="layui-input-inline">
                    {%html_upload src="second_img_url" value="second_img" name="second_img"%}
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">价格:单位分</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="价格:单位分" name="price"
                           value="{{=d.price }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">推广价格:单位分</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="推广价格:单位分" name="promo_price"
                           value="{{=d.promo_price }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-inline">
                    <select name="type" data-value="{{d.type}}">
                        {%html_options options=ProductModel::GOODS_TYPES%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">VIP多少天</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="VIP多少天" name="valid_date"
                           value="{{d.valid_date||0 }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">多少币</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="多少币" name="coins"
                           value="{{d.coins||0 }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">赠送多少币</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="赠送多少币" name="free_coins"
                           value="{{d.free_coins||0 }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">可行类型</label>
                <div class="layui-input-inline">
                    <select name="visible_type" data-value="{{d.visible_type}}">
                        {%html_options options=ProductModel::VISIBLE_TYPE%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">产品状态</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{d.status}}">
                        {%html_options options=ProductModel::STATUS%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">金币视频免费天数</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="金币视频免费天数" name="free_view_day"
                           value="{{d.free_view_day||0 }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">vip等级</label>
                <div class="layui-input-inline">
                    <select name="vip_level" id="" data-value="{{d.vip_level}}">
                        {%html_options options=ProductModel::VIP_LEVEL%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">折扣</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="折扣" name="discount"
                           value="{{=d.discount }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">折扣过期时间</label>
                <div class="layui-input-inline">
                    <input placeholder="折扣过期时间" name="promo_expire_time" value="{{=d.promo_expire_time_str }}"
                           class="layui-input x-date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">更多</label>
                <div class="layui-input-inline">
                    <select name="show_more" data-value="{{d.show_more}}">
                        {%html_options options=ProductModel::SHOW_MORE%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="排序" name="sort_order" value="{{=d.sort_order }}"
                           class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">产品描述</label>
            <div class="layui-input-block">
                <textarea name="description" class="layui-textarea" placeholder="产品描述">{{=d.description }}</textarea>
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>
<style>
    .xss > .layui-form-checkbox{ float: left;  width: 140px;  }
</style>

<script type="text/html" class="data-dialog" id="x-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            {%foreach from=$resource_list key=k item=v%}
                <div class="layui-input-block">
                    <div class="layui-form-mid" style="display: block;width: 330px">{%$v%}：</div>
                    <div style="margin-left: 40px;width: 330px">
                        {%foreach from=$privilege_list key=k1 item=v1%}
                        <div class="layui-inline xss">
                            <input type="checkbox" name="privilege[{%$k%}][{%$k1%}][status]" lay-skin="primary" {{=(typeof(data_get(d.privileges,'{%$k%}.{%$k1%}.value')) != "undefined" ? 'checked' :'')}} title="{%$v1%}">
                            <div class="layui-form-mid">数值：</div>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" name="privilege[{%$k%}][{%$k1%}][value]" placeholder="数值" value="{{data_get(d.privileges,'{%$k%}.{%$k1%}.value',0)}}" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid">折扣：最大值100，其他数值。9999无限</div>
                        </div>
                        {%/foreach%}
                    </div>
                </div>
            {%/foreach%}
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="product_id" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {};

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'qx':
                    lazy('#x-dialog')
                        .data(data)
                        .width(900)
                        .dialog(function (id, ele) {
                            let from = $(ele).find('form')
                            $.post("{%url('save_qx')%}", from.serializeArray())
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
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            form.render('checkbox');
                        });
                    break;
                case 'map':
                    lazy('')
                        .iframe('{%url('productrightmap/index')%}?where[product_id]=' + data['id'])
                        .width('1200px')
                        .title('管理权益-' + data.pname)
                        .start(function () {

                        })
                    break;
                case 'payway':
                    lazy('')
                        .iframe('{%url('paymap/index')%}?where[product_id]=' + data['id'])
                        .width('1200px')
                        .title('管理权益-' + data.pname)
                        .start(function () {

                        })
                    break;
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
                            renderDateInput();
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
                            renderDateInput();
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
        function renderDateInput() {
            $('.x-date-time').each(function (key, item) {
                layDate.render({elem: item, 'type': 'datetime'});
            });
            $('.x-date').each(function (key, item) {
                layDate.render({elem: item});
            });
        }

        renderDateInput();
        // //渲染日期
        // $('.x-date-time').each(function (key, item) {
        //     layDate.render({elem: item, 'type': 'datetime'});
        // });
        // $('.x-date').each(function (key, item) {
        //     layDate.render({elem: item});
        // });
        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })
</script>