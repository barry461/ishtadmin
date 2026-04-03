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
    .layui-table td {
        font-size: 12px;
    }
    .layui-table-cell , .operate-toolbar{font-size: 12px;min-height:28px;height: auto;}
    
    /* 评论状态背景色 */
    .status-approved { background-color: #5FB878 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    .status-waiting { background-color: #FFB800 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    .status-spam { background-color: #FF5722 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    .status-filter { background-color: #FF5722 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    
    /* 标题字段样式 - 完全展示支持换行 */
    .title-cell {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-all;
        max-width: 250px;
        line-height: 1.4;
        display: inline-block;
        text-decoration: none;
    }
    
    .title-cell:hover {
        color: #1E9FFF !important;
        text-decoration: underline;
    }
</style>
<style>
.layui-table td {
    font-size: 12px;
    white-space: nowrap;  /* 禁止自动换行 */
    overflow: hidden;
    text-overflow: ellipsis; /* 溢出打点 */
    max-width: 300px;       /* 控制最大宽度 */
}

/* 标题单元格所在的行支持换行 */
.layui-table td .title-cell {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
}

/* 包含标题单元格的表格单元格支持换行 */
.layui-table-cell:has(.title-cell) {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
}

.table-text-preview {
    cursor: pointer;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: block;
}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">评论ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[coid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">文章ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[cid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">文章标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[c_title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">评论aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[app_aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        {%if $showLike %}
                            <div class="layui-inline">
                                <label class="layui-form-label">评论昵称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="like[author]" placeholder="请输入"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>

                            <div class="layui-inline">
                                <label class="layui-form-label">模糊查询</label>
                                <div class="layui-input-block">
                                    <input type="text" name="like[text]" placeholder="请输入"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>

                            <div class="layui-inline">
                                <label class="layui-form-label">精确查询</label>
                                <div class="layui-input-block">
                                    <input type="text" name="where[text]" placeholder="请输入"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                        {%/if%}

                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options selected=data_get($get,'where.status') options=CommentsModel::STATUS_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">置顶</label>
                            <div class="layui-input-block">
                                <select name="where[is_top]">
                                    <option value="">全部</option>
                                    {%html_options selected=data_get($get,'where.is_top') options=CommentsModel::TOP_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">时间</label>
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
                           lay-data="{url:'{%url('listAjax',$get)%}', page:true, id:'test',limit:50,toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'author',width: 100 }">评论昵称</th>
                            <th lay-data="{field:'text_show',width: 250, templet: '#commentContentTpl' }">评论内容</th>
                            <th lay-data="{field:'article_title',width: 250, templet: '#articleTitleTpl' }">评论文章</th>
                            <th lay-data="{field:'time_line',width: 130 }">评论时间</th>
                            <th lay-data="{field:'ip',width: 120 }">评论IP</th>
                            <th lay-data="{field:'status_str',width: 80, templet: '#statusTpl'}">审核状态</th>
                            <th lay-data="{width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作</th>
                            <th lay-data="{field:'admin_str',width: 120 }">审核管理员</th>
                            <th lay-data="{field:'app_aff_str',width: 120 }">用户AFF</th>
                            <th lay-data="{field:'cid',width: 80 }">文章ID</th>
                            <th lay-data="{field:'coid',width: 80 }">评论ID</th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="statusTpl">
                        <span class="{{=d.status_class}}">{{=d.status_str}}</span>
                    </script>
                    
                    <!-- 评论内容模板 - 完整显示支持多行 -->
                    <script type="text/html" id="commentContentTpl">
                        <div style="white-space: pre-wrap; word-break: break-word; line-height: 1.5; max-height: 150px; overflow-y: auto; cursor: pointer;" 
                             class="comment-text-preview" data-text="{{=d.text}}">
                            {{=d.text_show}}
                        </div>
                    </script>
                    
                    <!-- 文章标题模板 - 可点击跳转 -->
                    <script type="text/html" id="articleTitleTpl">
                        {{# if(d.article_title) { }}
                        <a href="{%options('siteUrl')%}/archives/{{=d.cid}}/" target="_blank"
                           class="title-cell" style="color: #467B96;">
                            {{=d.article_title}}
                        </a>
                        {{# } else { }}
                        <span style="color: #ccc;">无标题</span>
                        {{# } }}
                    </script>
                    
                    <script type="text/html" id="a10">
                        {{=d.text_show}}
                    </script>
                    <script type="text/html" id="a3">
                        <div style="display: flex;flex-direction: row;" onclick="row_click(this)">
                            <div style="display: flex;flex-direction: row;flex: 0.2">
                                <div><img src="https://secure.gravatar.com/avatar/?s=40&r=G&d="></div>
                                <div style="width: 170px;padding-left: 5px;">
                                    <div>{{d.author}}</div>
                                    <div style="width: 150px;">
                                        <p style="white-space: pre-wrap;color:#cccccc;word-break:break-word;">{{=d.ip}}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex;flex-direction: row;">
                                    <span style="padding-right: 8px;color:#cccccc;">{{=d.time_line}}</span>
                                    <p style="color: #467B96;">{{=d.contents.title}}</p>
                                </div>
                                <div><p style="white-space: pre-wrap;color:#000;padding: 10px 0">{{=d.text}}</p></div>
                            </div>
                        </div>
{%*                        *%}
{%*                        <hr>*%}
{%*                        <p style="white-space: pre-wrap;color:#000;">{{=d.text}}</p>*%}
                    </script>
                    <script type="text/html" id="commentTpl">
  <span class="table-text-preview" onclick="showCommentDetail('{{=d.text}}')">
    {{= d.text.length > 40 ? d.text.slice(0, 40) + '…' : d.text }}
  </span>
</script>
                    <script type="text/html" id="a4">
                        文章ID:{{=d.cid}} <br>
                        文章标题:{{=d.title_str}} <br>
                    </script>
                    <script type="text/html" id="a1">
                        {{=d.author}} <br>
                        {{=d.ip}} <br>
                    </script>
                    <script type="text/html" id="a2">
                        {{d.cid}} <br>
                        <p style="white-space: pre-wrap;color: #467B96; ">{{=d.contents.title}}</p>
                    </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="pass"
                                    data-pk="coid">通过所选
                            </button>
{%*                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"*%}
{%*                                    data-pk="coid">删除所选*%}
{%*                            </button>*%}
                            <button class="layui-btn layui-btn-sm" lay-event="spamSelect"
                                    data-pk="coid">标记为垃圾
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="filterSelect"
                                    data-pk="coid">标记为过滤
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a data-pk="{{=d.coid}}" lay-event="pass">通过</a> |
                            <a data-pk="{{=d.coid}}" lay-event="spam">垃圾</a> |
                            <a data-pk="{{=d.coid}}" lay-event="delSame">同评删除</a>
                            {{# if(d.status !== 'filter'){ }}
                                | <a data-pk="{{=d.coid}}" lay-event="filter">过滤</a>
                            {{# } }}
                            <br/><hr/>
                            <a data-pk="{{=d.coid}}" lay-event="banIp">封禁IP</a>|
                            <a data-pk="{{=d.coid}}" lay-event="banIpGroup">封禁IP组</a>
                            {{# if(d.is_official == 1){ }}
                                {{# if(d.is_top == 0){ }}
                                    | <a data-id="{{=d.coid}}" lay-event="topSet">置顶</a>
                                {{# } else { }}
                                    | <a data-id="{{=d.coid}}" lay-event="topSet">取消置顶</a>
                                 {{# } }}
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
            <label class="layui-form-label">文章ID：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="文章ID" name="cid"
                       value="{{=d.cid }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">aff(官方账号)：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="aff<官方账号>" name="app_aff"
                       value="{{=d.app_aff }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">内容：</label>
            <div class="layui-input-block">
                <textarea name="text" class="layui-textarea">{{=d.text }}</textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{d.status }}">
                        {%html_options options=CommentsModel::STATUS_TIPS%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">置顶：</label>
                <div class="layui-input-inline">
                    <select name="is_top" data-value="{{d.is_top }}">
                        {%html_options options=CommentsModel::TOP_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.coid}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>


{%include file="fooler.tpl"%}
<script>
    function row_click(){}
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {},
                tool = {  },
                toolbar = {
                    "pass": function (obj, pkValAry, that) {
                        $.post("{%url('pass')%}", {"value": pkValAry.join(',')})
                            .then(function (json) {
                                if (json.code){
                                    Util.msgErr(json.msg);
                                }else{
                                    $('button.layui-btn.layuiadmin-btn-useradmin').click()
                                }
                            })
                    }
                };

            row_click =function (that){
                let tr = $(that).parents('tr');
                if ($(tr).hasClass('layui-table-click')){
                    $(tr).removeClass('layui-table-click');
                }else{
                    $(tr).addClass('layui-table-click');
                }

                $(tr).find('div.layui-form-checkbox').click();
            }

            table.on('tool(table-toolbar)', function (obj) {
                //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                let data = obj.data,
                    layEvent = obj.event,
                    that = this;
                switch (layEvent) {
                    default:
                        if (typeof (tool[layEvent]) !== "undefined") {
                            tool[layEvent](obj, data, that)
                        }
                        break;
                    case 'del':
                        $.post("{%url('del')%}", {"_pk": $(that).data('pk')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    table.reload('test');
                                }
                            })
                        break;
                    case 'spam':
                        $.post("{%url('spam')%}", {"value": $(that).data('pk')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    table.reload('test');
                                }
                            })
                        break;
                    case 'filter':
                        $.post("{%url('filter')%}", {"value": $(that).data('pk')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    table.reload('test');
                                }
                            })
                        break;
                    case 'pass':
                        console.log($(that).data('pk'));
                        toolbar["pass"](obj, [$(that).data('pk')], that)
                        break;
                    case 'delSame':
                        layer.confirm('确定要删除未审核的相同的垃圾评论么?', function (index) {
                            layer.close(index);
                            $.post("{%url('delSame')%}", {"coid": $(that).data('pk')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        obj.del();
                                        table.reload('test')
                                    }
                                })
                        });
                        break;
                    case 'banIp':
                        layer.confirm('确定要封禁IP么?', function (index) {
                            layer.close(index);
                            $.post("{%url('banIp')%}", {"coid": $(that).data('pk')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                    }
                                })
                        });
                        break;
                    case 'banIpGroup':
                        layer.confirm('确定要封禁IP组么?', function (index) {
                            layer.close(index);
                            $.post("{%url('banIpGroup')%}", {"coid": $(that).data('pk')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                    }
                                })
                        });
                        break;
                    case 'topSet':
                        layer.confirm('评论真的要置顶/取消置顶吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('topSet')%}", {"coid": $(that).data('id')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        obj.del();
                                        table.reload('test')
                                    }
                                })
                        });
                        break;
                }
            });

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
                    default:
                        if (typeof (toolbar[layEvent]) !== "undefined") {
                            toolbar[layEvent](obj, pkValAry, this)
                        }
                        break;
                    case 'add':
                        lazy('#user-edit-dialog')
                            .width(`${document.body.clientWidth-300}px`)
                            .dialog(function (id, ele) {
                                dialogCallback(id, ele)
                            })
                            .laytpl(function () {
                                xx.renderSelect({}, $, form);
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
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
                    case 'spamSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        layer.confirm('真的标记为垃圾吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('spam')%}", {"value": pkValAry.join(',')})
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
                    case 'filterSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        layer.confirm('真的标记为过滤吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('filter')%}", {"value": pkValAry.join(',')})
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
                let data = {'_pk': obj.data['coid']}
                data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
            });

            function dialogCallback(id, ele, obj) {
                let from = $(ele).find('form')
                $.post("{%url('save')%}", from.serializeArray())
                    .then(function (json) {
                        if (json.code) {
                            return Util.msgErr(json.msg);
                        }
                        if (typeof (obj) == "undefined") {
                            layer.close(id);
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

            function tableUpdate(obj, json) {
                obj.update(json.data);
                let index = $(obj.tr).data('index')
                table.cache['test'][index] = json.data;
                layer.msg('ok', {time: 400})
            }
            function showCommentDetail(content) {
                layer.open({
                    type: 1,
                    title: '评论详情',
                    area: ['600px', '400px'],
                    shadeClose: true,
                    content: `<div style="padding:20px;white-space:pre-wrap;word-break:break-word;">${content}</div>`
                });
            }

            // 绑定评论内容点击事件
            $(document).on('click', '.comment-text-preview', function() {
                var text = $(this).attr('data-text');
                if (text) {
                    showCommentDetail(text);
                }
            });

        })
</script>
