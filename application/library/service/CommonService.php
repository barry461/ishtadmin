<?php


namespace service;

use Carbon\Carbon;
use EmailLogModel;
use tools\IpLocation;
use tools\HttpCurl;
use VersionModel;

class CommonService
{
    /** ######################### sms 相关 ################################## */

    static function sendSms($phone, $prefix = '86', $uuid, $type)
    {
        if (ini_get('yaf.environ') == 'product') {
            $row = self::getSmsByPhone($phone, $prefix, $type);
            if ($row && (TIMESTAMP - strtotime($row['created_at']) < 60)) {
                return [
                    'code' => 400,
                    'msg'  => '发送短信太频繁'
                ];
            }
        }
        if (matchVirNo($phone)) {
            return [
                'code' => 400,
                'msg'  => '发送失败'
            ];

        }
        $code = \SmsLogModel::genSmsCode();
        self::addSmsLog($uuid, $phone, $prefix, $code, $type);
        $smsData = [
            'content'  => $code,
            'mobile'   => '+' . $prefix . $phone,
            'app_name' => 'ct'
        ];
        $curl = new \tools\HttpCurl();
        $rs = $curl->Post(config('sms.url'), $smsData);
        if (!$rs) {
            return [
                'code' => 400,
                'msg'  => '短信服务器错误,请稍后再试'
            ];
        }
        $rs = json_decode($rs, true);
        if ($rs && $rs['success']) {
            return [
                'code' => 200,
                'msg'  => '发送成功'
            ];
        }
        return [
            'code' => 400,
            'msg'  => '发送失败'
        ];

    }

    static function addSmsLog($uuid, $phone, $prefix, $code, $type = 1)
    {
        $data = [
            'uuid'   => $uuid,
            'prefix' => $prefix,
            'mobile' => $phone,
            'code'   => $code,
            'ip'     => USER_IP,
            'status' => 0,
            'type'   => $type,
        ];
        return \SmsLogModel::create($data);
    }

    static function createQrcodeID()
    {
        $redisKey = 'login:qrcode';
        $leftNum = redis()->scard($redisKey);
        if ($leftNum < 200) {
            redis()->pipeline();
            for ($i = 0; $i < 1000; $i++) {
                $code = random(10);
                redis()->sadd($redisKey, $code);
            }
            redis()->exec();
        }
    }


    /**
     *  版本获取
     * @param $type
     * @param int $status
     * @param string $channel 渠道| 默认空
     * @return mixed
     */
    static function getleastVersion($type, $status = \VersionModel::STATUS_SUCCESS, $channel = '')
    {
        return cached('version:' . $type . '-' . $channel)
            ->group('version')
            ->fetchPhp(function () use ($type, $status, $channel) {
                $where = [
                    ['type', '=', $type],
                    ['status', '=', $status],
                ];
                $data = \VersionModel::query()->where($where)
                    ->when($channel, function ($query, $channel) {
                        $query->where('channel', $channel);
                    })
                    ->orderByDesc('id')->first();
                if (empty($data) && $channel) {
                    $data = \VersionModel::query()->where($where)->orderByDesc('id')->first();
                }
                return $data;
            }, 86400);
    }

    static function get_main_android_least_version_v2($custom)
    {
        return cached('version:android:v3' . $custom)
            ->group('version')
            ->chinese('版本管理')
            ->fetchPhp(function () use ($custom) {
                $where = [
                    ['channel', '=', ""],
                    ['type', '=', VersionModel::TYPE_ANDROID],
                    ['status', '=', VersionModel::STATUS_SUCCESS],
                    ['custom', '=', $custom],
                ];
                return VersionModel::query()->where($where)->orderByDesc('id')->first();
            }, 86400);
    }

    /**
     * 根据广告位置获取广告列表
     * @param int $position
     * @param string $channel
     * @return array
     */
    public static function getADsByPosition(int $position, $channel = '')
    {
        $redis = \tools\RedisService::instance();
        $ad_key = \AdsModel::REDIS_ADS_KEY . $position;

        $where = [
            ['status', '=', \AdsModel::STATUS_SUCCESS],
            ['position', '=', $position],
        ];
        $ads = null;
        if ($channel) {
            $where[] = ['channel', '=', $channel];
            $ad_key_c = $ad_key . $channel;
            $ads = $redis->getWithSerialize($ad_key_c);
            if (!$ads) {
                $ads = \AdsModel::query()
                    ->where($where)
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->orderByDesc('id')
                    ->get();
                $redis->setWithSerialize($ad_key_c, $ads, 3600);
            }
        }
        if (!$ads) {
            $where[] = ['channel', '=', ''];
            $ads = $redis->getWithSerialize($ad_key);
            if (!$ads) {
                $ads = \AdsModel::query()
                    ->where($where)
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->orderByDesc('id')
                    ->get();
                $redis->setWithSerialize($ad_key, $ads, 3600);
            }
        }
        $result = [];
        if ($ads->count()) {
            $ads = $ads->toArray();
            foreach ($ads as $key => $value) {
                $result[$key] = $value;
                $result[$key]['img_url'] = $value['img_url'] ? url_image($value['img_url']) : '';
            }
        }
        return $result;
    }


