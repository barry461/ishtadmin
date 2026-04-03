<?php

use Illuminate\Events\Dispatcher;

/**
 * class InfoVipModel
 *
 * @property int $id id
 * @property int $aff 用户id
 * @property string $title 标题
 * @property int $cityCode 城市代码
 * @property string $cityName
 * @property string $girl_num 妹子数量
 * @property string $girl_age 妹子年龄
 * @property int $girl_age_num
 * @property int $girl_height 身高
 * @property int $girl_cup
 * @property int $price_p 一炮价格
 * @property int $price_pp 两炮价格
 * @property int $price_all_night 包夜价格
 * @property string $cast_way 消费方式
 * @property int $girl_face 妹子颜值
 * @property string $girl_face_text
 * @property int $girl_service 妹子服务
 * @property string $girl_service_type 妹子服务种类
 * @property string $business_hours 营业时间
 * @property int $fee 预约金
 * @property string $desc 详细描述
 * @property string $price 价格
 * @property int $status 状态
 * @property int $view 查看次数
 * @property int $confirm
 * @property int $appointment
 * @property int $created_at
 * @property int $updated_at
 * @property int $type
 * @property string $category
 * @property int $price_op
 * @property string $wechat
 * @property string $qq
 * @property int $fee_ct
 *
 * @property-read InfoVipResourcesModel[]|\Illuminate\Support\Collection $resources
 * @property-read MemberModel $member
 *
 * @mixin \Eloquent
 */
class InfoVipModel extends BaseModel
{

