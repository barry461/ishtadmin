{layout name="@layout/popup-ajax" /}
<div class="login-modal-wrapper">
    <div class="modal-content-wrapper">
        <div class="modal-content flex-row">
            <div class="modal-left d-none d-sm-block"></div>
            <div class="modal-right">
                <div class="modal-header">
                    <h4>登入以继续</h4>
                    <p>登入以享受会员福利，包括降低广告频率、观看会员专属影片及一键收藏功能。</p>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger w-100" role="alert" style="display:none"></div>
                    <div class="form-group-attached">
                        <div class="form-group required">
                            <label for="username">帐号</label>
                            <input id="login_user" name="login_user" class="form-control" type="text" autocomplete="username">
                        </div>
                        <div class="form-group required">
                            <label for="pass">密码</label>
                            <input id="login_pass" name="login_pass" class="form-control" type="password" autocomplete="current-password">
                        </div>
                    </div>
                    <div class="switch-group">
                        <label class="mb-0 w-100" for="remember_me">记住朕</label>
                        <span class="input-switch">
                            <input id="remember_me" name="remember_me" type="checkbox" value="1" checked>
                            <label for="remember_me"><i></i></label>
                        </span>
                    </div>
                    <input name="action" value="login" type="hidden">
                    <button class="btn btn-submit btn-block" type="button" id="login_submit">登入</button>
                    <a data-fancybox="ajax" class="float-right d-flex bind_register pl-1 mb-0 w-100" href="/register">注册只需 30 秒</a>
                </div>
            </div>
        </div>
    </div>
</div>
