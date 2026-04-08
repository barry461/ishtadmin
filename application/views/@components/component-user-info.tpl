<!-- 个人中心/我的档案 -->

<div class="mine-user-info-container">
    <div class="page-title">我的档案</div>

    <div class="user-info-content">
        <div class="page-sub-title user-info-title">账号信息</div>
        <div class="info-row">
            <div class="info-label">用户名</div>
            <div class="info-name">else123</div>
        </div>

        <div class="page-sub-title">修改密码</div>
        <div class="input-label">新密码</div>
        <div class="input-content">
            <input type="password" v-model="newPassword" placeholder="请输入新密码" />
        </div>
        <div class="input-label">确认新密码</div>
        <div class="input-content">
            <input type="password" v-model="confirmPassword" placeholder="请再次输入新密码" />
        </div>
        <div class="input-label">旧密码</div>
        <div class="input-content">
            <input type="password" v-model="oldPassword" placeholder="请输入旧密码" />
        </div>
        <div class="submit-btn" @click="handleChangePassword">更新</div>
    </div>
</div>
