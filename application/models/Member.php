<?php

use Illuminate\Database\Eloquent\Model;
use service\UserService;
use Illuminate\Events\Dispatcher;
use Carbon\Carbon;

/**
 * class MembersModel
 *
 * @property int $uid
 * @property string $oauth_type 设备'ios','android'
 * @property string $oauth_id
 * @property string $oauth_new_id
 * @property string $uuid
 * @property string $username
 * @property string $password
 * @property int $created_at
 * @property int $updated_at
 * @property int $role_id
 * @property int $gender
 * @property int $ban_post
 * @property string $regip
 * @property string $regdate
 * @property string $lastip
 * @property string|datetime $expired_at 会员到期时间
 * @property int $lastpost
 * @property int $oltime 在线小时数
 * @property int $pageviews 论坛用的,电影可以不用
 * @property int $score 用户积分
 * @property int $aff 邀请码 = uid
 * @property int $invited_by 被谁 aff 邀请
 * @property int $post_num
 * @property int $invited_num 已邀请安装个数
 * @property int $new_comment_reply
 * @property int $new_topic_reply
 * @property int $login_count
 * @property int $agent
 * @property int $agent_fee
 * @property string $app_version app版本号
 * @property string $free_view_date
 * @property int $validate
 * @property int $share 分享
 * @property int $is_login
 * @property string $nickname 用户昵称
 * @property string $thumb 用户头像
 * @property int $coins 铜钱  :哩币 1:10
 * @property int $money 哩币  :RMB 1:1
 * @property int $temp_vip 临时卡
 * @property int $followed_count 有多少人关注此用户
 * @property int $videos_count 作品数
 * @property int $fabulous_count 获赞数
 * @property int $likes_count 喜欢数
 * @property int $comment_count 视频被评论数
 * @property int $vip_level vip等级
 * @property string $person_signnatrue 个人签名
 * @property int $old_vip 老vip
 * @property int $stature 身高
 * @property string $interest 爱好兴趣
 * @property string $city 城市
 * @property int $used_money_free_num 使用免费解锁哩币次数
 * @property int $buy_count 购买视频记录
 * @property int $news_num 发帖数量
 * @property int $build_id 渠道标识
 * @property int $auth_status 0 未认证 1 认证通过
 * @property int $exp 积分
 * @property string $is_virtual 是否是虚拟用户
 * @property string $chat_uid
 * @property string $phone 手机
 * @property string $phone_prefix 手机地区号
 * @property int $free_view_cnt 剩余免费观看次数
 * @property string $lastactivity 最后活跃时间
 * @property string $channel 渠道标示
 * @property int $proxy_money 代理收益,元
 * @property int $income_total 总收益
 * @property int $income_money 可提现收益
 * @property int $income_royalties 投稿收益
 * @property int $income_royalties_total 投稿总收益
 * @property int $ip_invite 是否通过ip进行的邀请
 * @property string $draw_name 提现名字
 * @property int $order_count 订单数量
 * @property int $post_count 帖子数量
 * @property int $follow_count 关注数
 * @property int $topic_count 创建的剧集数量
 * @property int $tags 用户给其打的标签
 * @property int $invited_reg_num
 * @property int $unread_reply 未读的评论回复
 * @property int $post_club_id 帖子俱乐部的id
 * @property int $post_club_month 帖子订阅月卡价格
 * @property int $post_club_quarter 帖子订阅季卡价格
 * @property int $post_club_year 帖子订阅年卡价格
 * @property int $post_club_total 帖子订阅总收益
 * @property string $email 邮箱
 *
 * @property bool $new_user
 * @property bool $in_newreg 是否是配置里面的几天内的新用户
 * @property MemberCreatorModel $creator
 * @property MemberLogModel $session
 *
 * @mixin \Eloquent
 */
class MemberModel extends BaseModel
{
    protected $table = 'members';

    const REDIS_USER_FOLLOWED_LIST = "user:follow:list:";
    const REDIS_USER_FANS_LIST = "user:fan:list";
    const REDIS_USER_FANS_ITEM = "user:fan:item";
    const REDIS_USRE_FOLLOWED_ITEM = "user:follow:item";
    const USER_ROLE_LEVEL_MEMBER = 8; // 普通用户
    const USER_REIDS_PREFIX = 'user:info:';
    const USER_REDIS_AFF_MAP = 'user:aff:map:';
    const USER_REIDS_USERCODE = 'user:usercode:';
    const USER_WORK = 'user:work:';
    const USER_BOSS_REPLY = 'user:boss:reply:';
    const USER_DEFAULT_NICKNAME_PREFIX = 'Guest_';
    const REDIS_KEY_CHECKER_TODAY_PICK = 'user:checker:pick:today:';

