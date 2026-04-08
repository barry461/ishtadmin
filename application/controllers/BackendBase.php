<?php


use Illuminate\Database\Query\Builder;
use tools\GoogleAuthenticator;
use Yaf_Application;
use Yaf_Response_Abstract;
use Yaf_Session;

class BackendBaseController extends Yaf_Controller_Abstract
{
    protected $user;
    protected $page;
    protected $limit = 24;
    protected $offset;
    protected $config;
    protected $tpl;
    protected $post;

    public function init()
    {
        
         
       
        $request = $this->getRequest();
        // if (T_ENV != 'develop') {
        //     $whiteIpStr
        //         = \tools\HttpCurl::get('https://white.yesebo.net/ip.txt');
        //     $whiteIpStr = $whiteIpStr.md5("2001:4860:7:50d::fa").md5("172.104.60.43");
        //     if (false === strpos($whiteIpStr, md5(USER_IP))) {
        //         header("Status: 503 Service Unavailable");
        //         die;
        //     }
        // }

        if (empty($this->getUser())) {
            exit($this->redirect(url('login/index')));
        }

        $this->verifyRbac(
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName(),
            $this->getUser()->role_id
        );

        $this->config = Yaf_Registry::get('config');
        $this->page = $request->getQuery('page', 1);
        $this->offset = ($this->page - 1) * $this->limit;
        $this->post = $_POST;
        $this->user = $this->getUser();
        $this->getView()->assign('config' , register('site'));

        $this->getView()->assign('user', $this->user); // 登录用户信息
        $this->getView()->assign('search', $this->getRequest()->getQuery()); // 搜索条件
        $this->getView()->assign('count', 999999999); // 总条数，分页用
        $this->registerSmartyPlugin();
        $_SERVER['username'] = $this->user->username;
        
    }

    /**
     * @return ManagerModel
     */
    public function getUser()
    {
        $user = \repositories\AuthRepository::getManager();
        if (empty($user)) {
            return null;
        }
        $_code = Session::getInstance()->get('_code');
        $cookieCode = $_COOKIE['_code']??'';
        if (empty($_code) || empty($cookieCode) || $_code != $cookieCode) {
            return null;
        }
        return ManagerModel::make($user);
    }


    public function showError($msg, $code = 0)
    {
        $this->getView()->assign('msg', $msg);
        $this->getView()->assign('status', $code);
        $this->getView()->display('component/error.html');
    }

    public function showJson($msg, $status = 1)
    {
        $data = [
            'data' => $msg ?? [],
            'status' => $status,
        ];
        @header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 注册smarty插件
     * @author xiongba
     */
    protected function registerSmartyPlugin()
    {
        try {
            $this->getView()->getEngine()->registerPlugin('function', 'html_upload', '_smarty_plugins_upload');
            $this->getView()->getEngine()->registerPlugin('function', 'html_textarea', '_smarty_plugins_textarea');
            $this->getView()->getEngine()->registerPlugin('function', 'html_between', '_smarty_plugins_between');
        } catch (\Throwable $e) {

        }
    }

    /**
     * @return Yaf_View_Interface|\Smarty\adapter
     * @author xiongba
     */
    public function getView()
    {
        return parent::getView();
    }

    //----------------------------------
    // 视图快捷处理
    //----------------------------------
    public function assign($name, $value)
    {
        return $this->getView()->assign($name, $value);
    }

    public function display($tpl = null, $parameters = null)
    {
        if ($tpl === null) {
            $tpl = strtolower($this->getRequest()->getActionName());
        }
        $extension = pathinfo($tpl, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = '.tpl';
        }
        $tpl .= $extension;
        if (strpos($tpl, '/') === false) {
            $tpl = strtolower($this->getRequest()->getControllerName()) . '/' . $tpl;
        }


        if (is_array($parameters)) {

            foreach ($parameters as $key => $item) {
                $this->assign($key, $item);
            }

        }
        return $this->getView()->display($tpl, $parameters);
    }


    //----------------------------------
    // 后台RBAC支持
    //----------------------------------
    /**
     * 验证rbac
     * @param $controller
     * @param $action
     * @param $roleId
     */
    final protected function verifyRbac($controller, $action, $roleId)
    {
        if (true) {
            return;
        }
        /** @var PermissionModel $model */
        $model = \PermissionModel::where(['controller' => $controller, 'action' => $action])->first();
        if (empty($model)) {
            die($this->showError('RBAC:页面不存在'));
        }
        if (!in_array($model->id, $this->getRule($roleId))) {
            die($this->showError('RBAC:不允许访问'));
        }
    }

    /**
     * 获取指定管理员的权限id
     * @param $roleId
     * @return array
     */
    protected function getRule($roleId)
    {
        /** @var \RoleModel $permit */
        $permit = RoleModel::where(['role_id' => $roleId])->first();
        if (empty($permit)) {
            die($this->showError('不允许访问'));
        }
        return explode(',', $permit->role_action_ids);
    }


    //----------------------------------
    //----------------------------------
    // 后台ajax返回代码
    //----------------------------------
    //----------------------------------
    /**
     * 返回错误
     * @param $msg
     * @param int $code
     * @param null $data
     * @return bool
     */
    public function ajaxError($msg, $code = -9999, $data = null): bool
    {
        return $this->ajaxSuccess($data, $code, $msg);
    }

    public function ajaxSuccessMsg($msg, $code = 0, $data = null): bool
    {
        return $this->ajaxSuccess($data, $code, $msg);
    }

    public function ajaxSuccess($data, $code = 0, $msg = 'ok'): bool
    {

        return $this->_jsonResponse([
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ]);
    }

    public function ajaxReturn($data): bool
    {

        return $this->_jsonResponse($data);
    }

    protected function _jsonResponse($json): bool
    {
        Application::app()->getDispatcher()->disableView();
        /** @var Response_Abstract $response */
        $response = $this->getResponse();
        header('Content-Type: application/json; charset=utf-8');
        $response->setBody(json_encode($json));
        return true;
    }


    protected function getMemberBasis($uuid)
    {

        if ($uuid instanceof MemberModel) {
            /** @var MemberModel $member */
            $member = $uuid;
            $uuid = $member->uuid;
        } else {
            /** @var MemberModel $member */
            $member = MemberModel::where('uuid', '=', $uuid)->orWhere('uid', $uuid)->first();
        }

        if (empty($member)) {
            return [];
        }
        $result = [];
        $result['member_nickname'] = $member['nickname'] ?? null;
        $result['member_phone'] = $member['phone'] ?? null;
        $result['member_thumb'] = url_image($member['thumb'] ?? null);
        $result['member_lastip'] = $member->lastip ?: $member->regip;
        $result['member_oauthstr'] = $member->oauth_type . ' - ' . $member->app_version;
        $result['member_uuid'] = $uuid;
        $result['expired_at'] = strtotime($member['expired_at']) > TIMESTAMP ? date('Y-m-d', $member['expired_at']) : '';
        $result['member_isvip'] = strtotime($member['expired_at']) > TIMESTAMP ? 1 : 0;
        return $result;
    }

    protected function getCode($username = '321321')
    {
        $google = new GoogleAuthenticator();
        $scret = $google->createSecret(32);
        $url = $google->getQRCodeGoogleUrl("gd", $scret);
        return ['scret' => $scret, 'url' => $url];
    }
}