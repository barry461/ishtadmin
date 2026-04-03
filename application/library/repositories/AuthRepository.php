<?php

namespace repositories;


use tools\RedisService;
use Yaf\Session;

class AuthRepository
{
    /**
     * 获取用户信息
     * @return bool|mixed
     */
    public static function getManager()
    {
        $user = Session::getInstance()->get('manager');
        if (empty($user)) {
            trigger_log("管理后台在线检测 用户信息丢失---- ".client_ip() );
            return false;
        }

        if ($user['lastip'] != client_ip()) {
//            trigger_log("管理后台在线检测{$user['username']} IP----{$user['lastip']},". client_ip() );
//            return false;
        }
        if (TIMESTAMP - $user['lastvisit'] > 7200*6) {
            return false;
        }
        return $user;
    }

    /**
     * login out
     * @return bool
     */
    public static function handleLoginOut()
    {
        Session::getInstance()->del('manager');
        return true;
    }
}