    const INVITED_REWARD_DAYS = 2; // 邀请奖励天数
    const AGENT_VIP_DEPOSIT = 500;
    const AGENT_HALL_DEPOSIT = 100;
    protected $primaryKey = 'uid';


    const VIP_LEVEL_NORMAL = 0;
    const VIP_LEVEL_TMP = 1;
    const VIP_LEVEL_WEEKLY = 2;
    const VIP_LEVEL_MONTHLY = 3;
    const VIP_LEVEL_QUARTERLY = 4;
    const VIP_LEVEL_HALFYEAR = 5;
    const VIP_LEVEL_ANNUAL = 6;
    const VIP_LEVEL_TWO_YEAR = 7;
    const VIP_LEVEL_LONG = 8;
    const VIP_LEVEL_ANNUAL_ZX = 9;

    const VIP_LEVEL = [
        self::VIP_LEVEL_NORMAL => '普通',
        self::VIP_LEVEL_TMP => '临时',
        self::VIP_LEVEL_WEEKLY => '周卡',
        self::VIP_LEVEL_MONTHLY => '月卡',
        self::VIP_LEVEL_QUARTERLY => '季卡',
        self::VIP_LEVEL_HALFYEAR => '半年卡',
        self::VIP_LEVEL_ANNUAL => '年卡',
        self::VIP_LEVEL_TWO_YEAR => '两年卡',
        self::VIP_LEVEL_LONG => '永久卡',
        self::VIP_LEVEL_ANNUAL_ZX => '尊享卡',
    ];

    const VIP_THUMB_BG = [
        self::VIP_LEVEL_NORMAL => '',
        self::VIP_LEVEL_TMP => '/upload_01/ads/20240704/2024070415113964694.png',
        self::VIP_LEVEL_WEEKLY => '/upload_01/ads/20240704/2024070415113964694.png',
        self::VIP_LEVEL_MONTHLY => '/upload_01/ads/20240704/2024070415113964694.png',
        self::VIP_LEVEL_QUARTERLY => '/upload_01/ads/20240704/2024070415103230944.png',
        self::VIP_LEVEL_HALFYEAR => '/upload_01/ads/20240704/2024070415083688713.png',
        self::VIP_LEVEL_ANNUAL => '/upload_01/ads/20240704/2024070415083688713.png',
        self::VIP_LEVEL_TWO_YEAR => '/upload_01/ads/20240704/2024070415083688713.png',
        self::VIP_LEVEL_LONG => '/upload_01/ads/20240704/2024070415083688713.png',
        self::VIP_LEVEL_ANNUAL_ZX => '/upload_01/ads/20240704/2024070415050153645.png',
    ];

    const VIP_ICON_BG = [
        self::VIP_LEVEL_NORMAL => '',
        self::VIP_LEVEL_TMP => '/upload_01/ads/20240704/2024070415114840032.png',
        self::VIP_LEVEL_WEEKLY => '/upload_01/ads/20240704/2024070415114840032.png',
        self::VIP_LEVEL_MONTHLY => '/upload_01/ads/20240704/2024070415114840032.png',
        self::VIP_LEVEL_QUARTERLY => '/upload_01/ads/20240704/2024070415104185808.png',
        self::VIP_LEVEL_HALFYEAR => '/upload_01/ads/20240704/2024070415084714979.png',
        self::VIP_LEVEL_ANNUAL => '/upload_01/ads/20240704/2024070415084714979.png',
        self::VIP_LEVEL_TWO_YEAR => '/upload_01/ads/20240704/2024070415084714979.png',
        self::VIP_LEVEL_LONG => '/upload_01/ads/20240704/2024070415084714979.png',
        self::VIP_LEVEL_ANNUAL_ZX => '/upload_01/ads/20240704/2024070415055318550.png',
    ];

    const AUTH_STATUS_NO = 0;
    const AUTH_STATUS_YES = 1;
    const AUTH_STATUS = [
        self::AUTH_STATUS_NO => '未认证',
        self::AUTH_STATUS_YES => '已认证',
    ];

