<?php


namespace service;

use MemberFollowModel;
use MemberModel;
/*use AffOpenLogModel;
use UserProxyCashBackDetailModel;*/
use Carbon\Carbon;
use ChannelModel;
use AppointmentModel;
use MoneyLogModel;

class UserService
{
    public $member;
    public $redis;

    function __construct($member)
    {
        $this->member = $member;
    }

    /**
     *
     * @param $oauth_new_id
     * @return MemberModel
     */
    public static function getUserByOauthNewId($oauth_new_id)
    {
        return MemberModel::where(['oauth_new_id' => $oauth_new_id])->first();
    }

    /**
     * @param $aff
     * @return MemberModel|null
     */
    public static function getUserByAff($aff): ?MemberModel
    {
        $key = MemberModel::USER_REIDS_PREFIX . $aff;
        return cached($key)
            ->prefix('')
            ->fetchPhp(function () use ($aff){
                return MemberModel::where('aff', $aff)->first();
            });
    }

    static function clearUserByUUID($uuid, $aff = null)
    {
        $redisKey = MemberModel::USER_REIDS_PREFIX . ($uuid);
        redis()->del($redisKey);
        if ($aff) {
            $redisKey = MemberModel::USER_REIDS_PREFIX . $aff;
            redis()->del($redisKey);
            redis()->del('user:config:' . $aff);
        }
    }


    /**
     * @return string
     * @author
     * @date 2019-12-06 17:42:39
     */
    public static function loginTokenIv()
    {
        return config('encrypt.encrypt_key') . config('encrypt.sign_key');
    }


    /**
     * 通过aff 修改用户个人信息
     *
     * @param $aff
     * @param array $data
     *
     * @return bool
     */
    static function updateUser($aff, array $data): bool
    {
        if (!empty($data)) {
            // (!isset($data['updated_at'])) && $data['updated_at'] = TIMESTAMP;
            /** @var MemberModel $member */
            $member = MemberModel::where('aff', $aff)->first();
            if (empty($member)) {
                return false;
            }
            foreach ($data as $key => $value) {
                $member->{$key} = $value;
            }
            if ($member->isCreator()) {
                $creator = $member->creator;
                if ($member->isDirty('thumb')) {
                    $creator->thumb = $member->thumb;
                }
                if ($member->isDirty('nickname')) {
                    $creator->nickname = $member->nickname;
                }
            }
            if ($member->save()) {
                self::clearUserByUUID(self::getUserRealUuidByObject($member), $aff);
                redis()->del('user:config:' . $aff);
                $member->clearCached();
                return true;
            }
        }
        return false;
    }

    static function clearCache($aff): bool
    {
        if ($aff) {
            $member = MemberModel::findByAff($aff);
            if ($member) {
                self::clearUserByUUID(self::getUserRealUuidByObject($member), $aff);
            }
            redis()->del('user:config:' . $aff);
            return true;
        }
        return false;
    }

    public static function getShareURL()
    {
        $shareUrl = explode(',', setting('share_url'));
        $shareUrl = array_filter($shareUrl);
        return $shareUrl[array_rand($shareUrl)];
    }

    /**
     * 根据 aff 生成我的分享推广数据
     *
     * @param MemberModel $member
     *
     * @return array
     */
    public function getMyShareURLDATA(MemberModel $member): array
    {
        $aff_code = generate_code($member->aff);
        $shareUrl = self::getShareURL();
        $url = $shareUrl . '?code=' . $aff_code;
        if ($member->channel != 'self') {
            $channel = cached('channel:' . $member->channel)
                ->fetchPhp(function () use ($member){
                    return ChannelModel::where('channel_id', $member->channel)->first();
                });
            $url .= "&c=" . $channel->channel_num;
        }
        $text = setting('share-text', '分享文案 ');
        $text = str_replace('{url}', $url, $text);
        return [
            'aff_code'   => $aff_code,
            'share_text' => $text,
            'share_url'  => $url
        ];
    }

    static public function getUserRealUuidByObject(MemberModel $member): string
    {
        return md5($member->oauth_type . $member->oauth_id);
    }



    /**
     * @param $member
     * @param $type
     * @param $source
     * @param $num
     * @param null $source_aff
     * @param null $desc
     * @param null $model
     * @return \Illuminate\Database\Eloquent\Model|MoneyLogModel
     * @throws \Exception
     */
    static function updateMoney(
        $member,
        $type,
        $source,
        $num,
        $source_aff = null,
        $desc = null,
        $model = null
    )
    {
        if (is_array($member) && isset($member['uuid'])) {
            $member = MemberModel::findByUuid($member['uuid']);
        }
        if (empty($member)) {
            throw new \Exception('用户不存在');
        }

        $rs = \MoneyLogModel::create([
            'aff'        => $member->aff,
            'source'     => $source,
            'type'       => $type,
            'coinCnt'    => $num,
            'source_aff' => $source_aff,
            'desc'       => $desc,
            'data_name'  => $model ? $model->getTable() : '',
            'data_id'    => $model ? $model->getAttribute($model->getKeyName()) : '',
        ]);
        test_assert($rs, '记录日志失败');
        if ($type == \MoneyLogModel::TYPE_ADD) {
            $isOk = MemberModel::where(['aff' => $member->aff])->increment('money', $num);
            test_assert($isOk, '处理用户数据失败:add');
        } else {
            if ($num < 0) {
                throw new \Exception('金额错误');
            }
            if ($member->money < $num) {
                throw new \Exception('余额不足');
            }
            $isOk = MemberModel::where([
                ['aff', '=', $member->aff],
                ['money', '>=', $member->money]
            ])->decrement('money', $num);
            test_assert($isOk, '处理用户数据失败:sub');
        }
        MemberModel::clearFor($member);
        return $rs;
    }
    // 预定
    public function appointment($aff, $info)
    {
        $insert['status'] = AppointmentModel::STATUS_INIT;
        $insert['aff'] = $aff;
        $insert['info_id'] = $info->id;
        $insert['freeze_money'] = $info->fee;
        $insert['coupon_id'] = $info->couponId;
        $insert['created_at'] = TIMESTAMP;
        $insert['updated_at'] = TIMESTAMP;
        return AppointmentModel::create($insert);
    }
    //用户关注列表
    public static function getUserFollowedList($aff, $page, $limit)
    {
        return MemberFollowModel::query()
            ->where('aff', $aff)
            ->with('follow:uid,aff,uuid,nickname,thumb,person_signnatrue,vip_level,followed_count,post_count')
            ->orderByDesc('created_at')
            ->forPage($page,$limit)
            ->get()
            ->pluck('follow')
            ->map(function (MemberModel $item){
                return [
                    'uid'        => $item->uid,
                    'aff'        => $item->aff,
                    'nickname'   => $item->nickname,
                    'vip_level'  => $item->vip_level,
                    'person_signnatrue' => $item->person_signnatrue,
                    'followed_count' => $item->followed_count,
                    'post_count' => $item->post_count,
                    'thumb'      => $item->thumb,
                    'is_follow'  => 1,
                    'thumb_bg'   => $item->thumb_bg,
                    'vip_bg'     => $item->vip_bg,
                ];
            });
    }
}