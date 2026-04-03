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
    
    /* 帖子评论状态背景色 */
    .status-pass { background-color: #5FB878 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    .status-wait { background-color: #FFB800 !important; color: white; padding: 2px 6px; border-radius: 3px; }
    .status-unpass { background-color: #FF5722 !important; color: white; padding: 2px 6px; border-radius: 3px; }
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
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">帖子ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[post_id]" placeholder="请输入" value="{%$postId%}"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">帖子标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[post_title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">用户AFF</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">发布类型</label>
                            <div class="layui-input-block">
                                <select name="where[type]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::TYPE_TIPS%}
                                </select>
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
                            <label class="layui-form-label">评论内容</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[comment]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        {%/if%}

                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostCommentModel::STATUS_TIPS selected=$default_status %}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">置顶</label>
                            <div class="layui-input-block">
                                <select name="where[is_top]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostCommentModel::TOP_TIPS%}
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
                           lay-data="{url:'{%url('listAjax')%}',where:{'where[post_id]':'{%$postId%}','where[pid]':'{%$pid%}','where[status]':'{%$default_status%}'}, limit:90, page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width: 80 }">评论ID</th>
                            <th lay-data="{field:'post_id',width: 120 ,templet:'#a1'}">关联信息</th>
                            <th lay-data="{field:'aff',width: 120,templet:'#a2'}">用户</th>
                            <th lay-data="{field:'comment',width:300}">内容</th>
                            <th lay-data="{minWidth:210,templet:'#attr3-xx'}">资源</th>
                            <th lay-data="{field:'status_str',width: 80, templet: '#statusTpl'}">状态</th>
                            <th lay-data="{field:'top_str',width: 80}">置顶</th>
                            <th lay-data="{field:'ipstr',width: 120,templet:'#a3'}">IP/城市</th>
                            <th lay-data="{field:'sort',width: 80,edit:true,sort:true}">排序</th>
                            <th lay-data="{field:'like_num',width: 80}">点赞数</th>
                            <th lay-data="{field:'created_at',width: 150}">创建时间</th>
                            <th lay-data="{field:'admin_str',width: 150}">审核管理员</th>
                            <th lay-data="{fixed: 'right',width: 350 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                        <script type="text/html" id="statusTpl">
                            <span class="{{=d.status_class}}">{{=d.status_str}}</span>
                        </script>
                        <script type="text/html" id="a2">
                            aff:{{=d.aff}} <br>
                            昵称:{{=d.author}} <br>
                        </script>
                        <script type="text/html" id="a1">
                            帖子ID:{{=d.post_id}} <br>
                            pid:{{=d.pid}} <br>
                        </script>
                        <script type="text/html" id="a3">
                            IP:{{=d.ipstr}} <br>
                            城市:{{=d.cityname}} <br>
                        </script>
                        <script type="text/html" id="attr3-xx">
                            <div style="line-height: normal">
                                图片:
                                {{# layui.each(d.imgs, function(index, item){ }}
                                <img style="display: inline-block;width: 25px;height: 25px;margin-bottom: 3px;"
                                     onclick="clickShowImage(this)" src="{{=item.media_url}}">
                                {{# }); }}
                            </div>
                            <div style="line-height: normal;margin-top: 10px;">
                                视频:
                                {{# layui.each(d.videos, function(index, item){ }}
                                <a style="color: red" target="_blank" href="{{=item.media_url}}">
                                    <img style="display: inline-block;width: 25px;height: 25px;margin-bottom: 3px;" src="{{=item.cover}}">
                                </a>&nbsp;&nbsp;&nbsp;
                                {{# }); }}
                            </div>
                        </script>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
                            <button class="layui-btn layui-btn-sm" lay-event="batPass" data-pk="id">批量通过</button>
                            <button class="layui-btn layui-btn-sm" lay-event="batRefuse" data-pk="id">批量拒绝</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        {{# if(d.photo_num > 0){ }}
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="img" data-id="{{d.id}}">
                            <i class="layui-icon layui-icon-picture"></i>图片</a>
                        {{# } }}

                        {{# if(d.video_num > 0){ }}
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="video" data-id="{{d.id}}">
                            <i class="layui-icon layui-icon-video"></i>视频</a>
                        {{# } }}

                        {{# if(d.pid == 0 && d.status == 1){ }}
                            {{# if(d.is_top == 0){ }}
                                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="topSet" data-id="{{d.id}}">
                                    <i class="layui-icon layui-icon-top"></i>置顶
                            {{# } else { }}
                                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="topSet" data-id="{{d.id}}">
                                    <i class="layui-icon layui-icon-close-fill"></i>取消置顶
                            {{# } }}
                        {{# } }}

                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="element" data-id="{{d.id}}">
                            <i class="layui-icon layui-icon-search"></i>子评</a>
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
            <label class="layui-form-label">帖子ID：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="帖子ID" name="post_id"
                       value="{{=d.post_id }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">pid：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="0表示1级评论" name="pid"
                       value="{{=d.pid || 0}}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">用户AFF：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="用户AFF" name="aff"
                       value="{{=d.aff }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">广告图片：</label>
                <div class="layui-input-inline" style="width: 140px">
                    <span id="ads-img">{%html_upload name='ads_img' src='ads_img' value='ads_img'%}</span>
                </div>
            </div>
            <input type="hidden" name="ads_img_w" value="{{=d.ads_img_w}}">
            <input type="hidden" name="ads_img_h" value="{{=d.ads_img_h}}">
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">广告跳转类型：</label>
                <div class="layui-input-inline">
                    <select name="redirect_type" data-value="{{=d.redirect_type }}">
                        {%html_options options=PostCommentModel::REDIRECT_TYPE_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">广告地址：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="广告地址" name="ads_url"
                       value="{{=d.ads_url }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">IP：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="IP" name="ipstr"
                       value="{{=d.ipstr || '127.0.0.1'}}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">城市：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="城市" name="cityname"
                       value="{{=d.cityname || '火星'}}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">评论内容：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="评论内容" name="comment"
                       value="{{=d.comment }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{=d.status }}">
                        {%html_options options=PostCommentModel::STATUS_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">处理状态：</label>
                <div class="layui-input-inline">
                    <select name="is_finished" data-value="{{=d.is_finished }}">
                        {%html_options options=PostCommentModel::FINISH_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">排序：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="排序" name="sort"
                       value="{{=d.sort }}" class="layui-input">
            </div>
        </div>


        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

<script type="text/html" class="data-dialog" id="quickSelectList2">
    <style>
        #quick-list-2{
            font-family: "微软雅黑", serif;
            background: rgba(0,0,0,0);
            width: 372px;
            height: 40px;
            font-size: 18px;
            border: 1px #e6e6e6 solid;
            padding-left: 9px;
        }
    </style>
    <select name="value" id="quick-list-2">
        {%html_options options=$refuseReason%}
    </select>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit','upload','jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

        function join(data,obj){
            $.post("{%url('joinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg,location.reload);
                    }
                })
        }
        function batJoin(data,obj){
            $.post("{%url('batJoinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg,location.reload);
                    }
                })
        }

        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this;
            switch (layEvent) {
                case 'img':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('postmedia/index')%}?pid=' + data['id'] + '&relate_type=2&type=1')
                        .area([`${ddd.clientWidth - 200}px`, `${ddd.clientHeight}px`])
                        .title(`数据管理-[${data.id}]${data.title}`)
                        .start(function () {

                        })
                    break;
                case 'video':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('postmedia/index')%}?pid=' + data['id'] + '&relate_type=2&type=2')
                        .area([`${ddd.clientWidth - 200}px`, `${ddd.clientHeight}px`])
                        .title(`数据管理-[${data.id}]${data.title}`)
                        .start(function () {

                        })
                    break;
                case 'element':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('postcomment/index')%}?post_id='+data['post_id']+'&pid=' + data['id'])
                        .area([`${ddd.clientWidth - 200}px`, `${ddd.clientHeight}px`])
                        .title(`数据管理-[${data.id}]${data.title}`)
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
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            $('#ads-img img').on('load',function (){
                                $('input[name="ads_img_w"]').val(this.naturalWidth)
                                $('input[name="ads_img_h"]').val(this.naturalHeight)
                            });
                        });
                    break;
                case 'topSet':
                    layer.confirm('帖子真的要置顶/取消置顶吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('topSet')%}", {"id": $(that).data('id')})
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
        })

        //监听头工具栏事件
        table.on('toolbar(table-toolbar)', function (obj) {
            var layEvent = obj.event;
            switch (layEvent) {
                case 'batPass':
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
                    layer.confirm('真的通过吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('pass_all')%}", {"value": pkValAry.join(',')})
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
                case 'batRefuse':
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
                    layer.prompt({
                        formType: 2,
                        value: ' ',
                        id:'prompt-replys',
                        title: '请输入拒绝内容',
                        area: ['350px', '250px'], //自定义文本域宽高
                        success:function () {
                            $(".layui-layer-content input").attr({'placeholder':'请输入拒绝内容'})
                            let html = quickSelectList2.innerHTML
                            $(".layui-layer-content").append("<br/>" + html)
                            $('#quick-list-2').on('change', function () {
                                $('.layui-layer-prompt textarea').val($('#quick-list-2').val())
                            })
                        }
                    }, function(value, index, elem){
                        layer.close(index);
                        $.post("{%url('batch_refuse')%}", {"ids": pkValAry.join(','), "content":value})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                    table.reload('test');
                                } else {
                                    Util.msgOk(json.msg);
                                    table.reload('test');
                                }
                            })
                    });
                    break;
                case 'add':
                    lazy('#user-edit-dialog')
                        .width(900)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            $('#ads-img img').on('load',function (){
                                $('input[name="ads_img_w"]').val(this.naturalWidth)
                                $('input[name="ads_img_h"]').val(this.naturalHeight)
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