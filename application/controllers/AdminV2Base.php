<?php

/*
 * @package application\controllers
 * @author chenmoyuan
 * @version 1.0.0
 * @date 2025-12-24
 * @description 管理后台V2基础控制器
 */

class AdminV2BaseController extends \Yaf\Controller_Abstract
{
    // 状态码
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 0;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_VALIDATION_ERROR = 422;
    const STATUS_SERVER_ERROR = 500;
    const STATUS_SERVICE_UNAVAILABLE = 503;

    protected $page;
    protected $last_ix;
    protected $limit;
    protected $offset;
    protected $data = [];
    protected $rawPost = []; // 原始加密的POST数据
    protected $user; // 当前登录的管理员

    public function init()
    {
        $this->initGlobalParam();
        $this->initPagination();


        $this->initAuth();


        // 自动记录操作日志（在权限验证后执行）
        $this->autoRecordAdminLog();
    }


    protected function initGlobalParam()
    {
        if (empty($_POST)) {
            $_POST = [];
        }
        if (empty($_GET)) {
            $_GET = [];
        }

        // 保存原始POST数据（用于调试）
        $this->rawPost = $_POST ? json_decode(json_encode($_POST), true) : [];

        // 检查 Content-Type，决定如何处理请求体
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // 如果是 JSON 格式，从 php://input 读取（php://input 只能读取一次）
        if (stripos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $jsonData = json_decode($rawInput, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $_POST = array_merge($_POST, $jsonData);
                    $this->rawPost = $jsonData;
                } else {
                    $this->rawPost = ['raw_input' => substr($rawInput, 0, 1000)];
                }
            }
        }

        // 检查$_POST是否包含加密数据格式（有data和sign字段）
        if (isset($_POST['data']) && isset($_POST['sign']) && APP_MODULE === 'adminv2') {
            try {
                $crypt = new LibCrypt();
                $decrypted = $crypt->checkInputDataPwa($_POST);
                if ($decrypted !== false && is_array($decrypted)) {
                    $_POST = $decrypted;
                }
            } catch (\Throwable $e) {
                // 解密失败，保持原始数据
            }
        }

