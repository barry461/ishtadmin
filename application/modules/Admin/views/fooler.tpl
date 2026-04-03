<script type="text/html" id="member-basis" >

    {{# if(d.member_uuid) { }}
    <div class="table-member">
       <a href="{{d.member_thumb}}" target="_blank"><img src="{{d.member_thumb}}"/></a>
        <p style="{{=(d.member_isvip==1?'color: red':'')}}">
            <span>{{d.member_nickname}} <i>{{d.member_phone}}</i></span>
            <em>{{d.member_uuid}}</em>
            <em>{{d.member_oauthstr}}</em>
            <em>{{d.member_lastip}}</em>
        </p>
    </div>
    {{# } else if(d.member) { }}
    <div class="table-member">
        <p style="{{=(d.member.vip_level!=0?'color: red':'')}}">
            <span>Aff：{{d.member.aff}} , <a onclick="show_img('{{d.member.thumb}}')" href="javascript:;">{{d.member.nickname}}</a></span>
            <span>Vip：{{d.member.vip_level_str}}</span>
            <span>余额：{{d.member.money}}&nbsp;&nbsp;|&nbsp;&nbsp;收益：{{d.member.income_money}}</span>
        </p>
    </div>
    {{# } else { }}
    该用户已被删除
    {{# } }}

</script>
<script type="text/html" id="time-attr">
    创：{{d.created_at}}<br>
    改：{{d.updated_at}}
</script>

<style>
div.operate-toolbar>a{
    cursor: pointer;
    color: #0a6aa1;
    margin: 2px 0;
}
</style>
<style>

    .refuse-container {
        padding: 20px;
        min-width: 300px;
        box-sizing: border-box;
    }

    .refuse-container .refuse-title-container {
        margin-bottom: 10px;
    }

    .refuse-container .layui-form-label {
        padding: 0;
        text-align: left;
    }

    .refuse-container .refuse-item-box {
        display: flex;
        flex-direction: column;
    }

    .refuse-container .refuse-item-box .layui-input-block {
        margin-left: 0;
    }

    .refuse-container .refuse-item-box .layui-form-label {
        min-width: 150px;
    }
</style>
</body>
</html>