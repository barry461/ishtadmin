<?php

use Illuminate\Support\Str;
use service\UserService;
use Carbon\Carbon;
use service\ChannelService;

class LibMember
{
    public $version;
    public $oauth_id;
    public $oauth_ads_id;
    public $oauth_type;
    public $uuid = '';
    public $build_affcode = '';
    public $redisKey;
    public $build_id;
    public $redis;
    public $aff;
    public $route_uri;

    function __construct($data)
    {
        $this->oauth_id = $data['oauth_id'] ?? '';
        $this->oauth_ads_id = $data['oauth_ads_id'] ?? '';
        $this->route_uri = strtolower($data['route_uri'] ?? '');

        // 如果有广告标示，oauth_id 为广告标示
        if (isset($data['oauth_new_id'])
            && !empty($data['oauth_new_id'])
            && $data['oauth_new_id'] != '00000000-0000-0000-0000-000000000000'
        ) {
            $this->oauth_id = $data['oauth_new_id'];
        }
        $this->build_id = $data['build_id'] ?? '';
        $this->build_affcode = $data['build_affcode'] ?? ''; // 渠道包支持
        $this->version = $data['version'] ?? '';
        $this->oauth_type = $data['oauth_type'] ?? '';
        $token = $data['token'] ?? '';
        if ($token && $data['checkToken']) {
            $crypt = new LibCrypt();
            $tokenInfo = $crypt->decryptToken($token);
            if (empty($tokenInfo)) {
                throw new \Exception('token无效', 422);
            } else {
                $this->aff = $tokenInfo[0];
            }
        }
        if ($this->oauth_id && $this->oauth_type) {
            $this->uuid = md5($this->oauth_type . $this->oauth_id);
            if ($this->aff) {
                $this->redisKey = MemberModel::USER_REIDS_PREFIX . $this->aff;
            } else {
                $this->redisKey = MemberModel::USER_REIDS_PREFIX . ($this->uuid);
            }
        }
    }

    /**
     * @return MemberModel|null|stdClass
     */
    public function findMemberInWritePdo()
    {
        return MemberModel::useWritePdo()
            ->where('oauth_id', $this->oauth_id)
            ->where('oauth_type', $this->oauth_type)
            ->first();
    }

    /**
     * @return MemberModel
     * @throws Throwable
     * @throws \Yaf_Exception
     */
    public function fetchMember(): MemberModel
    {
        if (empty($this->uuid)){
            trigger_log(json_encode($_SERVER));
            trigger_log(file_get_contents('php://input'));
            trigger_log(print_r($_POST, true));
            throw new RuntimeException('错误的请求');
        }

        $cacheObject = cached($this->redisKey)->prefix('')->suffix('')->serializerPHP()->expired(7200);

        /** @var MemberModel $member */
        $member = (clone $cacheObject)
            ->fetch(function () {
                $member = $this->getMember();
                if (empty($member)) {
                    throw new RuntimeException('获取用户失败'.':' .$this->oauth_id, 422);
                }
                return $member;
            });
        //拉黑的
        if ($member->role_id == MemberModel::ROLE_BAN) {
            header("Status: 503 Service Unavailable");
            exit();
        }

        // 更新用户版本信息
        if ($member->app_version != $this->version) {
            $member->app_version = $this->version;
        }

        // 更新用户本日剩余免费观看次数
//        if ($member->free_view_date != date('Y-m-d')) {
//            $member->setFreeNum((int)setting('free_view_cnt', 10));
//            $member->free_view_date = date('Y-m-d');
//        }

        $member->lastip = client_ip();

        if (strtotime($member->lastactivity) < strtotime(date('Y-m-d 00:00:00',TIMESTAMP))) {
            $this->updateSession($member);
        }

        if ($member->isDirty()) {
            //如果IP在1分钟变换10次，把oauthId拉入黑名单
            $key = "ip:update:num:%s:%s";
            $key = sprintf($key, $member->oauth_id, $member->oauth_type);
            $val = redis()->incrBy($key, 1);
            $val = intval($val);
            if (redis()->ttl($key) == -1){
                redis()->expire($key, 60);
            }
            if ($val >= 100){
                $setKey = 'brush_oauth_id';
                redis()->sAdd($setKey, $member->oauth_id);
                header("Status: 503 Service Unavailable");
                exit();
            }

            $member->save();
            $member->lastactivity = date('Y-m-d H:i:s', TIMESTAMP);
            $member->syncCached($this->redisKey);
        }
        return $member;
    }