        $this->data = array_merge($_GET, $_POST);
    }

    protected function initPagination()
    {
        // 获取 limit，支持空字符串的情况
        if (isset($this->data['limit']) && $this->data['limit'] !== '') {
            $this->limit = (int)$this->data['limit'];
        } else {
            $this->limit = 20;
        }
        $this->limit = min(max($this->limit, 1), 100);

        // 获取 page，支持空字符串的情况
        if (isset($this->data['page']) && $this->data['page'] !== '') {
            $this->page = (int)$this->data['page'];
        } else {
            $this->page = 1;
        }
        $this->page = min(max($this->page, 1), 9999);

        $this->offset = ($this->page - 1) * $this->limit;
        $this->last_ix = $this->data['last_ix'] ?? null;

        if (empty($this->last_ix)) {
            $this->last_ix = null;
        }
    }

    protected function initAuth()
    {
        // IP白名单验证
        // if (\Yaf\Application::app()->environ() === 'product') {// 生产环境
        //     $whiteListStr = \tools\HttpCurl::get('https://white.yesebo.net/ip.txt');
        //     if ($whiteListStr !== false && !empty($whiteListStr)) {
        //         $whiteList = explode(',', $whiteListStr);
        //         $whiteList = array_filter(array_map('trim', $whiteList)); // 去除空值和空格
        //         if (!empty($whiteList) && !in_array(md5(USER_IP), $whiteList)) {
        //             $this->sendErrorResponse([], self::STATUS_SERVICE_UNAVAILABLE, '禁止访问');
        //             return;
        //         }
        //     }
        // }
        if ($this->getRequest()->getMethod() == 'OPTIONS') {
            return;
        }

        // 登录接口跳过权限验证
        $controller = strtolower($this->getRequest()->getControllerName());
        if ($controller == 'login' || $controller == 'upload') {
            return;
        }

        // 获取 token
        $token = $this->getToken();

        if (empty($token)) {
            $this->sendUnauthorizedResponse('请先登录');
            return;
        }

        // 解密token获取管理员信息
        $userInfo = $this->verifyToken($token);
        if (!$userInfo) {
            $this->sendUnauthorizedResponse('Token 无效或已过期');
            return;
        }

        list($uid, $username, $type) = $userInfo;

        // 获取管理员信息
        $user = ManagerModel::find($uid);
        if (empty($user)) {
            $this->sendUnauthorizedResponse('管理员不存在');
            return;
        }

        // 验证账号状态
        if ($user->newpm) {
            $this->sendForbiddenResponse('账号已被禁用');
            return;
        }

        // 验证角色权限
        if (!RoleModel::find($user->role_id)) {
            $this->sendForbiddenResponse('账号权限不对');
            return;
        }

        // 验证权限
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $this->verifyPermission($controller, $action, $user->role_id);

        $this->user = $user;
        $_SERVER['username'] = $user->username;
    }

    /**
     * 在 init 阶段直接发送未授权响应
     */
    protected function sendUnauthorizedResponse(string $msg = '未授权访问')
    {
        $this->sendErrorResponse([], self::STATUS_UNAUTHORIZED, $msg);
    }

    /**
     * 在 init 阶段直接发送禁止访问响应
     */
    protected function sendForbiddenResponse(string $msg = '禁止访问')
    {
        $this->sendErrorResponse([], self::STATUS_FORBIDDEN, $msg);
    }

    /**
     * 在 init 阶段直接发送错误响应并退出
     */
    protected function sendErrorResponse($data, int $status, string $msg)
    {
        http_response_code($status);

        // 设置CORS响应头
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        @header('Access-Control-Allow-Origin: ' . $origin);
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        @header('Access-Control-Allow-Headers: Content-Type, Authorization, Token, X-Requested-With');
        @header('Access-Control-Max-Age: 86400');
        @header('Content-Type: application/json; charset=utf-8');

        $data = replace_share(json_encode($data));
        $replace = setting('global_replace', '');
        $replaces = json_decode($replace, true);
        if (json_last_error() == JSON_ERROR_NONE && is_array($replaces)) {
            $keys = array_keys($replaces);
            $values = array_values($replaces);
            $data = str_replace($keys, $values, $data);
        }
        $data = json_decode($data, true);

        $returnData = [
            'data' => $data,
            'status' => $status,
            'msg' => $msg,
            'crypt' => true,
        ];

        if (isset($this->data["debug"]) && $this->data["debug"] == "fasdf4ed@1`!" && defined('DEBUG') && DEBUG == true) {
            echo json_encode($returnData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $crypt = new LibCrypt();
            $returnData = $crypt->replyDataPwa($returnData);
            echo $returnData;
        }
        exit;
    }

    protected function getToken()
    {
        // 从 header 获取 token
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $auth = $headers['Authorization'];
                if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                    return $matches[1];
                }
                return $auth;
            }
            if (isset($headers['Token'])) {
                return $headers['Token'];
            }
        } else {
            // 兼容没有 getallheaders 的环境
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth = $_SERVER['HTTP_AUTHORIZATION'];
                if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                    return $matches[1];
                }
                return $auth;
            }
            if (isset($_SERVER['HTTP_TOKEN'])) {
                return $_SERVER['HTTP_TOKEN'];
            }
        }

        // 从参数获取 token
        return $this->data['token'] ?? '';
    }

    protected function verifyToken($token)
    {
        if (empty($token) || strlen($token) < 10) {
            return false;
        }

        try {
            $tokenKey = config('encrypt.token_key');
            $tokenInfo = LibCrypt::decrypt($token, $tokenKey);
            if (empty($tokenInfo)) {
                return false;
            }

            $data = unserialize($tokenInfo);
            if (empty($data) || !is_array($data) || count($data) < 3) {
                return false;
            }

            list($uid, $username, $type) = $data;

            // 验证 token 是否在 Redis 中存在
            $existToken = redis()->hGet('manager:token', $uid);
            if ($token != $existToken) {
                return false;
            }

            return [$uid, $username, $type];
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function verifyPermission($controller, $action, $roleId)
    {
        // 登录接口不需要验证权限
        if (strtolower($controller) == 'login') {
            return;
        }

        // 查找权限
        $permission = PermissionModel::where([
            'controller' => $controller,
            'action' => $action
        ])->first();

        // 如果权限表中没有该权限，默认允许访问（兼容旧系统）
        if (empty($permission)) {
            return;
        }

        // 获取角色权限列表
        $role = RoleModel::find($roleId);
        if (empty($role)) {
            $this->sendForbiddenResponse('角色不存在');
            return;
        }

        $permissionIds = explode(',', $role->role_action_ids);
        $permissionIds = array_filter($permissionIds);

        // 验证是否有权限
        if (!in_array($permission->id, $permissionIds)) {
            $this->sendForbiddenResponse('没有权限访问');
            return;
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    // 自动记录操作日志
    protected function autoRecordAdminLog()
    {
        $controller = strtolower($this->getRequest()->getControllerName());
        $action = strtolower($this->getRequest()->getActionName());

        if ($controller == 'login') {
            return;
        }

        $user = $this->getUser();
        $username = '';
        if (!empty($user)) {
            $username = $user->username;
        } elseif (isset($_SERVER['username']) && !empty($_SERVER['username'])) {
            $username = $_SERVER['username'];
        } else {
            return;
        }

        $actionType = 'access';
        $logDesc = '';

        if (strpos($action, 'save') !== false || strpos($action, 'update') !== false || strpos($action, 'edit') !== false || strpos($action, 'set') !== false) {
            $actionType = 'updated';
            $logDesc = '更新了' . $controller . '数据';
        } elseif (strpos($action, 'create') !== false || strpos($action, 'add') !== false || strpos($action, 'insert') !== false) {
            $actionType = 'created';
            $logDesc = '创建了' . $controller . '数据';
        } elseif (strpos($action, 'delete') !== false || strpos($action, 'remove') !== false || strpos($action, 'del') !== false) {
            $actionType = 'deleted';
            $logDesc = '删除了' . $controller . '数据';
        } elseif (strpos($action, 'list') !== false || strpos($action, 'get') !== false || strpos($action, 'index') !== false) {
            $actionType = 'view';
            $logDesc = '查看了' . $controller . '列表';
        } else {
            $actionType = 'access';
            $logDesc = '访问了' . $controller . '/' . $action;
        }

        $requestData = [];
        if (!empty($this->data)) {
            $sensitiveFields = ['password', 'pwd', 'token', 'secret', 'key', 'card_num'];
            foreach ($this->data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $requestData[$key] = '***';
                } else {
                    if (is_string($value) && strlen($value) > 500) {
                        $requestData[$key] = substr($value, 0, 500) . '...';
                    } else {
                        $requestData[$key] = $value;
                    }
                }
            }
        }

        $logContext = [
            'controller' => $this->getRequest()->getControllerName(),
            'action' => $this->getRequest()->getActionName(),
            'module' => $this->getRequest()->getModuleName(),
            'method' => $this->getRequest()->getMethod(),
            'request_data' => $requestData,
            'time' => time(),
        ];

        $this->recordAdminLog($actionType, $logDesc, $logContext);
    }

    // 写入操作日志
    protected function recordAdminLog(string $action, string $log, array $context = [])
    {
        $user = $this->getUser();
        if (empty($user)) {
            $username = $_SERVER['username'] ?? '';
            if (empty($username)) {
                return;
            }
        } else {
            $username = $user->username;
        }

        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        $referrer = $_SERVER['HTTP_REFERER'] ?? $uri;
        $ip = defined('USER_IP') ? USER_IP : ($_SERVER['REMOTE_ADDR'] ?? '');

        $logContext = array_merge([
            'controller' => $this->getRequest()->getControllerName(),
            'action' => $this->getRequest()->getActionName(),
            'module' => $this->getRequest()->getModuleName(),
            'uri' => $uri,
            'time' => time(),
        ], $context);

        $logData = [
            'username' => $username,
            'action' => $action,
            'ip' => $ip,
            'log' => $username . ' ' . $log,
            'referrer' => $referrer,
            'context' => json_encode($logContext, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (function_exists('bg_run')) {
            bg_run(function () use ($logData) {
                try {
                    AdminLogModel::query()->insert($logData);
                } catch (\Throwable $e) {
                    trigger_log('AdminLog写入失败: ' . $e->getMessage());
                }
            });
        } else {
            try {
                $queue = 'jobs:work:queue';
                $serialized = serialize([function () use ($logData) {
                    try {
                        AdminLogModel::query()->insert($logData);
                    } catch (\Throwable $e) {
                        trigger_log('AdminLog写入失败: ' . $e->getMessage());
                    }
                }, []]);
                $data = json_encode([$serialized]);
                redis()->rPush($queue, $data);
            } catch (\Throwable $e) {
                try {
                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                        AdminLogModel::query()->insert($logData);
                    } else {
                        AdminLogModel::query()->insert($logData);
                    }
                } catch (\Throwable $e2) {
                    trigger_log('AdminLog写入失败: ' . $e2->getMessage());
                }
            }
        }
    }

    /**
     * @param array|\Illuminate\Support\Collection $list
     * @param string|array $column
     * @param array|string $extra
     */
    public function listJson($list, $column = 'id', $extra = [])
    {
        if (is_array($column)) {
            if (is_string($extra)) {
                list($extra, $column) = [$column, $extra];
            } else {
                list($extra, $column) = [$column, 'id'];
            }
        }

        $list = collect($list);
        $last_end = $list->last();

        if (is_array($last_end) || $last_end instanceof ArrayAccess) {
            $last_idx = $last_end[$column] ?? '0';
        } else {
            $last_idx = $last_end;
        }

        if (empty($last_idx)) {
            $last_idx = (string)$last_idx;
        }

        $ret = array_merge([
            'list' => $list,
            'last_ix' => (string)$last_idx,
        ], $extra);

        return $this->showJson($ret);
    }

    /**
     * @param array|\Illuminate\Support\Collection $list
     * @param int $total
     * @param array $extra
     */
    public function pageJson($list, $total = 0, $extra = [])
    {
        $ret = array_merge([
            'list' => $list,
            'total' => (int)$total,
            'page' => $this->page,
            'limit' => $this->limit,
            'pages' => $total > 0 ? (int)ceil($total / $this->limit) : 0,
        ], $extra);

        return $this->showJson($ret);
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param string $msg
     */
    public function showJson($data, int $status = self::STATUS_SUCCESS, string $msg = '')
    {
        // 设置CORS响应头
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        @header('Access-Control-Allow-Origin: ' . $origin);
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        @header('Access-Control-Allow-Headers: Content-Type, Authorization, Token, X-Requested-With');
        @header('Access-Control-Max-Age: 86400');

        // 处理OPTIONS预检请求
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        @header('Content-Type: application/json; charset=utf-8');

        $data = replace_share(json_encode($data));
        $replace = setting('global_replace', '');
        $replaces = json_decode($replace, true);
        if (json_last_error() == JSON_ERROR_NONE && is_array($replaces)) {
            $keys = array_keys($replaces);
            $values = array_values($replaces);
            $data = str_replace($keys, $values, $data);
        }
        $data = json_decode($data, true);

        $returnData = [
            'data' => $data,
            'status' => $status,
            'msg' => $msg,
            'crypt' => true,
        ];

        if (isset($this->data["debug"]) && $this->data["debug"] == "fasdf4ed@1`!" && defined('DEBUG') && DEBUG == true) {
            $this->getResponse()->setBody(json_encode($returnData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $crypt = new LibCrypt();
            $returnData = $crypt->replyDataPwa($returnData);
            $this->getResponse()->setBody($returnData);
        }

        return true;
    }

    public function successMsg(string $msg, $data = [])
    {
        return $this->showJson($data, self::STATUS_SUCCESS, $msg);
    }

    public function failMsg(string $msg, int $code = self::STATUS_ERROR, $data = [])
    {
        return $this->showJson($data, $code, $msg);
    }

    public function errorJson(string $msg, int $code = self::STATUS_ERROR, $data = [])
    {
        return $this->failMsg($msg, $code, $data);
    }

    public function unauthorized(string $msg = '未授权访问')
    {
        return $this->showJson([], self::STATUS_UNAUTHORIZED, $msg);
    }

    public function forbidden(string $msg = '禁止访问')
    {
        return $this->showJson([], self::STATUS_FORBIDDEN, $msg);
    }

    public function notFound(string $msg = '资源不存在')
    {
        return $this->showJson([], self::STATUS_NOT_FOUND, $msg);
    }

    public function validationError(string $msg = '参数验证失败', array $errors = [])
    {
        return $this->showJson(['errors' => $errors], self::STATUS_VALIDATION_ERROR, $msg);
    }

    public function serverError(string $msg = '服务器内部错误')
    {
        return $this->showJson([], self::STATUS_SERVER_ERROR, $msg);
    }

    public function getResponse()
    {
        static $run = null;
        if ($run) {
            return $this->_response;
        }
        $run = true;
        return $this->_response;
    }
}