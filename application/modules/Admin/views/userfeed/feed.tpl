{%include file='../component/header.html'%}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>用户反馈列表:({%$count%})条</h5>
                <div class="ibox-tools">
                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-10"></div>
                    <div class="col-sm-2"><a class="btn btn-sm btn-primary" onclick="replylist()">回复模板</a><a
                                class="btn btn-sm btn-primary" onclick="replyall()">批量回复</a></div>
                </div>
                <div class="row">
                    <form action="/admin/feed/index" method="get">
                        <div class="col-sm-2" style="width: auto">
                            <div class="input-group">
                                <select name="feed_status" class="input-sm form-control input-s-sm inline">
                                    <option value="">最后留言方筛选</option>
                                    {%foreach $feed_status as $k => $v%}
                                    <option {%if ($search.status== $k)%}selected{%/if%} value="{%$k%}">{%$v%}</option>
                                    {%/foreach%}
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <input class="input-sm form-control input-s-sm inline" value="{%$search.uuid%}"
                                   placeholder="UUID" name="uuid">
                        </div>
                        <div class="col-sm-2">
                            <input class="input-sm form-control input-s-sm inline" style="width: 200px"
                                   value="{%$search.start%}" readonly placeholder="开始时间" id="start" name="start">
                        </div>
                        <div class="col-sm-2">
                            <input class="input-sm form-control input-s-sm inline" style="width: 200px"
                                   value="{%$search.end%}" readonly placeholder="结束时间" id="end" name="end">
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-sm btn-primary"> 搜索</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th width="40px"><a href="javascript:void(0);" class="choose_all">全选</a></th>
                            <!--                                <th>ID</th>-->
                            <th>增加时间</th>
                            <th>uuid</th>
                            <th>内容</th>
                            <th>图片</th>
                            <th>最后留言方</th>
                            <th>快捷回复</th>
                            <th>更新时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        {%if $items%}
                        {%foreach $items as $v%}
                        <tr class="uuid{%$v['uuid']%}">
                            <td><input type="checkbox" class="sel" value="{%$v['uuid']%}"></td>
                            <!--                                    <td>{$v['id']}</td>-->
                            <td>{%$v['created_at']%}</td>
                            <td>
                                <a onclick="userInfo('{%$v['uuid']%}')">{%$v['uuid']%}</a>
                                <br/>{%$v.user.nickname%} - <span style="{%if $v.user.vip_level != 0%}color:red{%/if%}">{%$v.user.vip_level_name%}</span>
                                <br/> {%$v.user.oauth_type%} - {%$v.user.app_version%}
                                <br/> <a href="http://ip.zxinc.org/ipquery/?ip={%$v['user_ip']%}&action=2"
                                         target="_blank">{%$v['user_ip']%}</a>
                            </td>
                            <td>{%$v['question']%}</td>
                            <td>
                                {%if (!empty($v['image_1']))%}
                                <img class="images" src="{%$img_head%}{%$v['image_1']%}" style="max-width:100px;"
                                     onclick="showImg('{%$img_head%}{%$v['image_1']%}')">
                                {%/if%}
                            </td>
                            <td>{%$v['status_name']%}</td>
                            <td>
                                <select style="width: 150px;" data-uuid="{%$v['uuid']%}"
                                        class="replay_model uuid-{%$v['uuid']%}">
                                    <option value="">选择模板快速回复</option>
                                    {%foreach $replay_list as $v2%}
                                    <option value="{%$v2['title']%}">{%$v2['title']%}</option>
                                    {%/foreach%}
                                </select>
                            </td>
                            <td>{%$v['updated_at']%}</td>
                            <td>
                                <a class="btn btn-xs btn-primary"
                                   onclick="feedDetail('{%$v['uuid']%}','{%$v['uuid']%}')">详情</a>
                                <a class="btn btn-xs btn-danger" onclick="handleDel('{%$v['id']%}')">删除</a>
                            </td>
                        </tr>
                        {%/foreach%}
                        {%else%}
                        <tr>
                            <td colspan="15">暂无数据</td>
                        </tr>
                        {%/if%}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.laydate.render({
        elem: '#start',
        event: 'click', //触发事件
        format: 'yyyy-MM-dd HH:mm:ss', //日期格式
        istime: true, //是否开启时间选择
        type: 'datetime'
    })
    layui.laydate.render({
        elem: '#end',
        event: 'click', //触发事件
        format: 'yyyy-MM-dd HH:mm:ss', //日期格式
        istime: true, //是否开启时间选择
        type: 'datetime'
    })

    function userInfo(uuid) {
        var url = "/admin/member/index?uuid=" + uuid;
        layerOpen(url, '用户详情')
    }

    function replyall() {
        var arr = [];
        $(".sel:checked").each(function (i) {
            arr[i] = $(this).val();
        });
        if (arr.length == 0) {
            layer.msg("请选择回复的项", {icon: 7});
            return;
        }
        var uuids = arr.join(",");
        layer.open({
            type: 2,
            title: '批量回复',
            shadeClose: true,
            shade: 0.8,
            area: ['600px', '500px'],
            content: "/admin/feed/batchBack?uuids=" + uuids
        });
    }

    function feedback(id) {
        var url = "d.php?mod=user&code=replyUserFeed&feed_id=" + id
        layer.prompt({title: '请填写回复', formType: 2}, function (reply, index) {
            if (reply.replace(/(^\s*)|(\s*$)/g, "").length == 0) {
                layer.msg('回复不能为空!', {icon: 5});
                return false
            }
            url += "&reply=" + reply.replace(/(^\s*)|(\s*$)/g, "")
            getAjax(url, function (res) {
                layer.msg(res, {icon: 1, time: 2000}, function () {
                    window.location.reload()
                })
            })
        });
    }

    function feedDetail(uuid) {
        top.layer.open({
            type: 2,
            title: '反馈详情',
            shadeClose: true,
            shade: 0.4,
            area: ['1200px', '600px'],
            content: "{%url('detail')%}?uuid=" + uuid
        });
    }

    function handleDel(id) {
        var url = "/admin/feed/destroy/id/" + id;
        layer.confirm('确定要删除此条反馈吗？', {btn: ['确定', '取消']}, function () {
            getAjax(url, function (res) {
                layer.msg(res, {icon: 1, time: 2000}, function () {
                    window.location.reload()
                })

            })
        })
    }

    function replylist() {
        layer.open({
            type: 2,
            title: '回复模板',
            shadeClose: true,
            shade: 0.8,
            area: ['800px', '700px'],
            content: "/admin/feed/replyList"
        });
    }

    function showImg(url) {
        layer.open({
            type: 1,
            title: false,
            shadeClose: true,
            shade: 0.8,
            area: ['auto', 'auto'],
            content: "<img src='" + url + "' style='max-height:500px;max-width:800px;' />"
        });
    }

    function layerOpen(url, title) {
        parent.layer.open({
            type: 2,
            title: title,
            closeBtn: 1,
            shadeClose: true,
            shade: [0.5],
            area: ['1400px', '750px'],
            content: url
        })
    }

    function getAjax(url, reloadRes) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                if (res.status == 1) {
                    layer.msg(res.data, {icon: 1});
                    reloadRes(res.data)
                } else {
                    layer.msg(res.data, {icon: 5})
                }
            }
        })
    }

    $(".replay_model").change(function () {
        var title = $(this).val();
        if (title == '') {
            layer.msg('回复不能为空!', {icon: 5});
            return false
        }
        var uuid = $(this).attr("data-uuid");
        var url = "/admin/feed/feedback?uuid=" + uuid + "&reply=" + title;
        $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                layer.msg(res.data, {icon: 1});
                $(".uuid-" + uuid).parent().parent().hide();
            }
        })
    });


    function reload(msg) {
        parent.layer.msg(msg, {icon: 1});
        window.location.reload()
    }

    //全选
    $(document).on('click', '.choose_all', function () {
        $('.sel').prop("checked", true);
        $(this).addClass('inverse');
        $(this).removeClass('choose_all');
        $(this).text('反选');
    });
    //反选
    $(document).on('click', '.inverse', function () {
        $('.sel').each(function () {
            $(this).prop("checked", !$(this).prop("checked"));
        });
        $(this).addClass('choose_all');
        $(this).removeClass('inverse');
        $(this).text('全选');
    });
</script>
{%include file='../component/footer.html'%}