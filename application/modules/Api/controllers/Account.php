<?php

use service\UserService;
use service\CommonService;
use service\ChannelService;
use Tbold\Serv\biz\BizAppVisit;

class AccountController extends BaseController
{
    public function registerByPasswordAction(): bool
    {
        $Validator = \helper\Validator::make($this->data, [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $username = $this->data['username'];
        $is_email = validateEmail($username);
        if (!$is_email){
            if (!preg_match('/^([a-zA-Z0-9]{5,19})+$/', $username)) {
                return $this->errorJson('用户名不对');
            }
        }
        $invitedAff = $this->data['invitedAff'] ?? '';
        try {
            $member = $this->member->refresh();
            if (!empty($member->username) || !empty($member->email)) {
                return $this->errorJson('您已经登录了，注册失败');
            }
            if (in_array(strtolower($username), ['channel', 'windows', 'window', 'self', 'admin', 'android', 'ios', 'pwa', 'web', 'pc', 'proxy'])) {
                return $this->errorJson('系统保留账号。注册失败');
            }
            $password = MemberModel::generatePassword($this->data['password']);
            $rs = MemberModel::createAccount($member, $username, $password, $invitedAff, $is_email);

            //上报渠道V2数据
            ChannelService::reportVisit($member,USER_IP,BizAppVisit::ID_REG);

            return $this->showJson(...$rs);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function loginByPasswordAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $username = $this->data['username'];
        $password = MemberModel::generatePassword($this->data['password']);
        $is_email = validateEmail($username);

        try {
            if ($is_email){
                $member = MemberModel::findByEmail($username);
            }else{
                $member = MemberModel::findByUsername($username);
            }
            if (empty($member) || $member->password != $password) {
                throw new \Exception('用户名或密码错误');
            }
            LoginLogModel::log($member->aff, $this->data['oauth_id'], $this->data['oauth_type']);
            $crypt = new LibCrypt();
            $token = $crypt->encryptToken($member->aff, $this->data['oauth_id'], $this->data['oauth_type']);

            //上报渠道V2数据
            ChannelService::reportVisit($member,USER_IP,BizAppVisit::ID_LOGIN);

            return $this->showJson($token);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function validateUsernameAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'username' => 'required',
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        try {
            $this->verifyFrequency();
            $member = MemberModel::findByUsername($this->data['username']);
            if (!empty($member)) {
                throw new \Exception('已经存在');
            }
            return $this->successMsg('可以使用');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function newValidateUsernameAction(){
        $Validator = \helper\Validator::make($this->data, [
            'username' => 'required',
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        try {
            $this->verifyFrequency();
            $username = $this->data['username'];
            $res = validateEmail($username);
            $is_email = 0;
            if ($res){
                $member = MemberModel::findByEmail($username);
                $is_email = 1;
            }else{
                $member = MemberModel::findByUsername($username);
            }
            if (!empty($member)) {
                throw new \Exception('已经存在');
            }
            return $this->showJson(['is_email' => $is_email], 1, '可以使用');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function sendEmailCodeAction(){
        $Validator = \helper\Validator::make($this->data, [
            'username' => 'required',
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        try {
            $this->verifyFrequency();
            $username = $this->data['username'];
            $type = 1;//注册绑定
            $res = validateEmail($username);
            if ($res){
                $member = MemberModel::findByEmail($username);
            }else{
                return $this->errorJson('请输入正确的邮箱地址');
            }
            if (!empty($member)) {
                throw new \Exception('邮箱地址已经存在');
            }
            //发送邮件
            CommonService::sendEmail($username, $this->member->aff, $type);
            return $this->successMsg('发送成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function validateEmailCodeAction(){
        try {
            $Validator = \helper\Validator::make($this->data, [
                'username' => 'required',
                'code' => 'required',
            ]);
            if ($Validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $this->verifyFrequency();
            $username = $this->data['username'];
            $type = 1;//注册绑定
            $code = $this->data['code'];
            $res = validateEmail($username);
            test_assert($res, '请输入正确的邮箱地址');
            //验证邮箱验证码
            CommonService::validatorCode($username, $this->member->aff, $type, $code);
            return $this->successMsg('发送成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function bindEmailAction(){
        try {
            $Validator = \helper\Validator::make($this->data, [
                'email' => 'required',
                'code' => 'required',
            ]);
            if ($Validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $this->verifyFrequency();
            $email = $this->data['email'];
            $type = 1;//注册绑定
            $code = $this->data['code'];
            $res = validateEmail($email);
            test_assert($res, '请输入正确的邮箱地址');
            test_assert($this->member->isReg(),'注册用户才能绑定邮箱');
            test_assert(!$this->member->isBindEmail(), '邮箱已经绑定');
            $exist = MemberModel::emailExist($email);
            if ($exist){
                test_assert(false, '该邮箱已被使用');
            }
            //验证码是否正确
            CommonService::validatorCode($email, $this->member->aff, $type, $code);
            //绑定邮箱
            MemberModel::bindEmail($this->member, $email);
            return $this->successMsg('绑定成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function emailExistAction(){
        try {
            $Validator = \helper\Validator::make($this->data, [
                'email' => 'required',
            ]);
            if ($Validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $this->verifyFrequency();
            $email = $this->data['email'];
            $res = validateEmail($email);
            test_assert($res, '请输入正确的邮箱地址');
            //验证邮箱是否存在
            $rs = MemberModel::emailExist($email);
            if ($rs){
                return $this->successMsg('存在');
            }else{
                return $this->errorJson('不存在');
            }
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function changeEmailAction(){
        try {
            $Validator = \helper\Validator::make($this->data, [
                'email' => 'required',
                'code'  => 'required',
            ]);
            if ($Validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $member = $this->member;
            $this->verifyFrequency();
            $email = $this->data['email'];
            $code = $this->data['code'];
            $res = validateEmail($email);
            test_assert($res, '请输入正确的邮箱地址');
            test_assert($member->email, '未绑定邮箱');
            //验证码验证
            CommonService::validatorCode($email, $member->aff, 1, $code);
            //验证邮箱是否存在
            $rs = MemberModel::emailExist($email);
            if ($rs){
                test_assert(false, '此邮箱地址已经被使用');
            }
            //更换邮箱地址
            if ($member->username == $member->email){
                $member->username = $email;
                $member->oauth_id = $email;
            }
            $member->email = $email;
            $isOk = $member->saveOrFail();
            test_assert($isOk, '改绑邮箱失败，请重试');
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}
