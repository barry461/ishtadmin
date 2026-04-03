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
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=FaceMaterialModel::STATUS_TIPS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-block">
                                <select name="orderBy[sort]" id="">
                                    <option value="">全部</option>
                                    <option value="asc">顺序</option>
                                    <option value="desc">倒序</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">使用数</label>
                            <div class="layui-input-block">
                                <select name="orderBy[used_ct]" id="">
                                    <option value="">全部</option>
                                    <option value="asc">顺序</option>
                                    <option value="desc">倒序</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">创建</label>
                            <div class="layui-input-block">
                                <select name="orderBy[created_at]" id="">
                                    <option value="">全部</option>
                                    <option value="asc">顺序</option>
                                    <option value="desc">倒序</option>
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
                            <th lay-data="{field:'id',minWidth:80}">id</th>
                            <th lay-data="{field:'aff',minWidth:259,templet:'#attrxx'}">用户</th>
                            <th lay-data="{field:'coins',minWidth:90,align:'center'}">金币</th>
                            <th lay-data="{templet:'#photolist',minWidth:100}">图片</th>
                            <th lay-data="{templet:'#attr-x1',minWidth: 180,align:'left'}">属性</th>
                            <th lay-data="{field:'sort',minWidth:80,align:'center',edit:true,sort:true}">排序</th>
                            <th lay-data="{field:'status_str',minWidth:90,align:'center'}">状态</th>
                            <th lay-data="{field:'created_at',minWidth:173,templet:'#attr2'}">日期</th>
                            <th lay-data="{fixed: 'right',minWidth: 199 ,align:'center', toolbar: '#operate-toolbar'}">
                                操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="attrxx">
                        {{# if(d.has_member) { }}
                        <div style="display: flex;flex-direction: row;align-items:center;">
                            <img style="width: 50px;height:50px;margin-right: 10px;background:#ccc;" title="头像"
                                 alt="头像" src="{{d.member_thumb}}" onclick="show_img('{{d.member_thumb}}')">
                            <div>
                                会员AFF：{{d.member_aff}} <br>
                                会员昵称：{{d.member_nickname}} <br>
                                会员账户：{{d.member_username}} <br>
                                会员角色：{{d.member_role_str}} <br>
                                会员等级：<span
                                        style="{{=(d.member_vip_str?'color: red':'')}}">{{d.member_vip_str}}</span><br>
                                会员到期：{{d.member_expired_at}}<br>
                            </div>
                        </div>
                        {{# } else { }}
                        官方素材
                        {{# } }}
                    </script>
                    <script type="text/html" id="attr-x1">
                        标题&nbsp;:&nbsp;{{d.title}}<br>
                        尺寸&nbsp;:&nbsp;{{d.size_str}}<br>
                        标识&nbsp;:&nbsp;{{d.p_id}}<br>
                        已用&nbsp;:&nbsp;{{d.used_ct}}次<br>
                        已用&nbsp;:&nbsp;{{d.used_fct}}次(假)<br>
                        周用&nbsp;:&nbsp;{{d.used_week_ct}}次<br>
                    </script>
                    <script type="text/html" id="attr2">
                        上：{{d.up_at}}<br>
                        创：{{d.created_at}}<br>
                        更：{{d.updated_at}}<br>
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
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


<script type="text/html" id="photolist">
    <div style="line-height: normal">
        <img style="display: inline-block;max-width: 52px;margin-bottom: 3px;max-height: 52px"
             onclick="clickShowImage(this)"
             src="{{=d.thumb}}">
    </div>
</script>

<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">aff：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="aff" name="aff" value="{{d.aff || 0 }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">图片：</label>
                <div class="layui-input-inline">
                    <span id="img-0">{%html_upload name='thumb' src='thumb' value='thumb'%}</span>
                    <input type="hidden" name="thumb_w" value="{{=d.thumb_w}}">
                    <input type="hidden" name="thumb_h" value="{{=d.thumb_h}}">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">标题：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="标题" name="title" value="{{=d.title }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">标识：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="标识" name="p_id" value="{{d.p_id || '' }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">周使用数：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="周使用数" name="used_week_ct" value="{{d.used_week_ct || 0 }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">热门：</label>
                <div class="layui-input-inline">
                    <select name="is_hot" data-value="{{# if(d.is_hot == undefined){ }}1{{# }else{ }}{{d.is_hot}}{{# } }}">
                        {%html_options options=FaceMaterialModel::HOT_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">排序：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="排序" name="sort" value="{{d.sort || 0 }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">使用数：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="使用数" name="used_ct" value="{{d.used_ct || 0 }}"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">假使用数：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="假使用数" name="used_fct" value="{{d.used_fct || 0 }}"
                           class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{# if(d.status == undefined){ }}1{{# }else{ }}{{d.status}}{{# } }}">
                        {%html_options options=FaceMaterialModel::STATUS_TIPS%}
                    </select>
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">金币：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="金币" name="coins" value="{{d.coins || 0 }}"
                           class="layui-input">
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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

            table.on('edit(table-toolbar)', function (obj) {
                let data = {'_pk': obj.data['id']};
                data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
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
                        .width(`${document.body.clientWidth-300}px`)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            $('#img-0 img').on('load', function () {
                                $('input[name="thumb_w"]').val(this.naturalWidth)
                                $('input[name="thumb_h"]').val(this.naturalHeight)
                            });
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
                        .width(`${document.body.clientWidth-300}px`)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            $('#img-0 img').on('load', function () {
                                $('input[name="thumb_w"]').val(this.naturalWidth)
                                $('input[name="thumb_h"]').val(this.naturalHeight)
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