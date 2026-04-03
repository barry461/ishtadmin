<?php


use Illuminate\Events\Dispatcher;

/**
 * class InfoModel
 *
 * @property string $address 地址
 * @property string $business_hours 营业时间
 * @property int $uid 用户uid
 * @property int $chech_aff
 * @property int $cityCode 城市代码
 * @property int $coin 价格
 * @property string $desc 详细描述
 * @property int $env 环境
 * @property string $fee 消费
 * @property int $girl_age 妹子年龄
 * @property int $girl_face 妹子颜值
 * @property int $girl_num 妹子数量
 * @property int $girl_service 妹子服务
 * @property string $girl_service_type 妹子服务种类
 * @property int $id id
 * @property string $phone 电话
 * @property int $status 状态
 * @property string $title 标题
 * @property int $type 资源类型
 * @property int $post_type
 * @property string $contact_info
 * @property string $sync_ids
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 *
 *
 * @property MemberModel $member
 * @property MemberModel $checker
 * @property InfoPicModel[] $photos
 *
 * @date 2020-05-23 03:31:36
 *
 * @mixin \Eloquent
 */
class InfoModel extends BaseModel
{

    protected $table = "info";

    protected $primaryKey = 'id';

    protected $fillable = [
        '_id',
        'address',
        'uid',
        'check_aff',
        'business_hours',
        'cityCode',
        'coin',
        'created_at',
        'desc',
        'env',
        'fee',
        'girl_age',
        'girl_face',
        'girl_num',
        'girl_service',
        'girl_service_type',
        'phone',
        'status',
        'title',
        'type',
        'updated_at',
        'created_at',
        'view',
        'confirm',
        'fake',
        'buy',
        'favorite',
        'cityName',
        'girl_face_text',
        'source_link',
        'authentication',
        'tran_flag',
        'is_money',
        'price',
        'reason',
        'category',
        'source',
        'created_time',
        'post_type',
        'contact_info',
        'sync_ids'
    ];


    protected $hidden = ["source", "_id"];

    const SOURCE_ZHI68 = "zhi68";
    const SOURCE_XINGANG = "xingang";
    const SOURCE_CCDE = "ccde";
    const SOURCE = [
        self::SOURCE_ZHI68   => "zhi68.xyz",
        self::SOURCE_XINGANG => "网址.xingangstone.cn",
        self::SOURCE_CCDE    => "64ccde.xyz"
    ];

    const CATEGORY = [
        "baoyang" => "包养",
        "waiwei" => "外围",
        "loufeng" => "楼凤",
    ];

    const STATUS_INIT = 1; //待审核
    const STATUS_PASS = 2; //审核通过
    const STATUS_FAIL = 3; //审核失败
    const STATUS_DELETE = 4; //用户删除


    const STATUS_CANCEL_AGENT = 44; // 取消身份下资源
    const STATUS_PASS_BAN = 99; //审核通过被封禁
    const STATUS_INIT_BAN = 98; //待审核被封禁

    const STATUS_FAKE = 97;
    const STATUS = [
        self::STATUS_INIT   => '待审核',
        self::STATUS_PASS   => '审核通过',
        self::STATUS_FAIL   => '审核失败',
        self::STATUS_DELETE => '用户删除',
        self::STATUS_CANCEL_AGENT => '取消身份下资源',
        self::STATUS_PASS_BAN => '审核通过被封禁',
        self::STATUS_INIT_BAN => '待审核被封禁',
        self::STATUS_FAKE => '投诉处理中',
    ];

    const AUTH_ZERO = 0;   // 平台用户
    const AUTH_ONE = 1;    // 茶小二
    const AUTH = [
        self::AUTH_ZERO => '平台用户',
        self::AUTH_ONE  => '茶小二'
    ];

    const REDIS_KEY_DETAIL = 'info:detail:';
    const REDIS_KEY_LIST = 'info:list:';
    const REDIS_KEY_LIST_RANDOM = 'info:list:random:';
    const REDIS_KEY_POST_LIST = 'info:poster:list:';
    const REDIS_KEY_TODAY_POST = 'user:post:today:';
    const REDIS_KEY_SEARCH = 'user:search:';
    const REDIS_KEY_INFO_VIEW_SORT_SET = 'info:view:sort:set';
    const REDIS_KEY_TOP_LIST = 'info:top:list:';


    const AUTHENTICATION_FLAG = 1;
    const TRAN_FLAG = 1;
    const TRAN_FLAG_INIT = 0;
    const TRAN = [
        self::TRAN_FLAG_INIT => '不支持品茶宝',
        self::TRAN_FLAG => '支持品茶宝'
    ];
    const IS_MONEY = 1; //元宝贴
    const IS_COIN = 0; //铜钱贴

    const INIT_POST_NUM = [
        MemberModel::AGENT_TYPE_AGENT => 5,
        MemberModel::AGENT_TYPE_AUTH => 5
    ];

