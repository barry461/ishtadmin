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
    }
    .layui-table-body td .layui-table-cell{height: 70px;line-height: 70px;}
</style>
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
                            <label class="layui-form-label">客服回复</label>
                            <div class="layui-input-block">
                                <select name="where[is_replay]">
                                    <option value="">全部</option>
                                    {%html_options options=UserFeedModel::REPLAY_STATUS selected=0%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">用户等级</label>
                            <div class="layui-input-block">
                                <select name="where[vip_level]">
                                    <option value="">全部</option>
                                    {%html_options options=MemberModel::VIP_LEVEL%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">uuid</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[uuid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">邀请码</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff_code]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
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
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',where:{'where[is_replay]':0},limit:50,limits:[10,20,30,40,50,60,70,80,90,100],toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id',width:100}">id</th>
                            <th lay-data="{field:'user_str',templet:'#member-basis',width:280}">用户</th>
                            <th lay-data="{field:'uid',width:80}">aff</th>
                            <th lay-data="{field:'vip_level_str',width:90}">会员等级</th>
                            <th lay-data="{field:'question_str',width:200}">内容</th>
                            <th lay-data="{field:'no_reply_ct',width:50}">未回复数</th>
                            <!-- <th lay-data="{field:'image_str',width:100}">图片</th> -->
                            <th lay-data="{field:'status_name', width:80}">操作人</th>
                            <th lay-data="{field:'is_replay', width:80}">已回复</th>
                            <th lay-data="{field:'user_ip',width:140,}">ip</th>
                            <th lay-data="{field:'location_str',width:140}">位置</th>
                            <th lay-data="{field:'created_at',width:160}">创建时间</th>
                            <th lay-data="{field:'admin_str',width: 150}">最后处理管理员</th>
                            <th lay-data="{fixed: 'right',width: 220 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                             <button class="layui-btn layui-btn-sm" lay-event="replys"
                                    data-pk="uuid">统一回复
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="screen"
                                    data-pk="uuid">统一屏蔽
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="unScreen"
                                    data-pk="uuid">统一恢复
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="export">导出
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="export_img">有图导出
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a href="javascript:;" style="color: #0a6aa1" lay-event="quickHuifu" data-uuid="{{d.uuid}}">
                            快速回复</a>&nbsp;|&nbsp;
                        <a href="javascript:;" style="color: #1AB394" lay-event="huifu" data-uuid="{{d.uuid}}">
                            回复</a>&nbsp;|&nbsp;
                        <a href="javascript:;" style="color: #9B410E" lay-event="info" data-uuid="{{d.uuid}}">
                            详情</a>&nbsp;|&nbsp;
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}"
                           lay-event="del">
                            删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" class="data-dialog" id="quickSelectList">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px;width: 1200px">
        <div class="layui-form-item">
            <label class="layui-form-label">选择回复模版：</label>
            <div class="layui-input-inline" style="width:1000px">
                <select name="value" id="quick-list">
                    {%html_options options=$huifuList%}
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
            width: 322px;
            height: 40px;
            font-size: 18px;
            border: 1px #e6e6e6 solid;
            padding-left: 9px;
        }
    </style>
    <select name="value" id="quick-list-2">
        <option value="">无</option>
        {%html_options options=$huifuList%}
    </select>
</script>