    //role_id  设置 8 正常 9 禁言 10 封号 20 渠道（主）
    const ROLE_NORMAL = 8;
    const ROLE_FORBIDDEN = 9;
    const ROLE_BAN = 10;
    const ROLE_CHANNEL = 20;
    const ROLE_BROKER = 30;
    const ROLE_AUTH_AGENT = 40;
    const ROLE_AUTH_PERSONAL = 41;
    const ROLE = [
        self::ROLE_NORMAL => '普通',
        self::ROLE_FORBIDDEN => '禁言',
        self::ROLE_BAN => '封号',
        self::ROLE_CHANNEL => '渠道',
        self::ROLE_BROKER => '商家',
        self::ROLE_AUTH_AGENT => '经纪人入驻用户',
        self::ROLE_AUTH_PERSONAL => '个人入驻用户',
    ];


    const BAN_POST_YES = 1;
    const BAN_POST_NO = 0;
    const BAN_POST = [
        self::BAN_POST_YES => '禁止',
        self::BAN_POST_NO => '允许',
    ];

    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';
    const TYPE_WEB = 'web';
    const TYPE_WINDOWS = 'windows';
    const TYPE_MACOS = 'macos';
    const TYPE = [
        self::TYPE_ANDROID => '安卓',
        self::TYPE_IOS => '苹果',
        self::TYPE_WEB => 'web',
        self::TYPE_MACOS => 'macos',
        self::TYPE_WINDOWS => 'windows',
    ];

    const AGENT_TYPE_INIT = 0;
    const AGENT_TYPE_AGENT = 1;
    const AGENT_TYPE = [
        self::AGENT_TYPE_INIT => '普通',
        self::AGENT_TYPE_AGENT => '创作',
    ];

    // 可填充字段
    protected $fillable = [
        'oauth_type',
        'oauth_id',
        'oauth_new_id',
        'uuid',
        'username',
        'password',
        'channel',
        'created_at',
        'updated_at',
        'role_id',
        'gender',
        'regip',
        'regdate',
        'lastip',
        'expired_at',
        'lastpost',
        'oltime',
        'pageviews',
        'score',
        'aff',
        'invited_by',
        'invited_num',
        'ban_post',
        'post_num',
        'login_count',
        'app_version',
        'validate',
        'share',
        'is_login',
        'nickname',
        'thumb',
        'coins',
        'temp_vip',
        'followed_count',
        'videos_count',
        'fabulous_count',
        'likes_count',
        'comment_count',
        'vip_level',
        'person_signnatrue',
        'old_vip',
        'stature',
        'interest',
        'city',
        'used_money_free_num',
        'agent_fee',
        'agent',
        'build_id',
        'auth_status',
        'exp',
        'chat_uid',
        'phone',
        'phone_prefix',
        'free_view_cnt',
        'free_view_date',
        'lastactivity',
        'income_money',
        'income_total',
        'money',
        'draw_name',
        'ip_invite',
        'order_count',
        'post_count',
        'follow_count',
        'topic_count',
        'tags',
        'invited_reg_num',
        'unread_reply',
        'post_club_id',
        'post_club_month',
        'post_club_quarter',
        'post_club_year',
        'post_club_total',
        'income_royalties',
        'income_royalties_total',
        'email',
    ];

    protected $hidden = [
        'password',
        'invited_reg_num',
        'order_count',
        'draw_name',
        'oauth_id',
        'oauth_new_id',
        'ip_invite',
    ];
    protected $appends = [
        'is_set_password',
        'new_user',
        'tag_list',
        'vip_str',
        'is_follow',
        'vip_bg',
        'thumb_bg',
        'is_official',
    ];

    /**
     * @param int $invited_by
     *
     * @return MemberModel|stdClass
     */
    public static function findByAff(int $invited_by)
    {
        return MemberModel::where('aff', $invited_by)->first();
    }

    public function getRegdateAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    public function getLastactivityAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    public function getExpiredAtAttribute($value): ?string
    {
        return $this->getCreatedAtAttribute($value);
    }

