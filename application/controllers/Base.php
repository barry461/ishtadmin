<?php


class BaseController extends Yaf_Controller_Abstract
{
    /** @var MemberModel */
    protected $member; // 用户信息
    protected $member_key; // 用户在redis中的的key
    protected $config; // 配置信息

    // 分页参数
    protected $page;
    protected $last_ix;
    protected $limit;
    protected $offset;
    protected $isVip;
    protected $debug;
    protected $version;
    protected $position; // 位置信息

    protected $redis;
    protected $data = [];
    protected $pwa;

    public function init()
    {
        $this->initGlobalParam();
        // $this->checkOauthId();
        $this->initMember();
        $this->isVip = $this->member->vip_level > 0;
        $this->ipBreaker();
        $this->initPagination(); // 分页参数
        // $this->verifyBanIp(USER_IP);
        $this->checkAuth();
        //设置用户权限
        if (!defined('USER_PRIVILEGE')) {
            //防止 forward 调用的时候重复声明
            $userPrivilege = cached(UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE . $this->member->aff)
                ->fetchPhp(function () {
                    return UserPrivilegeModel::getUserPrivilege($this->member);
                });
            define('USER_PRIVILEGE', $userPrivilege);
        }
        //添加api点击次数
//        $host = $_SERVER['HTTP_HOST'] ?? '';
//        if ($host){
//            \SysTotalModel::incrBy("api:{$host}");
//        }
    }

    protected function ipBreaker()
    {
        $whiteList = ['139.162.63.4', '172.20.0.0/16', '139.162.27.147', '172.104.60.98', '143.42.77.60'];
        if (in_network(USER_IP, $whiteList)) {
            return;
        }
        $uri = $this->data['route_uri'];
        # 禁止刷子
        $setKey = 'brush_ip';
        if (redis()->sIsMember($setKey, USER_IP)) {
            $msg = '已禁-请求:'.$uri.PHP_EOL;
            $msg .= '已禁-IP:'.USER_IP.PHP_EOL;
            $msg .= '已禁-参数:'.PHP_EOL.var_export($_POST, true).PHP_EOL;
            trigger_log($msg);
            header("Status: 503 Service Unavailable");
            exit();
        }
        if (empty($this->member)){
            return ;
        }

        # 刷子IP禁止
        # 1个IP下 30分钟 有超过5个安卓账户或者30个web账户就禁止或者ios 30个账户
        $aff = $this->member->aff;
        $regDate = strtotime($this->member->regdate);
        $oauthType = $this->member->oauth_type;

        if ($regDate > strtotime('today')) {
            $lockKey = 'brush_ip_'.$oauthType.'_'.USER_IP;

            $affs = redis()->get($lockKey);
            $affs = explode(",", $affs);
            $affs[] = $aff;
            $affs = array_filter(array_unique($affs));

            $num = $oauthType == 'android' ? 10 : 50;
            // 一个IP 拥有3个账户就直接禁止
            if (count($affs) > $num) {
                $msg = '禁止-请求:'.$uri.PHP_EOL;
                $msg .= '禁止-参数:'.PHP_EOL.var_export($_POST, true).PHP_EOL;
                $msg .= '禁止-添加:'.$oauthType.' IP禁止:'.USER_IP.PHP_EOL;
                $msg .= '禁止-AFF:'.implode(",", $affs).PHP_EOL;

                trigger_log($msg);
                redis()->sadd($setKey, USER_IP);
                redis()->del($lockKey);
                header("Status: 503 Service Unavailable");
                exit();
            } else {
                if (redis()->exists($lockKey)) {
                    redis()->setex($lockKey, redis()->ttl($lockKey), implode(",", $affs));
                } else {
                    redis()->setex($lockKey, 1800, implode(",", $affs));
                }
            }
        }
    }

    protected function checkOauthId()
    {
        $setKey = 'brush_oauth_id';
        if (redis()->sIsMember($setKey, $this->data['oauth_id'])) {
            header("Status: 503 Service Unavailable");
            exit();
        }
    }

    protected function initGlobalParam(){
        if (!is_array($_POST)) {
            $_POST = [];
        }
        $this->data = &$_POST;
        $uri = sprintf("%s/%s", $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName());
        if (0 === strcasecmp($uri, 'home/config')) {
            $this->data['checkToken'] = false;
        } else {
            $this->data['checkToken'] = true;
        }
        $this->data['route_uri'] = $uri;
        // 位置信息
        $this->position = IP_POSITION;
        $this->debug = $this->data['debug'] ?? 0;
        $this->pwa = $this->data['via'] ?? '';
        $this->version = $this->data['version'] ?? '';
    }

    protected function initPagination()
    {
        $this->limit = $this->data['limit'] ?? 20;
        $this->page = min(max($this->data['page'] ?? 1, 1), 500);
        $this->offset = ($this->page - 1) * $this->limit;
        $this->last_ix = $this->data['last_ix'] ?? null;
        if (empty($this->last_ix)) {
            $this->last_ix = null;
        }
    }