    protected function riskControl(){
        if (!Str::contains($this->route_uri, ['home/config', 'home/click_report', 'user/userinfo', 'domaincheckreport'])) {
            $server = array_filter($_SERVER, function ($val) {
                return strpos($val, 'HTTP_') !== false;
            }, ARRAY_FILTER_USE_KEY);
            unset($server['HTTP_CF_VISITOR']
                , $server['HTTP_X_FORWARDED_PROTO']
                , $server['HTTP_CONNECTION']
                , $server['HTTP_ACCEPT_ENCODING']
                , $server['HTTP_CF_RAY']
                , $server['HTTP_CF_RAY']
            );
            $server['DOCUMENT_URI'] = $_SERVER['DOCUMENT_URI'] ?? '';
            error_log(json_encode([
                    'server' => $server,
                    'post'   => $_POST,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, 3, APP_PATH . '/storage/logs/add-user.log');
            redis()->sadd('brush_ip', USER_IP);
            header("Status: 503 Service Unavailable", true, 503);
            exit();
        }
    }

    /**
     * 获取用户
     *
     * @return MemberModel
     * @throws \Yaf_Exception|Throwable
     */
    public function getMember(): MemberModel
    {
        /** @var MemberModel $user */
        if ($this->aff) {
            $user = MemberModel::findByAff($this->aff);
        } else {
            $user = MemberModel::where('oauth_id', $this->oauth_id)
                ->where('oauth_type', $this->oauth_type)
                ->first();
        }
        if (empty($user)) {
            $user = $this->createMember();
            $user = MemberModel::onWriteConnection()->where('uid', $user->uid)->first();
        }

        if (empty($user)) {
            throw new RuntimeException('获取用户失败' . ':' . $this->oauth_id, 422);
        }

        return $user;
    }

    /**
     * 通过ip获取邀请人, ip保存邀请人的方式。只保存30分钟
     * @return MemberModel|null
     */
    public function isAff(): ?MemberModel
    {
        $key = "aff:ip:" . md5(client_ip());
        $aff = redis()->get($key);
        if (empty($aff)) {
            return null;
        }
        return cached("is:aff:" . $aff)->fetchPhp(function () use ($aff){
            return MemberModel::findByAff($aff);
        });
    }

    /**
     * 创建用户信息
     * @return MemberModel
     * @throws \Throwable
     */
    public function createMember(): MemberModel
    {
        $uuid = $this->uuid;
        if ($this->build_affcode) { // 渠道包
            $aff = get_num($this->build_affcode);
            $invited_member = MemberModel::findByAff($aff);
            $ip_invite = 0;
        } else {
            $invited_member = $this->isAff();
            $ip_invite = $invited_member ? 1 : 0;
        }
        $invited_num = 0;

        if (strlen($this->oauth_id) != 32) {
            throw new RuntimeException('数据错误');
        }

        try {
            \DB::beginTransaction();
            // 创建用户
            $member = new MemberModel();
            $member->uuid = md5($uuid . time());
            $member->app_version = $this->version;
            $member->oauth_type = $this->oauth_type;
            $member->oauth_id = $this->oauth_id;
            $member->username = '';
            $member->role_id = MemberModel::USER_ROLE_LEVEL_MEMBER;
            $member->regdate = date('Y-m-d H:i:s', TIMESTAMP);
            $member->lastactivity = date('Y-m-d H:i:s' , 1);
            $member->regip = USER_IP;
            $member->lastip = USER_IP;
            $member->invited_num = $invited_num;
            // $member->thumb = ;
            $member->post_num = 1;
            $member->invited_by = $invited_member ? $invited_member->aff : 0;
            $member->channel = $invited_member ? $invited_member->channel : 'self';
            $member->ip_invite = $ip_invite;
            $member->nickname = \tools\MemberRand::randNickname();
            $member->thumb = \tools\MemberRand::randAvatar();
            $member->saveOrFail();

            // 更新推广码 / 昵称
            $member->aff = $member->uid;
            $member->saveOrFail();
            MemberLogModel::initSession($member);

            if ($invited_member) {
                // 渠道数据上报
                if ($invited_member->channel != 'self') {
                    \tools\Channel::addUserQueue($member->toArray());
                }
                //吃瓜网页渠道不加邀请书
                if ($invited_member->aff != 21227492){
                    $invited_member->increment('invited_num');
                }
                //春节活动
                jobs([LotteryFreeLogModel::class, 'invite'], [$invited_member->aff]);

                SysTotalModel::incrBy('member:create-channel');
            }
            // UserProxyModel::create($proxy_data);
            // \tools\Channel::keepDataV2($member);
            SysTotalModel::incrBy('member:create');

            \DB::commit();

            //上报渠道V2数据
            ChannelService::reportInstall($member,USER_IP);

            return $member;
        } catch (\Throwable $exception) {
            // \Illuminate\Support\Facades\Log::info('新增用户失败' . $exception->getMessage());
            \DB::rollBack();
            trigger_log('创建用户失败', E_USER_ERROR);
            throw $exception;
        }
    }

    const US_KEY = '{crontab:session}';

    protected static function batchUpdateSession($list){
        $chunks = array_chunk($list , 50);
        $todayFirstSec = strtotime(date('Y-m-d 00:00:00'));
        $i = 1;
        $count = count($chunks);
        foreach ($chunks as $chunk) {
            echo "执行第", $i, "/", $count, "次任务, 工作数：", count($chunk), " ";
            $t1 = microtime(true);
            $uidAry = array_column($chunk, 'uid');
            $members = MemberModel::query()
                ->with('session:id')
                ->whereIn('uid' , $uidAry)
                ->selectRaw('uid,uuid,oauth_type,regdate,lastactivity')
                ->get();
            $data1 = [];
            $data2 = [];
            /** @var MemberModel $member */
            foreach ($members as $member) {
                if (strtotime($member->lastactivity) >= $todayFirstSec) {
                    continue;
                }
                $tmp = $list[$member->uid];
                $lastTime = $tmp['time'];
                $lastDate = date('Y-m-d H:i:s', $lastTime);
                $data1[] = ['uid' => $member->uid, 'lastactivity' => date('Y-m-d H:i:s', $lastTime),];
                if (!empty($member->session)) {
                    $data2[] = [
                        'id'           => $member->session->id,
                        'lastip'       => $member->lastip,
                        'lastactivity' => $lastDate,
                        'app_version'  => $member->app_version,
                        'oauth_type'   => $member->oauth_type,
                    ];
                }
                $member->lastactivity = $lastDate;
                $member->syncCached($tmp['key']);
            }
            MemberModel::batchUpdate($data1);
            MemberLogModel::batchUpdate($data2);
            echo "执行结束，耗时：", microtime(true) - $t1, "秒\r\n";
            $i++;
        }
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public static function crontabUpdateSession(){
        retry(10 , function ($times){
            $key = self::US_KEY . "-$times";
            echo "正在尝试第：{$times}\r\n" ;
            if (redis()->exists($key)){
                throw new RuntimeException('该次更新已有');
            }
            $ok = redis()->rename(self::US_KEY , $key);
            echo "rename('", self::US_KEY, "' , '$key') = ",var_export($ok , true),"\r\n";
            $list = [];
            redis()->sChunk($key , 2000 , function ($chunk) use (&$list){
                foreach ($chunk as $s) {
                    list($uid, $time, $rk) = explode('_', $s);
                    $list[$uid] = ['uid'=>$uid, 'time'=>$time, 'key'=>$rk];
                }
            });
            echo "待处理任务: " , count($list) , "\r\n" ;
            redis()->del($key);
            echo "休眠30秒，等mysql从库有同步的时间\r\n";
            sleep(30);
            $t1 = time();
            echo "任务开始, 开始时间：" ,date('Y-m-d H:i:s') , "\r\n";
            self::batchUpdateSession($list);
            $t2 = time() - $t1;
            echo "任务结束, 结束时间：" ,date('Y-m-d H:i:s') , "，耗时：" , $t2 ,"秒\r\n";
        });
    }

    public function updateSession(MemberModel $member): bool
    {
        $s = 'active:'.date('m-d');
        // 判断用户是有更新日活
        if (!redis()->sAddTtl($s, $member->uid , 90000)) {
            return false;
        }
        $s = sprintf("%d_%s_%s", $member->uid, time(), $this->redisKey);
        redis()->sAdd(self::US_KEY, $s);

        \SysTotalModel::incrBy('member:active');
        switch ($member->oauth_type) {
            case MemberModel::TYPE_ANDROID:
                \SysTotalModel::incrBy('member:active:and');
                break;
            case MemberModel::TYPE_WEB:
                \SysTotalModel::incrBy('member:active:web');
                break;
            case MemberModel::TYPE_IOS:
                \SysTotalModel::incrBy('member:active:ios');
                break;
        }

        // 统计邀请留存相关
        if ($member->invited_by > 0) {
            $invited = MemberModel::firstAff($member->invited_by);
            if ($invited) {
                $carbon = Carbon::parse($member->regdate);
                $day = $carbon->diffInDays();
                jobs([self::class, 'dayInvite'], [$day, $invited->aff, $invited->channel]);
            }
        }

        $carbon = Carbon::parse($member->regdate);
        $day = $carbon->diffInDays();
        if ($day <= 0 || $day > 15) {
            return true;
        }
        // 1-15 天的留存
        $key = "keep:{$day}day";
        $channel = $member->channel;
        SysTotalModel::incrBy($key);
        if ($member->channel != 'self') {
            SysTotalModel::incrBy('c' . $key);
            SysTotalModel::incrBy($key . ':' . $channel);
        }
        return true;
    }

    public static function dayInvite($day, $aff, $channel)
    {
        if ($day == 1) {
            DayInviteModel::retain(DayInviteModel::DAY_1, $aff, $channel);
            DayInviteModel::retain(DayInviteModel::DAY_3, $aff, $channel);
            DayInviteModel::retain(DayInviteModel::DAY_7, $aff, $channel);
        } elseif ($day <= 3) {
            DayInviteModel::retain(DayInviteModel::DAY_3, $aff, $channel);
            DayInviteModel::retain(DayInviteModel::DAY_7, $aff, $channel);
        } elseif ($day <= 7) {
            DayInviteModel::retain(DayInviteModel::DAY_7, $aff, $channel);
        }
    }

}