    /**
     * 根据广告位置获取广告列表
     * @return array
     * 域名检测上报日志
     * {
     * "ip": "163.177.65.160",
     * "country": "中国",
     * "province": "广东",
     * "city": "深圳市",
     * "county": "",
     * "isp": "联通",
     * "area": "中国广东省深圳市腾讯计算机系统联通节点"
     * }
     *
     * {
     * "error": "ip invalid"
     * }
     */
    public static function getArea()
    {

        $position = IpLocation::getLocation(USER_IP);
        $area = '';
        if (!isset($position['error'])) {
            unset($position['ip']);
            $pstr = implode('', $position);
            $area = str_ireplace(['中国', '省', '市'], ['', '', ''], $pstr);
        }
        return $area;
    }

    static function domainCheckReport($inputData, $member): bool
    {
        if (!is_array($inputData)) {
            return false;
        }
        $user = $member;
        $area = self::getArea();
        $oauth_type = $user->oauth_type;
        $insertData = [];
        foreach ($inputData as $data) {
            $data['url'] && $insertData[] = [
                'uuid'       => $user['uuid'] ?? '',
                'url'        => $data['url'],
                'ip'         => USER_IP,
                'area'       => $area,
                'oauth_type' => $oauth_type,
                'sick'       => $data['sick'] ?? 0,
                'type'       => $data['type'] ?? '',
            ];
        }
        $insertData && \AreaLogModel::insert($insertData);
        return true;
    }

    public static function isPcQuest($oauth_type): int
    {
        if (in_array($oauth_type,[\MemberModel::TYPE_MACOS,\MemberModel::TYPE_WINDOWS])){
            return 1;
        }
        return 0;
    }

    public static function getAds(\MemberModel $member,$pos,$isOne = false){
        if (self::isPcQuest($member->oauth_type)){
            return $isOne ? \PcAdsModel::onePos($pos) : \PcAdsModel::listPos($pos);
        }else{
             //return $isOne ? \AdsModel::onePos($pos) : \AdsModel::listPos($pos);
            //替换
            if ($isOne){
                /** @var \AdsModel $ad */
                $ad = \AdsModel::onePos($pos);
                if(!empty($ad)){
                    $ad->unsetRelation('url_config');
                    $url = str_replace('{token}', getID2Code($member->uid), $ad->url_config);
                    $url = replace_share($url);
                    $ad->setAttribute('url_config', $url);
                }
                return $ad;
            }else{
                $list = \AdsModel::listPos($pos);
                $result =  collect($list)->map(function ($ad) use ($member){
                    $ad->unsetRelation('url_config');
                    $url = str_replace('{token}', getID2Code($member->uid), $ad->url_config);
                    $url = replace_share($url);
                    $ad->setAttribute('url_config', $url);
                    return $ad;
                });
                $result = $result->toArray();
                $shuffle = $list = [];
                foreach ($result as $row){
                    $list[$row['sort']][] = $row;
                }
                foreach ($list as $list1){
                    shuffle($list1);
                    $shuffle = array_merge($shuffle,$list1);
                }
                return $shuffle;
            }
        }
    }

