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
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">内容id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[cid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">用户aff</label>
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
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options options=UserContentsModel::STATUS selected=UserContentsModel::STATUS_WAIT%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',where:{'where[status]':0},limit:90,limits:[10,20,30,40,50,60,70,80,90,100,1000],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'cid'}">cid</th>
{%*                            <th lay-data="{field:'category_id'}">分类</th>*%}
                            <th lay-data="{field:'category_id',width: 100  , templet:'#a1'}">封面</th>
                            <th lay-data="{field:'aff'}">用户aff</th>
                            <th lay-data="{field:'nickname'}">用户昵称</th>
                            <th lay-data="{field:'title'}">title</th>
                            <th lay-data="{field:'body'}">内容</th>
                            <th lay-data="{field:'tags_str'}">标签</th>
                            <th lay-data="{field:'status_str'}">状态</th>
                            <th lay-data="{field:'income'}">受益</th>
                            <th lay-data="{field:'created_at'}">时间</th>
                            <th lay-data="{field:'admin_str'}">审核管理员</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="a1">
                    <img src="{{d.cover_url}}" onclick="show_img('{{d.cover_url}}')" style="width: 60px;height: 60px" />
</script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="rejectSelect"
                                    data-pk="id">拒绝所选
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a data-pk="{{=d.id}}" lay-event="edit">编辑</a> <br>
                            <a data-pk="{{=d.id}}" lay-event="preview">预览</a> <br>
                            <a data-pk="{{=d.id}}" data-status="pass" lay-event="pass">审核通过</a> <br>

                            <a data-pk="{{=d.id}}" lay-event="reject">审核拒绝</a><br>
                            {{# if(d.status == 3){ }}
                                <a data-pk="{{=d.id}}" lay-event="slice">切片重试</a>
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
            <label class="layui-form-label">分类：</label>
            <div class="layui-input-block">
                {{#  layui.each(cates, function(index, item){ }}
                <input type="checkbox" name="category_ids[]" lay-skin="primary" title="{{item.name}}" value="{{item.mid}}"
                        {{# if ((d.category_ids ? d.category_ids:[]).indexOf(item.mid)!=-1) { }}
                       checked=""
                       {{#  } }}
                >
                {{#  }); }}
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">作者：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="用户aff" name="aff"
                       value="{{=d.aff }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">标题：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="title" name="title"
                       value="{{=d.title }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">文章封面：</label>
                <div class="layui-input-inline">
                    {%html_upload name='cover' src='cover_url' value='cover'%}
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">内容：</label>
            <div class="layui-input-block">
                <textarea class="layui-textarea" name="body" rows="30">{{d.body||'这里是内容'}}</textarea>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">标签：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="标签" name="tags"
                       value="{{=d.tags_str }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

<script type="text/html" class="data-dialog" id="batch-reject-post">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">理由</label>
            <div class="layui-input-block">
                <select name="review_reason">
                    {%html_options options=$rejectAry2%}
                </select>
            </div>
        </div>
    </form>
</script>

<script type="text/html" class="data-dialog" id="pass-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>发布投稿</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">


        <div class="layui-form-item">
            <label class="layui-form-label">分类：</label>
            <div class="layui-input-block">
                {{#  layui.each(cates, function(index, item){ }}
                <input type="checkbox" name="category_ids[]" lay-skin="primary" title="{{item.name}}" value="{{item.mid}}"
                        {{# if ((d.category_ids ? d.category_ids:[]).indexOf(item.mid)!=-1) { }}
                       checked=""
                       {{#  } }}
                >
                {{#  }); }}
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">文章作者：</label>
            <div class="layui-input-block">
                <select name="author_id" data-value="{%$fbmrid%}">
                    {%html_options options=$users%}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">文章标题：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="文章标题" name="title" value="{{=d.title }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">标签：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="标签" name="tags"
                       value="{{=d.tags_str }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">稿费：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="稿费" name="coin" value="0" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">发布状态：</label>
            <div class="layui-input-block">
                <input type="radio" name="status" value="publish" title="发布" >
                <input type="radio" name="status" value="waiting" title="草稿" checked="">
            </div>
        </div>


        <div class="layui-form-item layui-hide">
            <input type="hidden" name="id" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>
<script type="text/html" id="jjly">
    <div class="layui-form refuse-container" action="" lay-filter="reject">
        <div class="layui-form-item refuse-item-box" pane="">
            <div class="layui-input-block">
                {%foreach $rejectAry as $item%}
                    <input type="radio" name="reject_reason" value="{%$item%}" title="{%$item%}"><br>
                {%/foreach%}
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="preview">
    <iframe src="{%url('preview')%}?id={{=d.id}}"></iframe>
</script>
{%include file="fooler.tpl"%}
<script>
    var cates = eval({%$cates%});

    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

            table.on('tool(table-toolbar)', function (obj) {
                //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                var data = obj.data,
                    layEvent = obj.event,
                    that = this,
                    id = $(this).data('pk');
                switch (layEvent) {
                    case 'reject':
                        xx.renderSelect(data, $, form);
                        layer.open({
                            type: 1
                            , title: "拒绝理由"
                            , content: $('#jjly').html()
                            , btn: ['确认', '取消']
                            , area: ['900px', '500px']
                            , yes: function (index, layero) {
                                let refuse = form.val('reject');
                                console.log({"id": id, 'reject_reason': refuse.reject_reason})
                                layer.close(index);
                                $.post("{%url('reject')%}", {"id": id, 'reason': refuse.reject_reason})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test')
                                        }
                                    })
                            }
                        });
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
                            .area([1100 + 'px', document.body.offsetHeight + 'px'])
                            .dialog(function (id, ele) {
                                dialogCallback(id, ele, obj)
                            })
                            .laytpl(function () {
                                xx.renderSelect(data, $, form);
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                                form.render()
                            });
                        break;
                    case 'preview':
                        lazy('#preview').width(780).iframe('{%url('preview')%}?id='+id).start();
                        break;
                    case 'slice':
                        layer.confirm('确认再次切片?', function (index) {
                            layer.close(index);
                            $.post("{%url('slice')%}", {"_pk": $(that).data('pk')})
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
                    case 'pass':
                        lazy('#pass-dialog')
                            .data(data)
                            .area([1100 + 'px', (document.body.offsetHeight *0.7) + 'px'])
                            .dialog(function (id, ele) {
                                let from = $(ele).find('form')
                                $.post("{%url('pass')%}", from.serializeArray())
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
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                                form.render()
                            });
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
                    case 'rejectSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        lazy('#batch-reject-post')
                            .data(data)
                            .offset('auto')
                            .title('审核所选')
                            .area(['1000px', '600px'])
                            .dialog(function (id, ele) {
                                let reason = $('select[name=review_reason]').val()
                                let data = {'ids':pkValAry.join(','),'reason':reason};
                                $.post("{%url('batch_reject')%}", data)
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test');
                                        }
                                    })
                                layer.close(id);
                                table.reload('test');
                            }).laytpl(function () {
                                xx.renderSelect(data, $, form);
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
