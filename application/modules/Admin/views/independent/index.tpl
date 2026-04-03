{%include file="header.tpl"%}
<style>
    .btn-row {
        display: flex;
        gap: 6px;
        margin-bottom: 6px;
    }
    /* 标题和分类字段样式 */
    .title-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
        max-width: 100%;
    }
    
    .title-cell {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-all;
        flex: 1;
        min-width: 0;
        line-height: 1.4;
        text-decoration: none;
    }
    
    .title-cell:hover {
        color: #1E9FFF !important;
        text-decoration: underline;
    }
    /* 标题列单元格样式 - 支持换行和flex布局 */
    .layui-table td[data-field="aa"],
    .layui-table td[data-field="title"] {
        white-space: normal !important;
        word-wrap: break-word;
    }
    /* 状态标签样式 */
    .status-label {
        font-size: 12px;
        color: #FF5722;
        flex-shrink: 0;
        white-space: nowrap;
        line-height: 1.4;
        align-self: flex-start;
    }
    .status-label.waiting {
        color: #FF5722;
    }
    .status-label.draft {
        color: #999;
    }
    .status-label.removed {
        color: #F44336;
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
                            <label class="layui-form-label">cid</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[cid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">作者名字</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[author_name]" placeholder="请输入"
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
                                    {%html_options selected=data_get($get,'where.status') options=ContentsModel::STATUS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">发布时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created"%}
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:50, limits:[10,20,30,40,50,60,70,80,90,100], id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox', width: 50}"></th>
                            <th lay-data="{field:'cid',width: 80}">cid</th>
                            <th lay-data="{field:'aa' ,templet:'#a5', width: 240}">标题</th>
                            <th lay-data="{field:'slug', width: 120}">缩略名</th>
                            <th lay-data="{field:'author_name',width: 120}">作者名字</th>
                            <th lay-data="{field:'home_top', width: 140, templet:'#home_top_tpl',sort: true}">排序</th>
                            <th lay-data="{field:'type',width: 140 ,templet:'#a1',sort: true}">发布时间</th>
                            <th lay-data="{field:'view',width: 90, edit:true}">浏览量</th>
                            <th lay-data="{field:'title',width: 100 ,templet:'#a3'}">属性</th>
                            <th lay-data="{field:'type',width: 110 ,templet:'#a2'}">状态</th>
                            <th lay-data="{field:'modified',width: 130}">更新时间</th>
                            <th lay-data="{fixed: 'right',width: 180 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <!-- 标题模板 - 支持完整显示和自动换行 -->
                    <script type="text/html" id="a5">
                        <div class="title-wrapper">
                            <a href="{{=d.url}}" target="_blank" class="title-cell" style="color: #467B96; cursor: pointer;">{{=d.title}}</a>
                            {{# if(d.status == 'waiting') { }}
                            <span class="status-label waiting">待审核</span>
                            {{# } else if(d.status == 'draft') { }}
                            <span class="status-label draft">草稿</span>
                            {{# } else if(d.status == 'removed') { }}
                            <span class="status-label removed">已下架</span>
                            {{# } }}
                        </div>
                    </script>
                  
                   <script type="text/html" id="a3">
                        <div style="margin: 0; line-height: 1.3;">
                        {{# if(d.allowComment) { }}评论{{# } }}
                        {{# if(d.allowFeed) { }}聚合{{# } }}
                        {{# if(d.allowPing) { }}引用{{# } }}
                        </div>
                    </script>
                    <script type="text/html" id="a2">
                        <div style="margin: 0; line-height: 1.3;">
                        {{d.type_str}}<br>
                        {{d.status_str}}<br>
                        {{# if(d.is_slice == 1) { }}已切片{{# } }}
                        </div>
                    </script>
                    <script type="text/html" id="a1">
                        <div style="margin: 0; line-height: 1.3;">
                            {{# if(d.published_at) { }}
                            {{d.published_at}}
                            {{# } else { }}
                            {{d.created}}
                            {{# } }}
                        </div>
                    </script>
                    <script type="text/html" id="home_top_tpl">
                        <span class="editable-text" data-field="home_top" data-id="{{d.cid}}" style="cursor: pointer; color: #1E9FFF;">{{d.home_top || 0}}</span>
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="cid">批量下架
                            </button> 
                            <button class="layui-btn layui-btn-sm" lay-event="updateStatus" data-pk="cid">状态批量设置</button>
                            
                            <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="deleteArticles" data-pk="cid">批量删除</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <div class="btn-row">
                                <button class="layui-btn layui-btn-xs" lay-event="edit">修改</button>
                                {{# if(d.status == 'publish') { }}
                                <button class="layui-btn layui-btn-xs layui-btn-danger" data-pk="{{=d.cid}}" lay-event="removed">下架</button>
                                {{# } else { }}
                                <button class="layui-btn layui-btn-xs layui-btn-normal" data-pk="{{=d.cid}}" lay-event="publish">公开</button>
                                {{# } }}
                            </div>
                        </div>
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" class="data-dialog" id="setType">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <input type="hidden" name="cid" value="{{=d.cid}}">
        <div class="layui-form-item">
            <label class="layui-form-label">类型:</label>
            <div class="layui-input-block">
                <select name="type" id="">
                    {%html_options options=ContentsModel::TYPE%}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">短剧/大事件ID:</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="短剧/大事件ID" name="sid" class="layui-input">
            </div>
        </div>
    </form>
</script>

<script type="text/html" class="data-dialog" id="batchUpdateStatus">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">状态:</label>
            <div class="layui-input-block">
                <select name="status" id="batch-update-status">
                    {%html_options options=ContentsModel::STATUS%}
                </select>
            </div>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    var themes = eval({%$theme_json%});
    var gis_reload = false;
    window.reload_test = function (is_reload){
        gis_reload = is_reload
    }
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

                table.on('tool(table-toolbar)', function (obj) {
                    //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                    var data = obj.data,
                        layEvent = obj.event,
                        that = this;
                    switch (layEvent) {
                        case 'removed':
                            layer.confirm('确定下架该内容吗?', function(index) {
                                layer.close(index);
                                updateStatus([data.cid], 'removed', '下架',table);
                            });
                            break;
                            
                        // 处理上架操作    
                        case 'publish':
                            layer.confirm('确定上架该内容吗?', function(index) {
                                layer.close(index);
                                updateStatus([data.cid], 'publish', '上架',table);
                            });
                            break;

                        case 'app_hide':
                            layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('app_hide')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'clear_cache':
                            layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('clear_by_id')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'web_show':
                            layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('web_show')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'edit':
                                layer.open({
                                type: 2,
                                content:'{%url('txt')%}?id='+data.cid,
                                area:['100%','100%'],
                                title:false,
                                closeBtn:0,
                                end:function() {
                                    if (gis_reload) {
                                        table.reload('test');
                                    }
                                }
                            });
                            break;
                        case 'setType':
                            lazy('#setType')
                                .offset('auto')
                                .data(data)
                                .title('类型设置')
                                .area(['800px', '450px'])
                                .dialog(function (id, ele) {
                                    let type = $('select[name=type]').val();
                                    let cid = $('input[name=cid]').val();
                                    let sid = $('input[name=sid]').val();
                                    $.post("{%url('setType')%}", {"cid" : cid, "type" : type, "sid" : sid})
                                        .then(function (json) {
                                            layer.close(id);
                                            if (json.code) {
                                                Util.msgErr(json.msg);
                                            } else {
                                                Util.msgOk(json.msg);
                                                table.reload('test')
                                            }
                                        })
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                });
                            break;

                            case 'toggle_home':
                                const currentStatus = obj.data.is_home; 
                                const newHomeStatus = currentStatus == 1 ? 0 : 1;
                                
                                layer.confirm('确认切换显示状态吗?', function(index) {
                                    layer.close(index);
                                    $.post("{%url('batchSetHome')%}", {
                                        "cid": obj.data.cid,
                                        "is_home": newHomeStatus
                                    }).then(function(json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test');
                                        }
                                    });
                                });
                                break;
                                 case 'setHot':
                                    layer.confirm('确定将该内容设为热搜吗？', function (index) {
                                        layer.close(index);
                                        $.post("{%url('setHotSearch')%}", { cid: data.cid })
                                            .then(function (res) {
                                                if (res.code === 0) {
                                                    layer.msg('设置成功');

                                                    const tableId = 'test';
                                                    const idx = $(obj.tr).attr('data-index');       // current row index
                                                    const next = Number(obj.data.hotSearch) === 1 ? 0 : 1;
                                                    obj.data.hotSearch = next;
                                                    const row = table.cache[tableId] && table.cache[tableId][idx];
                                                    if (row) row.hotSearch = next;
                                                    refreshToolbarCell(obj);
                                                } else {
                                                    layer.msg(res.msg || '设置失败');
                                                }
                                            });
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
                         layer.open({
                                type: 2,
                                content:'{%url('add_txt')%}',
                                area:['100%','100%'],
                                title:false,
                                closeBtn:0,
                                end:function() {
                                    if (gis_reload) {
                                        table.reload('test');
                                    }
                                }
                            });
                            break;
                        break;
                    case 'delSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择内容');
                        }
                        layer.confirm('确定要批量下架选中的内容吗?', function (index) {
                            layer.close(index);
                            updateStatus(pkValAry, 'removed', '批量下架', table);
                        });
                        break;

                    case 'updateStatus':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        lazy('#batchUpdateStatus')
                            .offset('auto')
                            .data(data)
                            .title('批量设置状态')
                            .area(['800px', '450px'])
                            .dialog(function (id, ele) {
                                let status = $('select[name=status]').val();
                                $.post("{%url('batchSetStatus')%}", {"pks_" : pkValAry.join(','), "status" : status})
                                    .then(function (json) {
                                        layer.close(id);
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test')
                                        }
                                    })
                            })
                            .laytpl(function () {
                                xx.renderSelect(data, $, form);
                            });
                        break;
                    case 'updateListCache':
                          layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('updateHomeCache')%}")
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                        break;

                         case 'deleteArticles':
                            if (pkValAry.length === 0) {
                                return Util.msgErr('请先选择内容');
                            }
                            layer.confirm('确定要永久删除选中的文章吗？该操作不可恢复！', function (index) {
                                layer.close(index);
                                $.post("{%url('deleteArticles')%}", {
                                    cids: pkValAry.join(',')
                                }).then(function (res) {
                                    if (res.code === 0) {
                                        Util.msgOk('删除成功');
                                        table.reload('test');
                                    } else {
                                        Util.msgErr(res.msg || '删除失败');
                                    }
                                }).fail(function () {
                                    Util.msgErr('请求失败，请重试');
                                });
                            });
                            break;
                }
            });
                $(document).on('click', '.editable-text', function(e) {
                    e.stopPropagation(); 
                    const $span = $(this);
                    const currentValue = $span.text();
                    const field = $span.data('field');
                    const id = $span.data('id');
                    
                    // Create container div
                    const $container = $('<div class="edit-container" style="display:inline-block;"></div>');
                    const $input = $('<input type="text" class="layui-input" style="width:60px;display:inline-block">');
                    const $btn = $('<button class="layui-btn layui-btn-xs layui-btn-normal" style="margin-left:5px">确定</button>');
                    
                    $input.val(currentValue);
                    $container.append($input).append($btn);
                    $span.hide().after($container);
                    
                    $input.focus();
                    
                    $(document).one('click', function(e) {
                        if (!$(e.target).closest('.edit-container').length) {
                            $span.show();
                            $container.remove();
                        }
                    });
                    
                    $btn.on('click', function(e) {
                        e.stopPropagation(); 
                        const newValue = $input.val();
                        
                        $.post("{%url('setHomeTop')%}", {
                            cid: id,
                            home_top: newValue
                        }).then(function(res) {
                            if(res.code === 0) {
                                $span.text(newValue).show();
                                $container.remove();
                                layer.msg('更新成功');
                                table.reload('test');
                            } else {
                                layer.msg(res.msg || '更新失败');
                            }
                        });
                    });
                    
                    $input.on('keyup', function(e) {
                        if(e.keyCode === 13) {
                            $btn.click();
                        }
                    });
            });
            table.on('edit(table-toolbar)', function (obj) {
                let data = {'_pk': obj.data['cid']}
                    data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
            });

            table.on('sort(table-toolbar)', function(obj){
                let orderBy = {};
                orderBy[obj.field] = obj.type;
                tableIns.reload({
                    orderBy: orderBy
                });
                return false;
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
                let fields = data.field;
                let where = {}, like = {}, orderBy = {};
                let categoryId = null;
                // 处理表单字段
                for (let k in fields) {
                    if (!fields.hasOwnProperty(k)) continue;
                    let val = (fields[k] || '').trim();
                    // if (!val) continue;

                    // 处理时间字段
                    if (k.endsWith('Time') && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
                        val += ' 00:00:00';
                    }

                    // 处理嵌套字段名
                    if (k.startsWith('where[')) {
                        // 处理 where[status], where[type] 等
                        let match = k.match(/^where\[(.+?)\]$/);
                        if (match && match[1]) {
                            where[match[1]] = val;
                        }
                    } else if (k.startsWith('like[')) {
                        // 处理 like[title], like[author_name] 等
                        let match = k.match(/^like\[(.+?)\]$/);
                        if (match && match[1]) {
                            like[match[1]] = val;
                        }
                    } else if (k.startsWith('orderBy[')) {
                        // 处理 orderBy[fake_view], orderBy[view] 等
                        let match = k.match(/^orderBy\[(.+?)\]$/);
                        if (match && match[1]) {
                            orderBy[match[1]] = val;
                        }
                    } else if (k === 'category_id') {
                        // 处理分类搜索 - 作为顶级参数
                        categoryId = val;
                    } else {
                        // 处理其他字段
                        where[k] = val;
                    }
                }

                console.log('搜索参数:', { where, like, orderBy, categoryId });

                table.reload('test', {
                    where: {
                        where: where,
                        like: like,
                        orderBy: orderBy,
                        category_id: categoryId
                    },
                    page: { curr: 1 }
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

     function updateStatus(cids, status, actionText, table) {
            if (!Array.isArray(cids)) {
                cids = [cids];
            }
            
            $.post("{%url('updateStatus')%}", {
                cids: cids,
                status: status
            }).then(function(res) {
                if(res.code === 0) {
                    layer.msg(actionText + '成功');
                    table.reload('test');
                } else {
                    layer.msg(res.msg || actionText + '失败');
                }
            }).fail(function() {
                layer.msg(actionText + '请求失败，请重试');
            });
    }

    function refreshToolbarCell(obj) {
        var tpl = $('#operate-toolbar').html();
        var html = layui.laytpl(tpl).render(obj.data);
        var $tr = $(obj.tr);
        var idx = $tr.attr('data-index');
        var $view = $tr.closest('.layui-table-view');

        // main table row
        $view.find('.layui-table-body tr[data-index="'+idx+'"] .operate-toolbar')
            .closest('.layui-table-cell').html(html);

        // fixed-right cloned row
        $view.find('.layui-table-fixed-r tr[data-index="'+idx+'"] .operate-toolbar')
            .closest('.layui-table-cell').html(html);
    }


</script>

<script src="https://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
