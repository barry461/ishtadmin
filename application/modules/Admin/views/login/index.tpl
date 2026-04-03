<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>吃瓜APP-登录</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/style/login.css" media="all">
</head>
<body style="display: none">

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>{%register('site.app_name')%}</h2>
            <p>管理系統</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username"
                       for="LAY-user-login-username"></label>
                <input type="text" name="user_name" id="LAY-user-login-username" lay-verify="required" placeholder="用户名"
                       class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                       for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required"
                       placeholder="密码" class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                       for="LAY-user-login-password"></label>
                <input type="text" name="card_num" id="LAY-user-login-password" lay-verify="required"
                       placeholder="标识码" class="layui-input">
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
            </div>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p>© 2021 <a href="javascript:;" target="_blank">{%register('site.app_name')%}@TM</a></p>
    </div>

    <!--<div class="ladmin-user-login-theme">
      <script type="text/html" template>
        <ul>
          <li data-theme=""><img src="{{ layui.setter.base }}style/res/bg-none.jpg"></li>
          <li data-theme="#03152A" style="background-color: #03152A;"></li>
          <li data-theme="#2E241B" style="background-color: #2E241B;"></li>
          <li data-theme="#50314F" style="background-color: #50314F;"></li>
          <li data-theme="#344058" style="background-color: #344058;"></li>
          <li data-theme="#20222A" style="background-color: #20222A;"></li>
        </ul>
      </script>
    </div>-->

</div>
<script>
    if (top !== self) {
        top.location.href = self.location.href;
    } else {
        document.body.style.display = 'block';
    }
</script>
<script src="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '{%$smarty.const.LAY_UI_STATIC%}layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function () {
        var $ = layui.$
            , form = layui.form;
        form.render();
        //提交
        form.on('submit(LAY-user-login-submit)', function (obj) {
            //请求登入接口
            $.post("{%url('login/doLogin')%}", obj.field, function (res) {
                if (0 === res.code) {
                    layer.msg(res.msg);
                    window.location.href = res.data;
                } else {
                    return layer.msg(res.msg, {anim: 6, time: 1000});
                }
            }, 'json');
        });
    });
</script>
</body>
</html>