    public function setThumbAttribute($value)
    {
        $this->resetSetPathAttribute('thumb', $value);
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function getOauthStrAttribute(): string
    {
        return sprintf("%s-%s", $this->attributes['oauth_type'] ?? '', $this->attributes['app_version'] ?? '');
    }

    public function getIsSetPasswordAttribute(): int
    {
        $password = $this->attributes['password'] ?? '';
        return $password ? 1 : 0;
    }

    public function getNewUserAttribute(): bool
    {
        // 使用原生 PHP 替代 Carbon，避免 autoload 问题
        $regdate = $this->attributes['regdate'] ?? null;
        if (!$regdate) {
            return false;
        }
        $regTimestamp = is_numeric($regdate) ? (int) $regdate : strtotime($regdate);
        $oneDayAgo = strtotime('-1 days');
        return $regTimestamp >= $oneDayAgo;
    }

    public function getInNewRegAttribute(): bool
    {
        // 使用原生 PHP 替代 Carbon，避免 autoload 问题
        $regdate = $this->attributes['regdate'] ?? null;
        if (!$regdate) {
            return false;
        }
        $regTimestamp = is_numeric($regdate) ? (int) $regdate : strtotime($regdate);
        $diffDays = (int) floor((time() - $regTimestamp) / 86400);
        return $diffDays <= setting('user-7day', 7);
    }

    public function getBanPostStrAttribute(): string
    {
        return $this->resolveConstantValue(self::BAN_POST, 'ban_post');
    }

    public function getVipStrAttribute(): string
    {
        if (isset($this->attributes['vip_level'])) {
            return self::VIP_LEVEL[$this->attributes['vip_level']] ?? '';
        } else {
            return '';
        }
    }

    public function getThumbBgAttribute(): string
    {
        if (isset($this->attributes['vip_level'])) {
            return url_image(self::VIP_THUMB_BG[$this->attributes['vip_level']]);
        } else {
            return '';
        }
    }

    public function getVipBgAttribute(): string
    {
        if (isset($this->attributes['vip_level'])) {
            return url_image(self::VIP_ICON_BG[$this->attributes['vip_level']]);
        } else {
            return '';
        }
    }

    public function getIsFollowAttribute(): int
    {
        static $ary = null;
        if (APP_MODULE == 'staff') {
            return 1;
        }
        if (isset($this->attributes['is_follow'])) {
            return $this->attributes['is_follow'];
        }
        $to_aff = $this->attributes['aff'] ?? 0;
        $aff = self::$watchUser ? self::$watchUser->aff : 0;
        if (empty($to_aff) || empty($aff)) {
            return 0;
        }
        $rk = MemberFollowModel::generateId($aff);
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (empty($ary) || !is_array($ary) || !in_array($to_aff, $ary)) {
            return 0;
        }

        return 1;
    }

    public function getIsOfficialAttribute(): int
    {
        static $ary = null;
        if (APP_MODULE == 'staff') {
            return 0;
        }
        if (isset($this->attributes['is_official'])) {
            return $this->attributes['is_official'];
        }
        $aff = $this->attributes['aff'] ?? 0;
        if (empty($aff)) {
            return 0;
        }
        $rk = OfficialAccountModel::OFFICIAL_ACCOUNT_SET;
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (is_array($ary) && !empty($ary) && in_array($aff, $ary)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param MemberModel|array|object $member
     */
    public static function clearFor($member, $oauth_type = '', $post_oauth_id = '')
    {
        $uuid = $member['uuid'] ?? '';
        $aff = $member['aff'] ?? '';
        $args = [];
        $args[] = MemberModel::USER_REIDS_PREFIX . $uuid;
        $md5Uuid = md5(($member['oauth_type'] ?? '') . ($member['oauth_id'] ?? ''));
        $args[] = MemberModel::USER_REIDS_PREFIX . $md5Uuid;
        if ($oauth_type && $post_oauth_id) {
            $md5Uuid = md5($oauth_type . $post_oauth_id);
            $args[] = MemberModel::USER_REIDS_PREFIX . $md5Uuid;
        }
        $args[] = MemberModel::USER_REIDS_PREFIX . $aff;
        $args[] = 'user:config:' . $aff;
        foreach ($args as $arg) {
            redis()->expire($arg, 3);
        }
    }

    /**
     * 是否是禁言
     * @return bool
     */
    public function isMuteRole(): bool
    {
        $ok = in_array($this->role_id, [
            self::ROLE_BAN,
            self::ROLE_FORBIDDEN
        ]);
        if ($ok) {
            return true;
        }

        return $this->ban_post == self::BAN_POST_YES;
    }

    /**
     * 是否是创作者
     *
     * @return bool
     */
    public function isCreator(): bool
    {
        return $this->auth_status == 1;
    }

    /**
     * 是否是创作者
     *
     * @return bool
     */
    public function isReg(): bool
    {
        return $this->oauth_id == $this->username;
    }

    /**
     * 是否绑定邮箱
     *
     * @return bool
     */
    public function isBindEmail(): bool
    {
        return !empty($this->email) ? 1 : 0;
    }


    public static function findByUsername($username): ?MemberModel
    {
        /** @var self $member */
        $member = MemberModel::where('username', $username)->first();
        return $member;
    }

    public static function findByEmail($email): ?MemberModel
    {
        /** @var self $member */
        $member = MemberModel::where('email', $email)->first();
        return $member;
    }

    public function getLevelAttribute()
    {
        return $this->attributes['level'] ?? 0;
    }

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($member) {
            redis()->del(MemberModel::USER_REIDS_PREFIX . md5($member->oauth_type . $member->oauth_id));
            redis()->del(MemberModel::USER_REIDS_PREFIX . $member->aff);
        });
    }

    public static function firstAff($aff, $useWritePdo = false): ?MemberModel
    {
        $query = MemberModel::query();
        if ($useWritePdo) {
            $query->useWritePdo();
        }
        /** @var ?MemberModel $member */
        $member = $query->where('aff', '=', $aff)->first();
        return $member;
    }

    public static function findByUuid($s, $useWritePdo = false)
    {
        $query = MemberModel::query();
        if ($useWritePdo) {
            $query->useWritePdo();
        }
        /** @var ?MemberModel $member */
        $member = $query->where('uuid', '=', $s)->first();
        return $member;
    }

    /**
     * 用户对应的log members_log
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function session(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberLogModel::class, 'uuid', 'uuid');
    }

    public static function generateUUID($oauth_id, $oauth_type)
    {
        return md5($oauth_id . $oauth_type . config('user.uuid.salt'));
    }

    public function clearCached()
    {
        self::clearFor($this);
    }

    public function syncCached($key)
    {
        try {
            if (!redis()->exists($key)) {
                return;
            }
            $cached = cached($key)->prefix('')->suffix('')->serializerPHP();
            $tmp = $cached->getCache();
            if (!$tmp instanceof MemberModel) {
                return;
            }
            $tmp->attributes = array_merge($tmp->attributes, $this->attributes);
            $cached->setCache($tmp);
        } catch (\Throwable $e) {

        }
    }

    static function generatePassword($password)
    {
        return md5($password . config('encrypt.token_key'));
    }


    static function createAccount(MemberModel $member, $username, $password, $invitedAff, $is_email): array
    {
        try {
            if ($is_email) {
                $exists = MemberModel::onWriteConnection()->where('email', $username)->lockForUpdate()->first();
            } else {
                $exists = MemberModel::onWriteConnection()->where('username', $username)->lockForUpdate()->first();
            }
            if ($exists) {
                return [
                    '',
                    0,
                    '您输入的用户名已经被注册，请更换用户名重新尝试注册'
                ];
            }
            \DB::beginTransaction();
            if ($is_email) {
                $member->email = $username;
            }
            $member->username = $username;
            $member->password = $password;
            $member->oauth_id = $username;
            if ($invitedAff && !$member->invited_by) {
                $member->invited_by = get_num($invitedAff);
            }
            $member->saveOrFail();
            if ($member->invited_by && $member->username) {
                $invitedMember = MemberModel::firstAff($member->invited_by);
                if ($invitedMember) {
                    $invitedMember->increment('invited_reg_num');
                    $invitedMember->clearCached();

                    // 邀请人数据统计
                    jobs([
                        DayInviteModel::class,
                        'invite'
                    ], [
                        $invitedMember->aff,
                        $invitedMember->channel,
                        client_ip()
                    ]);
                }
            }

            $registerLog = new RegisterLogModel();
            $registerLog->uid = $member->uid;
            $registerLog->old_uid = $member->uid;
            $registerLog->oauth_type = $member->oauth_type;
            $registerLog->oauth_id = $member->oauth_id;
            $registerLog->aff = $member->aff;
            $registerLog->saveOrFail();
            \DB::commit();
            UserService::clearUserByUUID(md5($member->oauth_type . $member->oauth_id), $member->aff);
            $crypt = new LibCrypt();
            $token = $crypt->encryptToken($member->aff, $member->oauth_id, $member->oauth_type);
            return [
                $token,
                1,
                '成功'
            ];
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public static function bindEmail(MemberModel $member, $email)
    {
        $member->email = $email;
        $isOk = $member->save();
        test_assert($isOk, '绑定失败');
        $member->clearCached();
    }

    static function getLevelByExp($exp)
    {
        switch (true) {
            case $exp < 80:
                return 1;
            case $exp < 80 + 170:
                return 2;
            case $exp < 80 + 170 + 210:
                return 3;
            case $exp < 80 + 170 + 210 + 240:
                return 4;
            case $exp < 80 + 170 + 210 + 240 + 280:
                return 5;
            case $exp >= 80 + 170 + 210 + 240 + 280:
                $exp = $exp - (80 + 170 + 210 + 240 + 280);
                return floor($exp / 280 + 1) + 5;
        }
    }

    static function getNicknameByAff($aff)
    {
        $member = MemberModel::firstAff($aff);
        return $member->nickname ?? '会员_' . generate_code($aff);
    }

    static function getVipValue($vipLevel)
    {
        $vip = ProductModel::where('vip_level', $vipLevel)
            ->where('type', ProductModel::GOODS_TYPE_VIP)
            ->first();
        if ($vip) {
            return $vip->promo_price / 100;
        } else {
            return 0;
        }
    }

    static function isRegisterUser($member)
    {
        if ($member->username || $member->phone) {
            return true;
        }
        return false;
    }

    static function getUserDiscount($privilege, $mv)
    {
        return 1;
    }

    public function getTagListAttribute()
    {
        $tagStr = $this->attributes['tags'] ?? '';
        return array_filter(explode(',', $tagStr));
    }

    public function getVipLevelStrAttribute()
    {
        return $this->resolveConstantValue(self::VIP_LEVEL, 'vip_level');
    }

    protected function generateFreeNumKey(): string
    {
        return 'free-num:' . date('d') . '-' . $this->aff;
    }

    public function getFreeNum()
    {
        $val = (int) redis()->get($this->generateFreeNumKey());
        return max($val, 0);
    }

    public function decrFreeNum($value)
    {
        $key = $this->generateFreeNumKey();
        if (!redis()->exists($key)) { // 2022-09-09 之后可以删除
            $this->setFreeNum($this->free_view_cnt);
        }
        $val = (int) redis()->decrBy($key, $value);
        return max($val, 0);
    }

    public function setFreeNum($value, $ttl = 88400)
    {
        $key = $this->generateFreeNumKey();
        (int) redis()->set($key, $value);
        redis()->expire($key, $ttl);
        return $value;
    }


    public function subMoney($coin, int $source, string $desc, Model $data = null)
    {
        if ($this->money < $coin) {
            throw new RuntimeException('余额不足');
        }

        return transaction(function () use ($coin, $source, $desc, $data) {
            $isOk = MoneyLogModel::createSubLog($this, $coin, $source, $desc, $data);
            test_assert($isOk, '扣除用户余额失败');
            $isOk = self::query()
                ->where([
                    'aff' => $this->aff,
                    'money' => $this->money
                ])
                ->decrement('money', $coin);
            test_assert($isOk, '扣除用户余额失败');
            $this->money -= $coin;
            $this->syncOriginal();
            $this->clearCached();

            return true;
        });
    }


    public function addMoney($coin, int $source, string $desc = '', Model $data = null)
    {
        return transaction(function () use ($coin, $source, $desc, $data) {
            $isOk = MoneyLogModel::createAddLog($this, $coin, $source, $desc, $data);
            test_assert($isOk, '增加用户余额失败');
            $isOk = self::query()
                ->where([
                    'aff' => $this->aff,
                    'money' => $this->money
                ])
                ->increment('money', $coin);
            test_assert($isOk, '增加用户余额失败');
            $this->money += $coin;
            $this->syncOriginal();

            return true;
        });
    }


    public function subIncome(
        $coin,
        self $from,
        ?Model $dataModel,
        int $sourceType,
        $desc = ''
    ): bool {
        if ($sourceType == MoneyIncomeLogModel::SOURCE_GAOFEI) {
            if ($this->income_royalties < $coin) {
                throw new RuntimeException('稿费余额不足');
            }
        } else {
            if ($this->income_money < $coin) {
                throw new RuntimeException('收益余额不足');
            }
        }

        //        if ($this->income_money < $coin){
//            throw new RuntimeException('余额不足');
//        }
        return transaction(function () use ($coin, $dataModel, $sourceType, $from, $desc) {
            $isOk = MoneyIncomeLogModel::createSubLog($this, $coin, $sourceType, $desc, $dataModel, $from);
            test_assert($isOk, '处理收益日志失败');
            $query = $this->newQuery()->where('aff', $this->aff);
            $update = [];
            if ($sourceType == MoneyIncomeLogModel::SOURCE_GAOFEI) {
                $query->where('income_royalties', $this->income_royalties);
                $this->income_royalties -= $coin;
                $update['income_royalties'] = $this->income_royalties;
            } else {
                $query->where('income_money', $this->income_money);
                $this->income_money -= $coin;
                $update['income_money'] = $this->income_money;
            }
            $isOk = $query->update($update);
            test_assert($isOk, '影响收益失败');
            $this->syncOriginal();
            $this->clearCached();
            return true;
        });
    }

    public function addIncome(
        $coin,
        self $from,
        ?Model $dataModel,
        int $sourceType,
        $desc = '',
        $addTotal = true
    ): bool {
        return transaction(function () use ($coin, $dataModel, $sourceType, $from, $desc, $addTotal) {
            $isOk = MoneyIncomeLogModel::createAddLog($this, $coin, $sourceType, $desc, $dataModel, $from);
            test_assert($isOk, '处理收益日志失败');
            $query = $this->newQuery()->where('aff', $this->aff);
            $update = [];
            if ($sourceType == MoneyIncomeLogModel::SOURCE_GAOFEI) {
                $query->where('income_royalties', $this->income_royalties);
                $this->income_royalties += $coin;
                $update['income_royalties'] = $this->income_royalties;
                if ($addTotal) {
                    $this->income_royalties_total += $coin;
                    $update['income_royalties_total'] = $this->income_royalties_total;
                }
            } else {
                $query->where('income_money', $this->income_money);
                $this->income_money += $coin;
                $update['income_money'] = $this->income_money;
                if ($addTotal) {
                    $this->income_total += $coin;
                    $update['income_total'] = $this->income_total;
                }
            }
            $isOk = $query->update($update);
            test_assert($isOk, '影响收益失败');
            cached('')->clearGroup('list_withdraw');
            $this->syncOriginal();
            $this->clearCached();
            return true;
        });
    }

    public static function emailExist($email)
    {
        return self::where('email', $email)->exists();
    }

    public static function incrFollowCount($aff, $to_aff)
    {
        self::findByAff($aff)->increment('follow_count');
        self::findByAff($to_aff)->increment('followed_count');
    }

    public static function decrFollowCount($aff, $to_aff)
    {
        self::findByAff($aff)->decrement('follow_count');
        self::findByAff($to_aff)->decrement('followed_count');
    }

    /**
     * 获取远程用户分页列表
     */
    public static function getMembersList(array $params, int $limit, int $offset): array
    {
        $query = self::query();

        // 关键词搜索
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', '%' . $keyword . '%')
                    ->orWhere('oauth_id', 'like', '%' . $keyword . '%')
                    ->orWhere('username', 'like', '%' . $keyword . '%');
            });
        }

        // 设备类型筛选
        if (!empty($params['oauth_type'])) {
            $query->where('oauth_type', $params['oauth_type']);
        }

        // VIP等级筛选
        if (isset($params['vip']) && $params['vip'] !== '') {
            $query->where('vip_level', (int) $params['vip']);
        }

        $total = $query->count();
        $list = $query->orderByDesc('uid')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return [$list, $total];
    }

    /**
     * 获取远程用户详情
     */
    public static function getMemberDetail(int $uid)
    {
        return self::find($uid);
    }

    /**
     * 更新远程用户
     */
    public static function updateMember(int $uid, array $data): bool
    {
        $member = self::find($uid);
        test_assert($member, '用户不存在');

        // 只允许更新特定字段
        $allowedFields = ['nickname', 'vip_level', 'coins', 'money', 'ban_post', 'role_id'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return true;
        }

        $member->fill($updateData);
        $result = $member->save();

        // 清除缓存
        if ($result) {
            $member->clearCached();
        }

        return $result;
    }

    /**
     * 批量删除远程用户
     */
    public static function deleteMembers(array $uids): int
    {
        return self::whereIn('uid', $uids)->delete();
    }
}
