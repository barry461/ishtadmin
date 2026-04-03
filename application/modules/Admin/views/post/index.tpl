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
                            <label class="layui-form-label">帖子ID</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input" value="">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">用户aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input" value="{%$aff%}">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input" value="{%$title%}">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">话题</label>
                            <div class="layui-input-block">
                                <select name="where[topic_id]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=$topicArr selected=$topicId%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">发布类型</label>
                            <div class="layui-input-block">
                                <select name="where[category]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::TYPE_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">置精</label>
                            <div class="layui-input-block">
                                <select name="where[is_best]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::BEST_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">资源状态</label>
                            <div class="layui-input-block">
                                <select name="where[is_finished]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::FINISH_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::STATUS_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">删除</label>
                            <div class="layui-input-block">
                                <select name="where[is_deleted]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::DELETED_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">订阅</label>
                            <div class="layui-input-block">
                                <select name="where[is_subscribe]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=PostModel::SUBSCRIBE_TIPS%}
                                </select>
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">创建时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created_at"%}
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
                           lay-data="{url:'{%url('listAjax')%}',where:{'where[topic_id]':'{%$topicId%}','where[aff]':'{%$aff%}'}, page:true, limit:90, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width: 80}">id</th>
                            <th lay-data="{field:'aff',width:160,templet:'#member-basis'}">用户</th>
                            <th lay-data="{field:'content',width:210,templet:'#a2'}">内容</th>
                            <th lay-data="{field:'photo_num',width: 160,templet:'#a3'}">属性</th>
                            <th lay-data="{field:'is_best',width: 150,templet:'#a1'}">状态</th>
                            <th lay-data="{field:'ipstr',width: 145,templet:'#a4'}">IP</th>
                            <th lay-data="{templet:'#time-attr',width: 168}">时间</th>
                            <th lay-data="{field:'sort',width: 70,edit:true,sort:true}">排序</th>
                            <th lay-data="{field:'category_str',width: 60}">类型</th>
                            <th lay-data="{field:'finish_str',width: 80}">资源完成</th>
                            <th lay-data="{field:'admin_str',width: 150}">审核管理员</th>
                            <th lay-data="{fixed: 'right',width: 220 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                        <script type="text/html" id="a4">
                           ip：{{d.ipstr}}  <br>
                           城市：{{d.cityname}}
                        </script>
                        <script type="text/html" id="a3">
                           图片：{{d.photo_num}} &nbsp;|&nbsp;
                           视频：{{d.video_num}} <br>
                           点赞：{{d.like_num}} &nbsp;|&nbsp;
                           评论：{{d.comment_num}} <br>
                           浏览：{{d.view_num}} <br>
                        </script>
                        <script type="text/html" id="a2">
                            <h4 style="white-space: pre-wrap;">{{d.title}}</h4>
                            <span style="color: #4298BA">#{{d.topic_str}}</span>
                            <hr>
                            {{d.content_word}}
                        </script>
                        <script type="text/html" id="a1">
                            <span style="color: {{d.status==1?'blue':'red'}}">审核：{{d.status_str}}</span><br>
                            <span style="color: {{d.is_subscribe==0?'blue':'red'}}">订阅：{{d.subscribe_str}}</span><br>
                            置顶：{{d.set_top}}<br>
                            置精：{{d.bast_str}}<br>
                            删除：{{d.deleted_str}}<br>
                            <span style="color: red">打赏次数：{{d.reward_num}}</span><br>
                            <span style="color: red">打赏收益：{{d.reward_amount}}</span>
                        </script>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">添加</button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect" data-pk="id">删除所选</button>
                            <button class="layui-btn layui-btn-sm" lay-event="batPass" data-pk="id">批量通过</button>
                            <button class="layui-btn layui-btn-sm" lay-event="batRefuse" data-pk="id">批量拒绝</button>
                            <button class="layui-btn layui-btn-sm" lay-event="cachedClear" data-pk="id">缓存清理</button>
                            <button class="layui-btn layui-btn-sm" lay-event="fixPostNum" data-pk="id">用户发帖数修复</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        {{# if(d.status == 0){ }}
                        <a href="javascript:;" lay-event="pass" data-id="{{d.id}}"><span style="color: blue">通过</span></a> |
                        <a href="javascript:;" lay-event="refuse"><span style="color: red">拒绝</span></a> |
                        {{# } }}

                        {{# if(d.photo_num > 0){ }}
                        <a href="javascript:;" lay-event="img" data-id="{{d.id}}">图片</a> |
                        {{# } }}

                        {{# if(d.video_num > 0){ }}
                        <a href="javascript:;" lay-event="video" data-id="{{d.id}}">视频</a>  |
                        {{# } }}
                        <a href="javascript:;" lay-event="element" data-id="{{d.id}}"></i>评论</a>
                        <br>
                        <a  lay-event="edit"></i>修改</a> |
                        {{# if(d.is_ban == 0){ }}
                        <a  lay-event="ban" data-id="{{d.id}}"></i><span style="color: red">封禁</span></a> |
                        {{# } }}
                        <a lay-event="txt"></i>内容</a> |
                        <a data-pk="{{=d.id}}" lay-event="del"><span style="color: red">删除</span></a>
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
            <label class="layui-form-label">aff：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="用户aff" name="aff" value="{{=d.aff }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">标题：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="标题" name="title"
                       value="{{=d.title }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">话题：</label>
                <div class="layui-input-inline">
                    <select name="topic_id" data-value="{{=d.topic_id }}">
                        {%html_options options=$topicArr%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">类型：</label>
                <div class="layui-input-inline">
                    <select name="category" data-value="{{=d.category }}" lay-filter="category">
                        {%html_options options=PostModel::TYPE_TIPS%}
                    </select>
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">订阅状态：</label>
                <div class="layui-input-inline">
                    <select name="is_subscribe" data-value="{{=d.is_subscribe||0 }}">
                        {%html_options options=PostModel::SUBSCRIBE_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">IP：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="IP" name="ipstr" value="{{=d.ipstr }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">定位城市：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="定位城市" name="cityname" value="{{=d.cityname }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">置顶值：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="置顶值" name="set_top"
                           value="{{d.set_top ||0 }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">排序：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="排序" name="sort" value="{{d.sort ||0 }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">精选：</label>
                <div class="layui-input-inline">
                    <select name="is_best" data-value="{{=d.is_best }}">
                        {%html_options options=PostModel::BEST_TIPS%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">删除：</label>
                <div class="layui-input-inline">
                    <select name="is_deleted" data-value="{{=d.is_deleted||0 }}">
                        {%html_options options=PostModel::DELETED_TIPS%}
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">资源状态：</label>
                <div class="layui-input-inline">
                    <select name="is_finished" data-value="{{=d.is_finished||0 }}">
                        {%html_options options=PostModel::FINISH_TIPS%}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">
                    <select name="status" data-value="{{=d.status }}">
                        {%html_options options=PostModel::STATUS_TIPS%}
                    </select>
                    <p style="color: red;width: 800px">帖子类型为视频时，这里不要把状态改为通过,因为这里没有发起视频切片。审核用操作下面的审核或者批量审核</p>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">拒绝原因：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="拒绝通过的原因" name="refuse_reason"
                       value="{{=d.refuse_reason }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

<script type="text/html" class="data-dialog" id="post_refuse">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <input type="hidden" name="post_refuse_id" value="{{=d.id}}">
        <div class="layui-form-item">
            <label class="layui-form-label">拒绝原因:</label>
            <div class="layui-input-block">
                <select name="refuse_reason" id="">
                    {%html_options options=$refuseReason%}
                </select>
            </div>
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

    var gis_reload = false;
    window.reload_test = function (is_reload){
        gis_reload = is_reload
    }

    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {
        $ = layui.jquery;

        form.on('select(category)', function (data) {
            switch (data.value){
                case "{%PostModel::TYPE_VIDEO%}":
                    $('#sps').show();
                    $('#tupian').hide();
                    break;
                case "{%PostModel::TYPE_IMG%}":
                case "{%PostModel::TYPE_TXT%}":
                    $('#tupian').show();
                    $('#sps').hide();
                    break;
            }
        });


        let verify = {}

        function join(data, obj) {
            $.post("{%url('joinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg, location.reload);
                    }
                })
        }

        function batJoin(data, obj) {
            $.post("{%url('batJoinElement')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                    } else {
                        Util.msgOk(json.msg, location.reload);
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
                        .iframe('{%url('postmedia/index')%}?pid=' + data['id'] + '&relate_type=1&type=1')
                        .area([`${ddd.clientWidth - 200}px`, `${ddd.clientHeight}px`])
                        .title(`数据管理-[${data.id}]${data.title}`)
                        .start(function () {

                        })
                    break;
                case 'video':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('postmedia/index')%}?pid=' + data['id'] + '&relate_type=1&type=2')
                        .area([`${ddd.clientWidth - 200}px`, `${ddd.clientHeight}px`])
                        .title(`数据管理-[${data.id}]${data.title}`)
                        .start(function () {

                        })
                    break;
                case 'element':
                    ddd = document.documentElement;
                    lazy('')
                        .iframe('{%url('postcomment/index')%}?post_id=' + data['id'] + '&pid=0')
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
                        .width(1000)
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele, obj)
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                            if (data.category == 2){
                                $('#sps').show();
                                $('#tupian').hide();
                            }else{
                                $('#sps').hide();
                                $('#tupian').show();
                            }
                            Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                        });
                    break;
                case 'audit':
                    if(data.status!=={%PostModel::STATUS_WAIT%}){
                        return ;
                    }
                    audit(data.id,table)
                    break;
                case 'pass':
                    layer.confirm('帖子真的要审核通过吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('pass')%}", {"id": $(that).data('id')})
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
                case 'ban':
                    layer.confirm('真的要封禁用户么?', function (index) {
                        layer.close(index);
                        $.post("{%url('ban')%}", {"id": $(that).data('id')})
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
                case 'refuse':
                    lazy('#post_refuse')
                        .offset('auto')
                        .data(data)
                        .title('帖子拒绝')
                        .area(['800px', '450px'])
                        .dialog(function (id, ele) {
                            let refuse_reason = $('select[name=refuse_reason]').val();
                            let post_id = $('input[name=post_refuse_id]').val();
                            $.post("{%url('refuse')%}", {"id" : post_id, "refuse_reason" : refuse_reason})
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
                case 'txt':
                    layer.open({
                        type: 2,
                        content:'{%url('txt')%}?id='+data.id,
                        area:['100%','100%'],
                        title:false,
                        closeBtn:0,
                        end:function() {
                            if (gis_reload) {
                                table.reload('test');
                            }
                        }
                    })
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
                case 'cachedClear':
                    layer.confirm('列表缓存真的要清理么吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('cached_clear')%}", {})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    // table.reload('test');
                                }
                            })
                    });
                    break;
                case 'fixPostNum':
                    layer.confirm('真的修复用户发帖数量吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('fix_post_num')%}", {})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    // table.reload('test');
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
                        $.post("{%url('batch_refuse')%}", {"ids": pkValAry.join(','), "refuseReason":value})
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
                case 'batJoinElement':
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
                    lazy('#elementSelectList')
                        .offset('auto')
                        .data(data)
                        .title('批量加入组件')
                        .area(['500px', '300px'])
                        .dialog(function (id, ele) {
                            batJoin({"id":pkValAry.join(','), "element_id":$('#element-list').val()} , obj);
                            layer.close(id);
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                        });
                    break;
                case 'add':
                    lazy('#user-edit-dialog')
                        .dialog(function (id, ele) {
                            dialogCallback(id, ele)
                        })
                        .width(1000)
                        .laytpl(function () {
                            xx.renderSelect({}, $, form);
                            $('#sps').hide();
                            $('#tupian').show();
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

        function dialogCallback(id, ele, obj) {
            let from = $(ele).find('form')
            $.post("{%url('save')%}", from.serializeArray())
                .then(function (json) {
                    if (json.code) {
                        return Util.msgErr(json.msg);
                    }
                    layer.close(id);
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
        form.verify(verify);
        layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
    })

    function clickShowPics(obj, pid) {
        $.get("{%url('postmedia/listAjax')%}", {"where[relate_type]": 1,"where[pid]": pid,"where[type]": 1,'page':1,'limit':10000})
            .then(function (json) {
                if (json.code) {
                    Util.msgErr(json.msg);
                } else {
                    let imgList = [];
                    for (let i in json.data) {
                        imgList.push({'src':json.data[i]['media_url']});
                    }
                    layer.photos({
                        //area: ['50%', 'auto'],//定义宽和高
                        photos: {
                            "title": "", //相册标题
                            "start": 0, //初始显示的图片序号，默认0
                            "data": imgList
                        },
                        tab: function (pic, layero) {
                            console.log(pic);
                        }
                    });
                }
            })
    }

    function audit(id,table) {
        layer.open({
            title: '审核',
            btn: ['确定'],
            content: '<div class="layui-form-item" style="font-size: 16px;!important;">'
                + '          <div class="layui-inline">'
                + '          <label class="layui-form-label">状态：</label>'
                + '          <div class="layui-input-inline" style="line-height: 38px">'
                + '             <select name="status" id="status" style="width: 150px;text-align: center;">'
                + '               <option value="{%PostModel::STATUS_PASS%}">通过</option>'
                + '               <option value="{%PostModel::STATUS_UNPASS%}">拒绝</option>'
                + '             </select>'
                + '          </div>'
                + '       </div>'
                + '</div>'
                + '<div class="layui-form-item" style="font-size: 16px;!important;">'
                + '       <label class="layui-form-label">拒绝原因：</label>'
                + '       <div class="layui-input-inline" style="line-height: 38px">'
                + '           <select name="status" id="refuse_reason" style="width: 150px;text-align: center;">'
                + '               <option value="">无</option>'
                + '               <option value="存在敏感词">存在敏感词</option>'
                + '               <option value="您的帖子涉及真实偷拍、未成年、兽交等违规内容">您的帖子涉及真实偷拍、未成年、兽交等违规内容</option>'
                + '               <option value="您帖子的不适用福利姬">您帖子的不适用福利姬</option>'
                + '               <option value="您的帖子/回复不符合福利姬版规，请详细阅读版规后再次尝试">您的帖子/回复不符合福利姬版规，请详细阅读版规后再次尝试</option>'
                + '           </select>'
                + '   </div>'
                + '</div>',
            yes: function (index, layero) {
                let status = $('#status').val();
                let refuseReason = status === {%PostModel::STATUS_PASS%} ? '' : $('#refuse_reason').val();
                if (status === '2' && !refuseReason) {
                    Util.msgErr('拒绝通过时请选择拒绝通过理由');
                    return;
                }

                $.post("{%url('audit')%}", {'id':id,"status":status,'refuse_reason':refuseReason})
                    .then(function (json) {
                        if (json.code) {
                            Util.msgErr(json.msg);
                            layero.close()
                        } else {
                            Util.msgOk(json.msg);
                            table.reload('test');
                            layero.close()
                        }
                    })
            }
        });
    }
</script>