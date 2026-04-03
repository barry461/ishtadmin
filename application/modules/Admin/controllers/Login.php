<?php


use Yaf\Session;
use tools\GoogleAuthenticator;
class LoginController extends BackendBaseController
{

    public function init()
    {
        // $whiteList = explode(',',\tools\HttpCurl::get('https://white.yesebo.net/ip.txt'));
        // if (\Yaf\Application::app()->environ() === 'product' && !in_array(md5(USER_IP),$whiteList)) {
        //     die(header('Status: 503 Service Unavailable'));
        // }
        Session::getInstance();
    }


    /**
     * 登录页面
     */
    public function indexAction()
    {
        //print_r($this->getCode('yangguofu'));die();
        $this->display();
    }


    public function doLoginAction()
    {

       
        $username = $this->getRequest()->getPost('user_name');
        $password = $this->getRequest()->getPost('password');

        $model = ManagerModel::login($username);
        if (empty($model)) {
            return $this->ajaxError('账号或密码错误');
        }
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('访问错误');
        }
        $card_number = $this->getRequest()->getPost('card_num');
        if (empty($card_number) || strlen($card_number) != 6) {
            return $this->ajaxError("动态碼有誤~");
        }

        if ('product' == T_ENV ) {
            $googleAuthor = new GoogleAuthenticator();
            $secret = $model->secret;
            if (!$secret) {
                return $this->ajaxError('请先绑定动态码');
            }
            $secretCheck = $googleAuthor->verifyCode($secret, $card_number, 1);
            if (!$secretCheck) {
                $key = 'manager_lock_'.$model->username;
                if(redis()->setnx($key,0)){
                    redis()->expire($key,300);
                }
                if (redis()->incr($key) > 3) {
                    ManagerModel::where('uid', $model->uid)->update([
                        'newpm' => 1,
                    ]);
                    return $this->ajaxError('登陆被禁止,账号异常，联系管理员');
                }

                return $this->ajaxError('动态碼有誤,稍后重试');
            }
        }
        if ($model->newpm) {
            return $this->ajaxError('账号已被禁用');
        }

        if (!RoleModel::find($model->role_id)) {
            return $this->ajaxError('账号权限不对');
        }

        if (!$model->verifyPassword($password)) {
            return $this->ajaxError('账号或密码错误');
        }

        $_SERVER['username'] = $model->username;
        
        $model->updateLoginStatus();
       
        Session::getInstance()->set('manager', $model->toArray());
        Session::getInstance()->set('_code', md5($card_number));
        $domain = explode(':', $_SERVER['HTTP_HOST'])[0]; // 去掉端口
        setcookie('_code',md5($card_number),0,'/',$domain,false,true);
        $this->ajaxSuccess(url('index/index'));

    }

    public function logoutAction()
    {
        \repositories\AuthRepository::handleLoginOut();
        return $this->redirect(url('index'));
    }
}