{%include file='../component/header.html'%}
<style>
    .chat {
        background: #1ab394;
        color: #fff;
        border-radius: 6px;
        line-height: 14px;
        max-width: 50%;
        margin-bottom: 10px;
        padding: 5px 10px;
        display: block;
        word-wrap: break-word;
    }

    .txxtRight {
        text-align: right;
        clear: both;
        float: right
    }

    .txxtRight .chat {
        float: right;
        background: #f3f3f4;
        color: #676a6c;
    }

    .seed {
        margin-bottom: 20px;
    }

    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
</style>
<div class="row" style="background: #fff; padding: 20px">
    <div class="seed clearfix" style="padding: 10px">
        <div class="input-group">
            <textarea type="text" id="reply" style="width: 450px;height:100px " placeholder="请输入反馈"
                      class="input-sm form-control"></textarea>
            <button type="button" class="btn btn-sm btn-primary" onclick="feedback('{%$uuid%}')"> 发送</button>
            <select class="replay_model">
                <option value="">快速回复</option>
                {%foreach $reply as $v2%}
                    <option value="{%$v2['title']%}">{%$v2['title']%}</option>
                {%/foreach%}
            </select>
            <br/><br/>
            <button type="button" class="layui-btn layui-btn-danger" id="test7"><i class="layui-icon"></i>发送图片</button>
        </div>
    </div>
    {%if !empty($items)%}
        {%foreach $items as $v%}
            <div class="col-xs-12 {%if $v['status'] == 2%}txxtRight{%/if%}">
                <div>{%$v['created_at']%}</div>
                <div style="padding: 10px">
                    {%if ($v['message_type'] == 2)%}
                        <img class="images" src="{%$img_url%}{%$v['question']%}" style="max-width:100px;"
                             onclick="showImage('{%$img_url%}{%$v['question']%}')">
                    {%else%}
                        <span class="chat">{%$v['question']%}</span>
                    {%/if%}
                </div>
            </div>
        {%/foreach%}
    {%/if%}
</div>

<script src="/static/js/jquery.min.js"></script>

<script>
    $(".replay_model").change(function () {
        var title = $(this).val();
        var reply = $('#reply').val(title);
    });

    function feedback(uuid) {
        var reply = $('#reply').val();
        reply = reply.replace(/(^\s*)|(\s*$)/g, "");
        if (reply.length == 0) {
            layer.msg('请输入反馈内容', {icon: 5});
            return false
        }
        $.ajax({
            "url": "{%url('back')%}",
            "type": "post",
            "data": {
                "uuid": uuid,
                "reply": reply,
            },
            "dataType": "json",
            "success": function () {
                console.log(arguments);
                var html = '<div class="col-xs-12 txxtRight"><div>刚刚</div><div><span class="chat">' + reply + '</span></div></div>';
                $(".seed").before(html);
                parent.$('.uuid' + uuid).hide();
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.window.changeFlag()
                parent.layer.close(index);
            }
        })
    }

    function showImage(url) {
        top.layer.open({
            type: 1,
            title: false,
            shadeClose: true,
            shade: 0.3,
            area: ['400px', '500px'],
            content: "<img src='" + url + "' style='max-height:100%;max-width:100%;' />"
        });
    }

    layui.use(['flow', 'laytpl', 'upload', 'laydate', 'layedit'], function (flow, laytpl, upload, layDate, layEdit) {
        upload.render({
            elem: '#test7'
            , url: '{%url("upload/upload")%}' //改成您自己的上传接口
            , size: 10000 //限制文件大小，单位 KB
            , done: function (res) {
                if (res.code === 200) {
                    $.post('{%url('back')%}', {"uuid": "{%$uuid%}", "reply": res.data.url, "type": 2}, function (json) {
                        if (json.code == 0) {
                            layer.msg('图片发送成功');
                            // var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                            // parent.layer.close(index);
                            return
                        }
                        layer.msg('图片发送失败，请重试', {icon: 5});
                        return
                    })
                } else {
                    layer.msg('图片发送失败，请重试', {icon: 5});
                    return
                }
            }
        });
    })
</script>
</html>