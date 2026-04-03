<?php

use tools\GoogleAuthenticator;


class LoginController extends AdminV2BaseController
{
    public function indexAction()
    {
        return $this->showJson(['message' => 'success']);
        
    }

    public function doLoginAction()
    {
        $username = $this->data['username'] ?? '';
        $password = $this->data['password'] ?? '';
        $card_num = $this->data['card_num'] ?? '';

        // 调试信息：收集加密参数和解密后的参数
        $debugInfo = [
            'raw_post' => $this->rawPost, // 原始加密的POST数据
            'decrypted_data' => $this->data, // 解密后的数据
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'server_post' => $_POST ?? [], // 服务器接收到的POST数据
        ];

        if (empty($username) || empty($password)) {
            return $this->errorJson('用户名和密码不能为空', self::STATUS_ERROR, ['debug' => $debugInfo]);
        }

        $model = ManagerModel::login($username);
        if (empty($model)) {
            return $this->errorJson('账号或密码错误');
        }

        // 验证账号是否被禁用
        if ($model->newpm) {
            return $this->errorJson('账号已被禁用');
        }

        // 验证角色权限
        if (!RoleModel::find($model->role_id)) {
            return $this->errorJson('账号权限不对');
        }

        // 验证密码
        if (!$model->verifyPassword($password)) {
            return $this->errorJson('账号或密码错误');
        }

        // 生产环境验证动态码
        // 调试阶段可以通过 skip_verify=1 参数跳过动态码验证
        $skipVerify = isset($this->data['skip_verify']) && $this->data['skip_verify'] == 1;
        
        if ('product' == T_ENV && !$skipVerify) {
            if (empty($card_num) || strlen($card_num) != 6) {
                return $this->errorJson('动态码有误');
            }

            $googleAuthor = new GoogleAuthenticator();
            $secret = $model->secret;
            if (!$secret) {
                return $this->errorJson('请先绑定动态码');
            }

            $secretCheck = $googleAuthor->verifyCode($secret, $card_num, 1);
            if (!$secretCheck) {
                $key = 'manager_lock_' . $model->username;
                if (redis()->setnx($key, 0)) {
                    redis()->expire($key, 300);// 登陆过期时间，字段预留，后期可以写到配置中去
                }
                if (redis()->incr($key) > 3) {
                    ManagerModel::where('uid', $model->uid)->update([
                        'newpm' => 1,
                    ]);
                    return $this->errorJson('登录被禁止，账号异常，联系管理员');
                }
                return $this->errorJson('动态码有误，稍后重试');
            }
        }

        // 更新登录状态
        $model->updateLoginStatus();

        // 生成 token
        $tokenData = serialize([$model->uid, $model->username, 'admin', time()]);
        $tokenKey = config('encrypt.token_key');
        $token = LibCrypt::encrypt($tokenData, $tokenKey);
        redis()->hSet('manager:token', $model->uid, $token);

        // 返回用户信息和 token
        $userInfo = [
            'uid' => $model->uid,
            'username' => $model->username,
            'role_id' => $model->role_id,
            'role_type' => $model->role_type,
        ];

        // 添加调试信息到返回数据
        $debugInfo = [
            'raw_post' => $this->rawPost, // 原始加密的POST数据
            'decrypted_data' => $this->data, // 解密后的数据
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'headers' => [
                'authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? '',
                'token' => $_SERVER['HTTP_TOKEN'] ?? '',
            ],
        ];

        return $this->showJson([
            'token' => $token,
            'user' => $userInfo,
            'debug' => $debugInfo, // 调试信息
        ], self::STATUS_SUCCESS, '登录成功');
    }
}