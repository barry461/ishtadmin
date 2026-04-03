{%include file="header.tpl"%}

<style>
    .layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }
    .layuiadmin-card-header-auto .layui-select-title input{
        width: 168px;
    }
</style>
<div class="layui-card layadmin-header">
    <div class="layui-breadcrumb" lay-filter="breadcrumb">
        <a lay-href="">主页</a>
        <a><cite>组件</cite></a>
        <a><cite>数据表格</cite></a>
        <a><cite>开启头部工具栏</cite></a>
    </div>
</div>

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
                                <input type="text" name="where[uid]" placeholder="请输入ID" autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">角色</label>
                            <div class="layui-input-block">
                                <select name="where[role_id]" id="">
                                    <option value="">全部</option>
                                    {%html_options options=$roleArray%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">账号</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[username]" placeholder="请输入" autocomplete="off"
                                       class="layui-input">
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
                    <table class="layui-hide" id="gridTab" lay-filter="table-toolbar"></table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="addUser" lay-form="user-edit-dialog">
                                添加
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-normal layui-btn-xs" onclick="ban({{=d.uid}})" lay-form="user-secret-dialog"><i class="layui-icon layui-icon-snowflake"></i>封禁/解封</a>
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="secret"
                           lay-form="user-secret-dialog">
                            <i class="layui-icon layui-icon-edit"></i>密钥</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" onclick="genCode({{=d.uid}})">
                            <i class="layui-icon layui-icon-edit"></i></i>生成授权
                        </a>
                        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"
                           lay-form="user-edit-dialog">
                            <i class="layui-icon layui-icon-edit"></i>修改</a>
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.uid}}" lay-event="del">
                            <i class="layui-icon layui-icon-delete"></i>删除</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" id="user-edit-dialog" data-h="450" data-w="480" layer-dialog="确认,取消"
        data-option="{title:'保存信息',closeBtn:false,shadeClose:true,anim:3,full:false}">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form" action="" lay-filter="form-save">

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">账号：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="账号" name="username"
                           value="{{=d.username }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">角色：</label>
                <div class="layui-input-inline">
                    <select name="role_id" data-value="{{=d.role_id}}">
                        <option value="">不选择</option>
                        {%html_options options=$roleArray%}
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">密码：</label>
                <div class="layui-input-inline">
                    {{# if( d.id ){ }}
                    <input placeholder="密码" name="password"
                           value="{{=d.password }}" class="layui-input">
                    {{# }else{ }}
                    <input lay-verify="required" placeholder="密码" name="password"
                           value="{{=d.password }}" class="layui-input">
                    {{# } }}
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">姓名：</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="对外展示姓名" name="nickname"
                           value="{{=d.nickname }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.uid}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>
<script type="text/html" id="user-secret-dialog" data-h="450" data-w="480" layer-dialog="确认,取消"
        data-option="{title:'保存信息',closeBtn:false,shadeClose:true,anim:3,full:false}">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">密钥</label>
                <div class="layui-input-inline">
                    <input lay-verify="required" placeholder="密钥" name="secret"
                           value="{{=d.secret }}" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.uid}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>
    </form>
</script>
<script>
    layui.config({
        base: '{%$smarty.const.LAY_UI_STATIC%}layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    });
    xx.use({
        url: '{%url("listAjax")%}',
        saveUrl: '{%url("save")%}',
        delUrl: '{%url("del")%}',
        updateUrl: "",
        title: '数据表',
        cols: [[

            {field: 'uid', title: 'id'}
            ,{field: 'role_name', title: '角色'}
            , {field: 'username', title: '账号'}
            , {field: 'secret', title: '谷歌验证'}
            , {field: 'lastip', title: '最后登陆IP'}
            , {field: 'lastvisit', title: '最后登陆时间'}
            , {fixed: 'right', width: 410, title: '操作', align: 'center', toolbar: '#operate-toolbar'}
        ]]
    });
</script>
<script src="/static/backend/data-list.js?t={%time()%}"></script>
{%include file="fooler.tpl"%}
<script>
    function genCode(uid) {
        $.post("{%url('admin/qrcode')%}", {"uid": uid})
            .then(function (json) {
                if (json.code) {
                    Util.msgErr(json.msg);
                } else {
                    //Util.msgOk(json.msg);
                    window.open(json.data)
                }
            })
    }
    // 解禁/禁止
    function ban(uid) {
        $.post("{%url('admin/ban')%}", {"uid": uid})
            .then(function (json) {
                if (json.code) {
                    Util.msgErr(json.msg);
                } else {
                    Util.msgOk(json.msg);
                }
            })
    }
</script>