    public static function getNotice(\MemberModel $member, $pos,$isOne = true)
    {
        if (self::isPcQuest($member->oauth_type)){
            return self::getPcNotice($member, $pos,$isOne);
        }
        $data = cached(\NoticeModel::REDIS_KEY_NOTICE_LIST . $pos)
            ->group(\NoticeModel::REDIS_KEY_NOTICE_LIST)
            ->fetchPhp(function () use ($pos) {
                return \NoticeModel::selectRaw('id,url,img_url,title,content,router,visible_type,type,height,width')
                    ->where(['status' => \NoticeModel::STATUS_SUCCESS, 'pos' => $pos])
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            }, 86400);

        $result = collect([]);
        /** @var \NoticeModel $datum */
        foreach ($data as $datum) {
            $datum->url = replace_share($datum->url);
            $datum->url = str_replace('{token}', getID2Code($member->uid), $datum->url);
            if ($datum->visible_type == \NoticeModel::VISIBLE_TYPE_NEWCOMER) {
                if ($member->new_user) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \NoticeModel::VISIBLE_TYPE_NEWCOMER_NOTVIP) {
                if ($member->new_user && Carbon::parse($member->expired_at)->lt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \NoticeModel::VISIBLE_TYPE_NEWCOMER_VIP) {
                if ($member->new_user && Carbon::parse($member->expired_at)->gt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \NoticeModel::VISIBLE_TYPE_VIP) {
                if (Carbon::parse($member->expired_at)->gt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \NoticeModel::VISIBLE_TYPE_NOTVIP) {
                if (Carbon::parse($member->expired_at)->lt(Carbon::now())) {
                    $result->push($datum);
                }
            } else {
                $result->push($datum);
            }
        }

        if (!$isOne) {
            return $result;
        }

        $first = $result->first();
        $first = $first ? $first->toArray() : null;
        return $first ?? null;
    }

    public function appClickReport($type,$id){
        $types = array_keys(\DayClickModel::TYPE_TIPS);
        test_assert(in_array($type, $types), '类型错误');

        switch ($type) {
            case \DayClickModel::TYPE_ADS:
                // 固定位广告
                jobs([\AdsModel::class, 'incrNum'], [$id]);
                jobs([\DayClickModel::class, 'incrNum'], [\DayClickModel::TYPE_ADS, $id]);
                break;
            case \DayClickModel::TYPE_NOTICE:
                // 弹框广告
                jobs([\NoticeModel::class, 'incrNum'], [$id]);
                jobs([\DayClickModel::class, 'incrNum'], [\DayClickModel::TYPE_NOTICE, $id]);
                break;
            case \DayClickModel::TYPE_APP:
                // 福利APP
                jobs([\AppModel::class, 'incrNum'], [$id]);
                jobs([\DayClickModel::class, 'incrNum'], [\DayClickModel::TYPE_APP, $id]);
                break;
            case \DayClickModel::TYPE_NOTICE_APP:
                // 弹窗APP
                jobs([\NoticeAppModel::class, 'incrNum'], [$id]);
                jobs([\DayClickModel::class, 'incrNum'], [\DayClickModel::TYPE_NOTICE_APP, $id]);
                break;
        }
    }

    public function pcClickReport($type,$id){
        $types = array_keys(\PcDayClickModel::TYPE_TIPS);
        test_assert(in_array($type, $types), '类型错误');

        switch ($type) {
            case \PcDayClickModel::TYPE_ADS:
                // 固定位广告
                jobs([\PcAdsModel::class, 'incrNum'], [$id]);
                jobs([\PcDayClickModel::class, 'incrNum'], [\PcDayClickModel::TYPE_ADS, $id]);
                break;
            case \PcDayClickModel::TYPE_NOTICE:
                // 弹框广告
                jobs([\PcNoticeModel::class, 'incrNum'], [$id]);
                jobs([\PcDayClickModel::class, 'incrNum'], [\PcDayClickModel::TYPE_NOTICE, $id]);
                break;
            case \PcDayClickModel::TYPE_APP:
                // 福利APP
                jobs([\PcAppModel::class, 'incrNum'], [$id]);
                jobs([\PcDayClickModel::class, 'incrNum'], [\PcDayClickModel::TYPE_APP, $id]);
                break;
        }
    }

    public static function getPcNotice(\MemberModel $member, $pos,$isOne = true)
    {
        $data = cached(\PcNoticeModel::REDIS_KEY_NOTICE_LIST . $pos)
            ->group(\PcNoticeModel::REDIS_KEY_NOTICE_LIST)
            ->fetchPhp(function () use ($pos) {
                return \PcNoticeModel::selectRaw('id,url,img_url,title,content,router,visible_type,type,height,width')
                    ->where(['status' => \PcNoticeModel::STATUS_SUCCESS, 'pos' => $pos])
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            }, 86400);

        $result = collect([]);
        /** @var \PcNoticeModel $datum */
        foreach ($data as $datum) {
            if ($datum->visible_type == \PcNoticeModel::VISIBLE_TYPE_NEWCOMER) {
                if ($member->new_user) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \PcNoticeModel::VISIBLE_TYPE_NEWCOMER_NOTVIP) {
                if ($member->new_user && Carbon::parse($member->expired_at)->lt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \PcNoticeModel::VISIBLE_TYPE_NEWCOMER_VIP) {
                if ($member->new_user && Carbon::parse($member->expired_at)->gt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \PcNoticeModel::VISIBLE_TYPE_VIP) {
                if (Carbon::parse($member->expired_at)->gt(Carbon::now())) {
                    $result->push($datum);
                }
            } elseif ($datum->visible_type == \PcNoticeModel::VISIBLE_TYPE_NOTVIP) {
                if (Carbon::parse($member->expired_at)->lt(Carbon::now())) {
                    $result->push($datum);
                }
            } else {
                $result->push($datum);
            }
        }

        if (!$isOne) {
            return $result;
        }

        $first = $result->first();
        $first = $first ? $first->toArray() : null;
        return $first ?? null;
    }

    public function helpFeedbackList(): array
    {
        return cached(\UserHelpModel::REDIS_USER_HELP_LIST)
            ->chinese('常见问题')
            ->fetchPhp(function (){
                $user_help_type = array(
                    1 => '热点问题',
                    2 => '加载缓存',
                    3 => '个人账户',
                    4 => '资源内容',
                    5 => '分享推广',
                    6 => '其他问题'
                );
                $result = \UserHelpModel::query()->where("status", 0)->get();
                $data = [];
                if ($result->count()) {
                    $result = $result->toArray();
                    foreach ($result as $key => $row) {
                        if (isset($user_help_type[$row['type']])) {
                            $data[$row['type']]['items'][] = $row;
                            $data[$row['type']]['type'] = $row['type'];
                            $data[$row['type']]['name'] = $user_help_type[$row['type']];
                        }
                    }
                    $data = array_values($data);
                }
                return $data;
            });
    }


    // mp4视频切片m3u8
    static function mp4Slices($mvId, $mvUrl, $notifyUrl)
    {
        $uploadUrl = config('mp4.accept');
        $post = array(
            'uuid'    => 'squid',
            'm_id'    => $mvId,
            'needImg' => '1',
            'needMp3' => '0',
            'playUrl' => $mvUrl,
        );
        $string = '';
        ksort($post);
        foreach ($post as $key => $value) {
            $string .= $key . '=' . $value . '&';
        }
        $string = substr($string, 0, -1);
        $post['sign'] = md5(hash('sha256', $string .  config('app.data_sync_key')));
        $post['notifyUrl'] = $notifyUrl;
        trigger_log("视频切片请求--\n" . print_r($post, true));
        $res = HttpCurl::post($uploadUrl, $post);
        trigger_log("视频切片请求--\n" . print_r($res, true));
        if ($res == 'success') {
            return true;
        }
        return false;
    }

    /** ######################### email 相关 ################################## */

    public static function sendEmail($email, $aff, $type)
    {
        if (ini_get('yaf.environ') == 'product') {
            $row = EmailLogModel::findByAff($email, $aff, $type);
            if ($row && (TIMESTAMP - strtotime($row['created_at']) < 60)) {
                test_assert(false, '发送短信太频繁');
            }
        }
        $code = EmailLogModel::genEmailCode();
        $res = EmailLogModel::send($email, $code);
        if ($res['code'] == 200){
            self::addEmailLog($aff, $email, $code, $type, EmailLogModel::STATUS_NO);
        }else{
            self::addEmailLog($aff, $email, $code, $type, EmailLogModel::STATUS_FAIL);
            test_assert(false, '发送失败，请重试');
        }
    }

    public static function addEmailLog($aff, $email, $code, $type, $status)
    {
        $data = [
            'aff'    => $aff,
            'email'  => $email,
            'code'   => $code,
            'ip'     => USER_IP,
            'status' => $status,
            'type'   => $type,
        ];
        return EmailLogModel::create($data);
    }

    public static function validatorCode($email, $aff, $type, $code)
    {
        /** @var EmailLogModel $data */
        $data = EmailLogModel::getEmailCode($email, $aff, $type);
        test_assert($data, '未找到验证码记录');
        test_assert($data->code == $code, '验证码不正确');
        test_assert(TIMESTAMP - 900 < strtotime($data->created_at), '验证码已过期');

        //使用完毕就将改手机号的验证码都设置成已使用
        $data->status = EmailLogModel::STATUS_YES;
        $isOK = $data->save();
        test_assert($isOK, '验证码修改失败');
        return true;
    }
}