    protected function initMember()
    {
        // 获取用户信息
        try {
            $member = new LibMember($this->data);
            $this->member_key = $member->redisKey;
            $this->member = $member->fetchMember();
        } catch (\Illuminate\Database\QueryException|\Doctrine\DBAL\Driver\PDO\Exception $e) {
            if ($e->getCode() != 23000 || empty($member)) {
                throw $e;
            }
            $this->member = $member->findMemberInWritePdo();
        } catch (\Throwable $e) {
            trigger_log($e);
            $this->failMsg($e->getMessage(), 0, []);
            $this->getResponse()->response();
            exit();
        }
        //trigger_log(json_encode($this->data));
    }

//    protected function privilege(): \tools\LibPrivilege
//    {
//        static $object = null;
//        if ($object === null) {
//            $object = new \tools\LibPrivilege($this->member, USER_PRIVILEGE);
//        }
//
//        return $object;
//    }

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
        // @header('Content-Type: application/json');
        /*
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $imgHost = [
            parse_url(TB_IMG_PWA_CN, PHP_URL_HOST),
            parse_url(TB_IMG_PWA_US, PHP_URL_HOST),
        ];
        $replaceHost = parse_url('https://newh5.niqcaok.cn', PHP_URL_HOST);
        $data = str_replace($imgHost, $replaceHost, $data);
        //$data = $this->covertt2s($data);
        $data = json_decode($data, true);
        */

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
            'isVip'  => $this->isVip,
        ];

        if (isset($_POST["debug"]) && $_POST["debug"]=="fasdf4ed@1`!" && DEBUG == true){
            $this->getResponse()->setBody(json_encode($returnData,320));
        }else{
            $crypt = new LibCrypt();
            $returnData = $crypt->replyDataPwa($returnData);
            $this->getResponse()->setBody($returnData);
        }
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

    /**
     * @throws Exception
     */
    protected function verifyMemberSayRole()
    {
        if ($this->member->isMuteRole()) {
            throw new RuntimeException('您已被禁言');
        }
    }

    /**
     * @throws Exception
     */
    protected function verifyIpBan()
    {
        if (redis()->sIsMember('ip:ban', client_ip())) {
            throw new Exception('您已被禁言');
        }
    }

    protected function addToIpban($ip)
    {
        redis()->sAdd('ip:ban', $ip);
    }

    /**
     * @throws Exception
     */
    protected function verifyFeeVip()
    {
        if (!$this->member->isFeeVip()) {
            throw new \Exception('只有收费会员才能操作');
        }
    }

    /**
     * @param BaseModel|string $model
     * @param $word
     */
    protected function verifySearchFrequency($model, $word)
    {
        if (is_string($model)) {
            $table = $model;
        } else {
            $table = $model->getTable();
        }
        $key = "search:$table:".$this->member->aff;
        $tmp = redis()->get($key);
        if (!empty($tmp) && $tmp != $word) {
            throw new RuntimeException('两次搜索间隔不能小于5秒');
        }
        redis()->set($key, $word, 5);
    }

    /**
     * 限制频率
     *
     * @param int $ttl 多长时间类
     * @param int $num 访问多少次
     * @param string $prefix
     * @param string $msg
     *
     */
    protected function verifyFrequency(
        int $ttl = 1,
        int $num = 1,
        string $prefix = '',
        string $msg = '您操作太快了，休息一下再来'
    ) {
        // $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT
        //     | DEBUG_BACKTRACE_IGNORE_ARGS);
        // if (!isset($debug[1])) {
        //     return;
        // }
        // if ($ttl == 1) {
        //     $ttl = 10;
        //     $num = 10;
        // }
        // $hash = md5($debug[0]['file'].$debug[0]['line']);
        // $key = 'fr:'.$this->member->aff.':'.($prefix ? $prefix.':' : '').$hash;
        // $tmp = redis()->incrBy($key, 1);
        // if ($tmp > $num) {
        //     throw new RuntimeException($msg);
        // }
        // if ($tmp <= 1) {
        //     redis()->expire($key, $ttl);
        // }
    }

    protected function verifyIpFrequency(
        int $ttl = 1,
        int $num = 1,
        string $msg = '您操作太快了，休息一下再来'
    ) {
        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT
            | DEBUG_BACKTRACE_IGNORE_ARGS);
        if (!isset($debug[1])) {
            return;
        }
        if ($ttl == 1) {
            $ttl = 10;
            $num = 10;
        }
        $ip = client_ip();
        if (in_array($ip, ['127.0.0.1', 'unknown'])) {
            return;
        }
        $hash = md5($ip.$debug[0]['file'].$debug[0]['line']);
        $key = 'ipfr:'.$hash;
        $tmp = redis()->incrBy($key, 1);
        if ($tmp > $num) {
            throw new RuntimeException($msg);
        }
        if ($tmp <= 1) {
            redis()->expire($key, $ttl);
        }
    }

    private function checkAuth()
    {
        if ($this->member->role_id == MemberModel::ROLE_BAN) {
            header("Status: 503 Service Unavailable");
            exit();
        }
    }

    private function verifyBanIp($ip)
    {
        if (redis()->sIsMember(BAN_IPS_KEY, $ip)) {
            $msg = '已禁-IP:' . $ip . PHP_EOL;
            $msg .= '已禁-参数:' . PHP_EOL . var_export($_POST, true) . PHP_EOL;
            header("Status: 503 Service Unavailable");
            exit();
        }
    }

}