    protected $table = "info_vip";

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'business_hours',
        'cityCode',
        'cityName',
        'confirm',
        'created_at',
        'desc',
        'fee',
        'price',
        'price_op',
        'girl_age',
        'girl_face',
        'girl_face_text',
        'girl_num',
        'girl_age_num',
        'girl_height',
        'girl_cup',
        'price_p',
        'price_pp',
        'price_all_night',
        'cast_way',
        'girl_service',
        'girl_service_type',
        'status',
        'title',
        'aff',
        'updated_at',
        'view',
        'video_valid',
        'vvip',
        'phone',
        'category',
        'address',
        'type',
        'mark',
        'sort',
        'wechat',
        'fee_ct',
        'comment_ct',
        'qq',
        'top'
    ];

    const STATUS_INIT = 1;//待审核
    const STATUS_PASS = 2;//审核通过
    const STATUS_FAIL = 3;//审核失败
    const STATUS_DELETE = 4;//用户删除
    const STATUS_REST = 5;//用户下架
    const STATUS_SLICE = 6;//待切片
    const STATUS_CANCEL_AGENT = 44; // 取消身份下资源
    const STATUS_PASS_BAN = 99;//审核通过被封禁
    const STATUS_INIT_BAN = 98;//待审核被封禁


    const STATUS = [
        self::STATUS_INIT         => '待审核',
        self::STATUS_PASS         => '审核通过',
        self::STATUS_FAIL         => '审核失败',
        self::STATUS_DELETE       => '用户删除',
        self::STATUS_REST         => '用户下架',
        self::STATUS_SLICE        => '切片中',
        self::STATUS_CANCEL_AGENT => '取消身份下资源',
        self::STATUS_PASS_BAN     => '审核通过被封禁',
        self::STATUS_INIT_BAN     => '待审核被封禁'
    ];

    const VIP_INIT = 0;  //普通VIP
    const VIP_ONE = 1;   //花魁阁楼
    const VIP = [
        self::VIP_INIT => '雅间',
        self::VIP_ONE  => '花魁阁楼'
    ];

    const VIDEO_INIT = 1;  // 视频认证
    const VIDEO = [
        self::VIDEO_INIT => '认证'
    ];
    const CATEGORY = [
        "baoyang" => "包养",
        "waiwei" => "外围",
        "loufeng" => "楼凤",
        "swktv" => "商务KTV",
        "zybjd" => "足浴保健店",
        "kuaican" => "快餐店",
        "huisuo" => "会所",
        "ljjz" => "良家兼职",
        "slspa" => "水疗spa馆",
    ];
    const CUP = [
        1 => 'A罩杯',
        2 => 'B罩杯',
        3 => 'C罩杯',
        4 => 'D罩杯',
        5 => 'E罩杯',
        6 => 'F+',
    ];
    const TYPE_VIP = 1;
    const TYPE_GIRL = 2;
    const TYPE_STORE = 3;
    const TYPE = [
        self::TYPE_VIP=> 'VIP',
        self::TYPE_GIRL  => '茶女郎',
        self::TYPE_STORE => '茶店铺'
    ];

    const GROUP_ENABLE = 1;
    const GROUP_DISABLE = 0;

    const REDIS_KEY_VIP_LIST = 'info:vip:lists:';
    const REDIS_KEY_VIP_LIST_RULE = 'info:vip:list:rule:';
    const REDIS_KEY_VIP_LIST_RULE_GROUP = 'info:vip:list:rule:group';
    const REDIS_KEY_GOODS_LIST = 'info:goods:list:';
    const REDIS_KEY_GOODS_LIST_GROUP = 'info:goods:list:group';
    const REDIS_KEY_GOODS_INDEX_LIST = 'info:goods:index:list:';
    const REDIS_KEY_VVIP_LIST = 'info:vvip:list:';
    const REDIS_KEY_CITY_LIST = 'info:vip:city:list';
    const REDIS_KEY_DETAIL = 'info:vip:detail:';
    const REDIS_KEY_INFO_VIP_VIEW_SORT_SET = 'info:vip:view:sort:set';
    const REDIS_KEY_INFO_VIP_USER_SORT = 'info:vip:user:sort:';
    const REDIS_KEY_INFO_VIP_FILTER_SORT = 'info:vip:sort:';
    const REDIS_KEY_INFO_VVIP_FILTER_SORT = 'info:vvip:sort:';
    const REDIS_KEY_INFO_VIP_USER_LIST = 'user:info:vip:list:';
    const REDIS_KEY_PERSON_DETAIL = 'user:info:person:detail:';
    const REDIS_KEY_STORE_DETAIL = 'user:info:store:detail:';
    const REDIS_KEY_VVIP_TOP_LIST = 'info:vvip:top:list:';
    const REDIS_KEY_VVIP_TOP_LIST_GROUP = 'info:vvip:top:list:group';
    const REDIS_KEY_VVIP_TEAGIRL_TOP_LIST = 'info:vvip:teagirl:top:list:';
    const REDIS_KEY_ALL_City = 'info:vip:city:all:';

    const REDIS_KEY_VIP_INFO_COUNT_BY_AFF = 'vip:info:count:';


    public function getCreatedAtAttribute($value): string
    {
        return parent::getCreatedAtAttribute($value); // TODO: Change the autogenerated stub
    }

    public function getUpdatedAtAttribute($value): string
    {
        return parent::getCreatedAtAttribute($value); // TODO: Change the autogenerated stub
    }

    protected static function booted()
    {
        static::setEventDispatcher(new Dispatcher());
        static::saved(function ($info) {
            redis()->del(self::REDIS_KEY_VIP_INFO_COUNT_BY_AFF . $info->aff);
            redis()->del('user:post:num:' . $info->aff);
        });
    }

    public function resources()
    {
        return $this->hasMany(InfoVipResourcesModel::class, 'info_id', 'id');
    }

    public function tea()
    {
        return $this->hasMany(AuthGirlModel::class, 'aff', 'aff');
    }

    public function authinfo()
    {
        return $this->hasMany(AuthGirlModel::class, 'info_id', 'id');
    }

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function tags($id)
    {
        $tags = \InfoVipTagModel::select('tags.name', 'tags.id')
            ->join('tags', 'tags.id', '=', 'info_vip_tag.tag_id')
            ->where('info_vip_tag.info_id', $id)
            ->get()
            ->toArray();
        shuffle($tags);
        return $tags;
    }

    protected $appends = ['updated_str', 'created_str', 'girl_cup_str'];

    public function getCreatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
    }

    public function getUpdatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['updated_at'] ?? 0);
    }

    public function getGirlCupStrAttribute()
    {
        return self::CUP[$this->attributes['girl_cup'] ?? 1];
    }

    public static function queryCreatedAt($op, $time)
    {
        return self::where('created_at', $op, $time);
    }

    static function getAgentDealNum($aff)
    {
        $redisKey = 'user:deal:num:' . $aff;
        $rs = redis()->getWithSerialize($redisKey);
        if (!$rs) {
            $appointmentNum = AppointmentModel::join('info_vip', 'info_vip.id', '=', 'appointment.info_id')
                ->where('info_vip.aff', $aff)
                ->whereIn('appointment.status', [AppointmentModel::STATUS_CONFIRM, AppointmentModel::STATUS_FINISH])
                ->count();
            $requireNum = RequireModel::where('agentAff', $aff)
                ->where('status', RequireModel::STATUS_FINISH)->count();
            $rs = $appointmentNum + $requireNum;
            redis()->setWithSerialize($redisKey, $rs, 7200);
        }
        return $rs;

    }

    static function getAgentPostNum($aff)
    {
        $redisKey = 'user:post:num:' . $aff;
        $rs = redis()->getWithSerialize($redisKey);
        if (!$rs) {
            $rs['postVipInfoNum'] = InfoVipModel::where('aff', $aff)
                ->where('status', InfoVipModel::STATUS_PASS)
                ->count();
            $rs['postInfoNum'] = InfoModel::where('uid', $aff)
                ->where('status', InfoModel::STATUS_PASS)
                ->count();
            $rs['postConfirmNum'] = ConfirmModel::where('uid', $aff)
                ->where('status', ConfirmModel::STATUS_PASS)
                ->count();
            redis()->setWithSerialize($redisKey, $rs, 7200);
        }
        return $rs;
    }

    static function getStoreByAff($aff)
    {
        $store = self::where('aff', $aff)
            ->where('type', self::TYPE_STORE)
            ->first();
        return $store;
    }

    static function getPersonByAff($aff)
    {
        $person = self::where('aff', $aff)
            ->where('type', self::TYPE_GIRL)
            ->first();
        return $person;
    }

    static function getStoreById($id)
    {
        $store = self::where('id', $id)
            ->first();
        return $store;
    }

    static function getPersonById($id)
    {
        $store = self::where('id', $id)
            ->first();
        return $store;
    }

    public function auth()
    {
        return $this->hasMany(AuthGirlModel::class, 'aff', 'aff');
    }

    public static function getResourceType($info)
    {
        if ($info->type == self::TYPE_VIP) {
            if ($info->vvip == 1) {
                return ProductPrivilegeModel::RESOURCE_TYPE_INFO_VIP_VVIP;
            } else {
                return ProductPrivilegeModel::RESOURCE_TYPE_INFO_VIP;
            }
        } elseif ($info->type == self::TYPE_GIRL) {
            return ProductPrivilegeModel::RESOURCE_TYPE_INFO_VIP_GIRL;
        } elseif ($info->type == self::TYPE_STORE) {
            return ProductPrivilegeModel::RESOURCE_TYPE_INFO_VIP_STORE;
        }
    }

    public static function hasPrivilege($aff, $info, $userPrivilege, $privilegeType): bool
    {
        if(is_array($info)){ // convert to object
            $info = json_decode(json_encode($info));
        }

        $sourceType = self::getResourceType($info);
        return UserPrivilegeModel::hasPrivilegeAndSubTimePrivilege(
            $userPrivilege,
            $sourceType,
            $privilegeType,
            $aff
        );
    }
}