    const POST_TYPE_STORE = 0;
    const POST_TYPE_PERSONAL = 1;
    const POST_TYPE = [
        self::POST_TYPE_STORE    => '店家分享',
        self::POST_TYPE_PERSONAL => '个人分享'
    ];

    const ADD_POST_NUM_EXP = 200;

    protected static function booted()
    {
        static::setEventDispatcher(new Dispatcher());
        static::saved(function ($info) {
            redis()->del('user:post:num:' . $info->uid);
        });
    }

    public function photos()
    {
        return $this->hasMany(InfoPicModel::class, 'info_id', 'id')
            ->where("type", InfoPicModel::TYPE_PHOTO);
    }

    public function screenshot()
    {
        return $this->hasMany(InfoPicModel::class, 'info_id', 'id')
            ->where("type", InfoPicModel::TYPE_SCREENSHOT);
    }

    public function resources()
    {
        return $this->hasMany(InfoPicModel::class, 'info_id', 'id');
    }

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'uid');
    }

    public function type_str()
    {
        return $this->belongsTo(InfoTypeModel::class, "type", "id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author xiongba
     */
    public function checkerMember()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'chech_aff');
    }



    public static function queryCreatedAt($op, $time)
    {
        return self::where('created_time', $op, $time);
    }
    public static function getNowPostNum($aff, $post_type = self::POST_TYPE_PERSONAL)
    {
        return InfoModel::where('uid', $aff)
            ->where("post_type", $post_type)
            ->where('status', '!=', self::STATUS_DELETE)
            ->count();
    }
    public static function getMaxPostNum($member, $post_type = self::POST_TYPE_PERSONAL)
    {

        if($post_type == self::POST_TYPE_PERSONAL){
            $initPostNum = self::getInitPostNum($member->vip_level, $member->agent, $member->aff);
            $exp = $member->exp;
        }else{
            $initPostNum = self::getInitStorePostNum($member->username);
            $exp = $member->exp1;
        }

        if ($exp < self::ADD_POST_NUM_EXP) {
            return $initPostNum;
        } else {
            $max = floor($exp / self::ADD_POST_NUM_EXP + $initPostNum);
            if($max > 10){
                $max = 10;
            }
            return $max;
        }
    }
    public static function getInitPostNum($vip_level, $agent, $aff)
    {
        switch ($agent) {
            case MemberModel::AGENT_TYPE_AGENT:
                return self::INIT_POST_NUM[MemberModel::AGENT_TYPE_AGENT];
            case MemberModel::AGENT_TYPE_AUTH:
                return self::INIT_POST_NUM[MemberModel::AGENT_TYPE_AUTH];
            case MemberModel::AGENT_TYPE_PERSON:
                return 1;
            default:
//                if ($vip_level < 3) {
                if (!UserProductModel::isVip($aff)) {
                    return 0;
                } else {
                    // check if configure init num
                    $hasInitNum = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
                        ProductPrivilegeModel::RESOURCE_TYPE_INFO_PERSONAL,
                        ProductPrivilegeModel::PRIVILEGE_TYPE_INIT_NUM);
                    if($hasInitNum !== false){
                        return $hasInitNum;
                    }
                    return 1;
                }
        }
    }

    public static function getInitStorePostNum($username)
    {
        if(!empty($username)){
            $hasInitNum = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
                ProductPrivilegeModel::RESOURCE_TYPE_INFO_STORE,
                ProductPrivilegeModel::PRIVILEGE_TYPE_INIT_NUM);
            if($hasInitNum !== false){
                return $hasInitNum;
            }
            return 1;
        }else{
            return 0;
        }
    }


     public static function getResourceType($postType)
     {
         if ($postType == self::POST_TYPE_PERSONAL) {
             return ProductPrivilegeModel::RESOURCE_TYPE_INFO_PERSONAL;
         }else{
             return ProductPrivilegeModel::RESOURCE_TYPE_INFO_STORE;
         }
     }

    public static function getDiscount($privilege, $postType)
    {
        $resourceType = self::getResourceType($postType);
        $value = UserPrivilegeModel::hasPrivilege($privilege, $resourceType, ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
        if ($value !== false) {
            return $value / 100;
        }

        return 1;
    }

    public static function getUnlockNum($privilege, $postType)
    {
        $resourceType = self::getResourceType($postType);
        $value = UserPrivilegeModel::hasPrivilege($privilege, $resourceType, ProductPrivilegeModel::PRIVILEGE_TYPE_UNLOCK);
        if ($value !== false) {
            return $value;
        }

        return 0;
    }

    public static function synced($infoId)
    {
        return InfoSyncLogModel::query()->where("info_id", $infoId)->pluck("app_id")->toArray();
    }

    public function sync_app(){
        return $this->hasMany(InfoSyncLogModel::class, "info_id", "id");
    }
}
