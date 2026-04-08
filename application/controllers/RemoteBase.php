<?php


class RemoteBaseController extends Yaf_Controller_Abstract
{
    /** @var MemberModel */
    protected $member; // 用户信息
    protected $config; // 配置信息

    // 分页参数
    protected $page;
    protected $last_ix;
    protected $limit;
    protected $offset;
    protected $version;
    protected $position; // 位置信息

    protected $redis;
    protected $data = [];
    protected $uid;

    protected $oauth_id;
    protected $oauth_type;

    /**
     * @throws Exception
     */
    public function init()
    {
        if(empty($_POST)) $_POST = $_REQUEST;
        $this->data = &$_POST;
        $this->initGlobalParam();
        $this->initMember();
        $this->initPagination(); // 分页参数
    }

    protected function initGlobalParam(){
        if (!is_array($_POST)) {
            $_POST = [];
        }
        $this->data = &$_POST;
        $uri = sprintf("%s/%s", $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName());
        if (0 === strcasecmp($uri, 'remote/config') || 0 === strcasecmp($uri, 'remote/snsstat')) {
            $this->data['checkToken'] = false;
        } else {
            $this->data['checkToken'] = true;
        }
        // 位置信息
        $this->position = IP_POSITION;
        $this->version = $this->data['version'] ?? '';
        $this->oauth_id = $this->data['oauth_id'] ?? '';
        $this->oauth_type = $data['oauth_type'] ?? '';
    }

    protected function initPagination()
    {
        $this->limit = $this->data['limit'] ?? 10;
        $this->page = min(max($this->data['page'] ?? 1, 1), 500);
        $this->offset = ($this->page - 1) * $this->limit;
        $this->last_ix = $this->data['last_ix'] ?? null;
        if (empty($this->last_ix)) {
            $this->last_ix = null;
        }
    }


    protected function initMember()
    {
        $this->version = $data['version'] ?? '';
        $token = $this->data['token'] ?? '';
        if ($token && $this->data['checkToken']) {
            $crypt = new LibCryptUser();
            $tokenInfo = $crypt->decryptToken($token);
            if (empty($tokenInfo)) {
                throw new \Exception('token无效', 422);
            } else {
                $this->uid = $tokenInfo[0];
            }
        }
        $uri = sprintf("%s/%s", $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName());
        if ($uri == 'remote/loginByPassword') {
            if (IP_POSITION['country'] == '中国' && IP_POSITION['province'] != '香港'){
                $log = IP_POSITION;
                $log['cf_country'] = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
                $log['uid'] = $this->uid ?? 0;
                $log['account'] = $this->data['username'] ?? '';
                error_log(var_export($log, true) . PHP_EOL, 3, APP_PATH . '/storage/logs/remote-error.log');
                $this->failMsg('登录错误 1001', 0, []);
                $this->getResponse()->response();
                exit();
            }
        }
    }

    /**
     * 返回含有last_idx的列表
     *
     * 参数传递 两种方式传递参数等效
     *  $this->listJson($list , string $column ,array $extra)
     *  $this->listJson($list , array $extra ,string $column)
     *
     * 返回事例
     * ```php
     * merge( [
     *     'list' : $list,
     *     'last_idx' : last($list)[id],
     * ] , $extra )
     * ```
     *
     *
     * @param $list
     * @param string|array $column
     * @param array|string $extra
     *
     * @return bool
     */
    public function listJson($list, $column = 'id', $extra = []): bool
    {
        if (is_array($column)) {
            // 当column参数是数组时候，交换column和extra的值，
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
            'list'    => $list,
            'last_ix' => (string)$last_idx,
        ], $extra);

        return $this->showJson($ret);
    }

    /**
     * 返回数据
     *
     * @param $data
     * @param int $status
     * @param string $msg
     *
     * @return bool
     */
    public function showJson(
        $data,
        int $status = 1,
        string $msg = ''
    ): bool {
        $this->getResponse()->setHeader('Content-Type' , 'application/json');

        $data = replace_share(json_encode($data));
        $replace = setting('global_replace', '');
        $replaces = json_decode($replace, true);
        if (json_last_error() == JSON_ERROR_NONE){
            $keys = array_keys($replaces);
            $values = array_values($replaces);
            $data = str_replace($keys, $values, $data);
        }
        $data = json_decode($data, 1);

        $returnData = [
            'data'   => $data,
            'status' => $status,
            'msg'    => $msg,
            'crypt'  => true,
        ];
        $crypt = new LibCrypt();
        $returnData = $crypt->replyDataPwa($returnData);
        $this->getResponse()->setBody($returnData);

        return true;
    }

    public function getResponse()
    {
        static $run = null;
        if ($run){
            return $this->_response;
        }
        $run = true;
        // 移除测试相关代码，避免加载不存在的测试类
        return $this->_response;
    }

    public function successMsg($msg): bool
    {
        return $this->showJson('', 1, $msg);
    }

    public function failMsg($msg, $code = 0, $data = []): bool
    {
        return $this->showJson($data, $code, $msg);
    }

    public function errorJson($msg, $code = 0, $data = []): bool
    {
        return $this->failMsg($msg, $code, $data);
    }

}