<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">created_at：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="created_at" name="created_at" value="{{=d.created_at }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户评价：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户评价" name="evaluation" value="{{=d.evaluation }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">问题类型:详见admin/Usercontroller：</label>
                <div class="layui-input-inline">
                    <input placeholder="问题类型:详见admin/Usercontroller" name="help_type" value="{{=d.help_type }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">图片1：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="图片1" name="image_1" value="{{=d.image_1 }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">is_read：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="is_read" name="is_read" value="{{=d.is_read }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">1客服已经回复：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="1客服已经回复" name="is_replay" value="{{=d.is_replay }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">消息类型 1 文字 2图片：</label>
                <div class="layui-input-inline">
                    <input placeholder="消息类型 1 文字 2图片" name="message_type" value="{{=d.message_type }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">问题描述：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="问题描述" name="question" value="{{=d.question }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">1:用户内容;2,管理员已回复：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="1:用户内容;2,管理员已回复" name="status" value="{{=d.status }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">updated_at：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="updated_at" name="updated_at" value="{{=d.updated_at }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">ip：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="ip" name="user_ip" value="{{=d.user_ip }}" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户唯一id：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="用户唯一id" name="uuid" value="{{=d.uuid }}" class="layui-input">
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

    var flag = 1;
    top.window.changeFlag = function (){
        flag = 0;
        console.log('change:'+flag)
    }

    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

        function doHuifu(data,obj){
            $.post("{%url('back')%}", data)
                .then(function (json) {
                    if (json.code) {
                        Util.msgErr(json.msg);
                         table.reload('test');
                    } else {
                        reloadObjectData(obj , json)
                        Util.msgOk(json.msg);
                         table.reload('test');
                    }
                })
        }


        table.on('tool(table-toolbar)', function (obj) {
            //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data,
                layEvent = obj.event,
                that = this,_backData = {},_uuid = undefined
            switch (layEvent) {
                case 'quickHuifu':
                    _uuid =$(that).data('uuid');
                    lazy('#quickSelectList')
                        .offset('auto')
                        .data(data)
                        .title('快速回复')
                        .area(['1200px' , '500px'])
                        .dialog(function (id, ele) {
                            doHuifu({"uuid":_uuid , "reply":$('#quick-list').val()} , obj);
                            layer.close(id);
                        })
                        .laytpl(function () {
                            xx.renderSelect(data, $, form);
                        });
                    break;
                case 'huifu':
                    _uuid =$(that).data('uuid');
                    top.layer.prompt({
                        formType: 2,
                        value: ' ',
                        title: '请输入回复内容',
                        area: ['300px', '150px'] //自定义文本域宽高
                    }, function(value, index, elem){
                        top.layer.close(index);
                        doHuifu({"uuid":_uuid , "reply":value} , obj);
                    });
                    break;
                case 'info':
                    _uuid =$(that).data('uuid');
                    flag = 1;
                    console.log('init:'+flag)
                    top.layer.open({
                        type: 2,
                        title: '反馈详情',
                        shadeClose: true,
                        shade: 0.4,
                        area: ['1200px', '600px'],
                        content: "{%url('detail')%}?uuid=" + _uuid,
                        cancel: function(res) {
                            console.log('cancel:'+flag)
                            flag = res;
                        },
                        end: function() {
                            console.log('end:'+flag)
                            if (!flag) {
                                table.reload('test');
                            }
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
                case 'replys':
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
                        title: '请输入回复内容',
                        area: ['300px', '150px'], //自定义文本域宽高
                        success:function () {
                            $(".layui-layer-content input").attr({'placeholder':'请输入回复内容'})
                            let html = quickSelectList2.innerHTML
                            $(".layui-layer-content").append("<br/>" + html)
                            $('#quick-list-2').on('change', function () {
                                $('.layui-layer-prompt textarea').val($('#quick-list-2').val())
                            })
                        }
                    }, function(value, index, elem){
                        layer.close(index);
                        $.post("{%url('someBack')%}", {"uuids": pkValAry.join(','), "content":value})
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
                case 'screen':
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

                    layer.confirm('真的屏蔽吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('screen')%}", {"uuids": pkValAry.join(',')})
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
                case 'unScreen':
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
                    layer.confirm('真的恢复屏蔽吗?', function (index) {
                        layer.close(index);
                        $.post("{%url('unScreen')%}", {"uuids": pkValAry.join(',')})
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
                case 'huifuSelect':
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
                case 'export':
                    window.open("{%url('dd')%}");
                    break;
                case 'export_img':
                    window.open("{%url('ddnew')%}");
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


        function reloadObjectData(obj, json) {
            //修改
            obj.update(json.data);
            let index = $(obj.tr).data('index')
            table.cache['test'][index] = json.data;
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