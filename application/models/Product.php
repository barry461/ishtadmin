<?php

use Carbon\Carbon;
use \Illuminate\Support\Collection;

/**
 * class ProductModel
 *
 * @property int $id
 * @property int $type 1:冲天数;2:冲币
 * @property int $visible_type 可见类型
 * @property string $pname 产品名称
 * @property string $img 图片
 * @property int $price 价格:单位分
 * @property int $promo_price 推广价格:单位分
 * @property int $valid_date VIP多少天
 * @property int $coins 多少币
 * @property int $free_coins 赠送多少币
 * @property string $pay_type 支付方式online线上支付agent代理支付
 * @property int $status 产品状态 0:未上架 1:上架 2:下架
 * @property int $sort_order 排序
 * @property int $payway_wechat 微信支付1支持0不支持
 * @property int $payway_bank 银联支付1支持0不支持
 * @property int $payway_alipay 支付宝支付1支持0不支持
 * @property int $payway_visa 01
 * @property int $payway_huabei 0支持1不支持
 * @property string $give_tip 赠送提示
 * @property string $description 产品描述
 * @property string $url 跳转地址
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property  string $img_url
 * @property int $vip_level vip等级 0普通 1 月卡 2季卡 3 年卡
 * @property string $price_yuan
 * @property string $promo_price_yuan
 * @property string $free_view_day 免费观看金币视频时间
 *
 * @property ProductPrivilegeModel[]|Collection $privilege
 * @property PayMapModel[]|Collection $pays
 * @property ProductRightMapModel[]|Collection $rights
 *
 * @mixin \Eloquent
 */
class ProductModel extends BaseModel
{

    protected $table = "product";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'type',
        'visible_type',
        'pname',
        'img',
        'second_img',
        'price',
        'promo_price',
        'discount',
        'valid_date',
        'coins',
        'free_coins',
        'give_tip',
        'promo_expire_time',
        'status',
        'sort_order',
        'description',
        'updated_at',
        'created_at',
        'vip_level',
        'show_more',
        'free_view_day',
    ];

    protected $appends = ['price_yuan' , 'promo_price_yuan'];
    protected $hidden = ['status' , 'updated_at' , 'created_at' , 'promo_expire_time'];


    const GOODS_TYPE_VIP = 1;
    const GOODS_TYPE_COIN = 2;
    const GOODS_TYPE_COIN_BUY_VIP = 3;
    const GOODS_TYPE_ACTIVITY = 4;
    const GOODS_TYPE_SPECIAL_COIN_CARD = 5;
    const GOODS_TYPE_GAME = 99;

    const GOODS_TYPES = [
        self::GOODS_TYPE_VIP => 'vip',
        self::GOODS_TYPE_COIN => '金币',
        self::GOODS_TYPE_COIN_BUY_VIP => '金币购买vip',
        self::GOODS_TYPE_ACTIVITY => '活动',
        self::GOODS_TYPE_SPECIAL_COIN_CARD => '特殊金币卡',
        self::GOODS_TYPE_GAME => '游戏'
    ];

    const PAY_TYPE_ONLINE = 'online';
    const PAY_TYPE_agent = 'agent';

    const PAY_TYPE = [
        self::PAY_TYPE_ONLINE => '在线支付',
        self::PAY_TYPE_agent => '代理支付'
    ];

    //产品状态 0:未上架 1:上架 2:下架
    const STATUS_NOT_LISTED = 0;
    const STATUS_LISTED = 1;
    const STATUS_DOWN_LISTED = 2;
    const STATUS_INVITATION_REWARD = 3;

    const STATUS = [
        self::STATUS_NOT_LISTED => '未上架',
        self::STATUS_LISTED => '上架',
        self::STATUS_DOWN_LISTED => '下架',
        self::STATUS_INVITATION_REWARD => '邀请赠送卡'
    ];

    //支持微信支付 0:未上架 1:上架 2:下架
    const PAYWAY_WECHAT_ENABLE = 0;
    const PAYWAY_WECHAT_DISABLE = 1;

    const PAYWAY_WECHAT = [
        self::PAYWAY_WECHAT_ENABLE => '支持',
        self::PAYWAY_WECHAT_DISABLE => '不支持'
    ];

    //支持银联支付 0:未上架 1:上架 2:下架
    const PAYWAY_BANK_ENABLE = 0;
    const PAYWAY_BANK_DISABLE = 1;

    const PAYWAY_BANK = [
        self::PAYWAY_BANK_ENABLE => '支持',
        self::PAYWAY_BANK_DISABLE => '不支持'
    ];

    //支持银联支付 0:未上架 1:上架 2:下架
    const PAYWAY_ALIPAY_ENABLE = 0;
    const PAYWAY_ALIPAY_DISABLE = 1;

    const PAYWAY_ALIPAY = [
        self::PAYWAY_ALIPAY_ENABLE => '支持',
        self::PAYWAY_ALIPAY_DISABLE => '不支持'
    ];

    //支持银联支付 0:未上架 1:上架 2:下架
    const PAYWAY_VISA_ENABLE = 0;
    const PAYWAY_VISA_DISABLE = 1;

    const PAYWAY_VISA = [
        self::PAYWAY_VISA_ENABLE => '支持',
        self::PAYWAY_VISA_DISABLE => '不支持'
    ];

    //支持银联支付 0:未上架 1:上架 2:下架
    const PAYWAY_HUABEI_ENABLE = 0;
    const PAYWAY_HUABEI_DISABLE = 1;

    const PAYWAY_HUABEI = [
        self::PAYWAY_HUABEI_ENABLE => '支持',
        self::PAYWAY_HUABEI_DISABLE => '不支持'
    ];

    // 显示更多 
    const SHOW_MORE_INSIDE = 0;
    const SHOW_MORE_OUTSIDE = 1;
    const SHOW_MORE = [
        self::SHOW_MORE_INSIDE => '单项卡',
        self::SHOW_MORE_OUTSIDE => '全能卡'
    ];


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
        self::VIP_LEVEL_NORMAL    => '普通',
        self::VIP_LEVEL_TMP       => '临时',
        self::VIP_LEVEL_WEEKLY    => '周卡',
        self::VIP_LEVEL_MONTHLY   => '月卡',
        self::VIP_LEVEL_QUARTERLY => '季卡',
        self::VIP_LEVEL_HALFYEAR  => '半年卡',
        self::VIP_LEVEL_ANNUAL    => '年卡',
        self::VIP_LEVEL_TWO_YEAR  => '两年卡',
        self::VIP_LEVEL_LONG      => '永久卡',
        self::VIP_LEVEL_ANNUAL_ZX => '尊享卡',
    ];

    const VISIBLE_TYPE_ALL = 0;
    const VISIBLE_TYPE_NEW_USER = 1;
    const VISIBLE_TYPE = [
        self::VISIBLE_TYPE_ALL => '所有',
        self::VISIBLE_TYPE_NEW_USER => '新用户专享',
    ];

    const SPECIAL_COIN_CARD_ID = 8;

    public function getPromoExpireTimeAttribute($value):string
    {
        return $this->getCreatedAtAttribute($value);
    }

    public function getPriceYuanAttribute()
    {
        return moneyFormat($this->attributes['price'] ?? 0);
    }

    public function getPromoPriceYuanAttribute()
    {
        return moneyFormat($this->attributes['promo_price'] ?? 0);
    }

    public function getImgAttribute():string
    {
        return url_image($this->attributes['img'] ?? '');
    }
    public function setImgAttribute($value)
    {
        $this->resetSetPathAttribute('img',$value);
    }

    public function getSecondImgAttribute():string
    {
        return url_image($this->attributes['second_img'] ?? '');
    }
    public function setSecondImgAttribute($value)
    {
        $this->resetSetPathAttribute('second_img',$value);
    }

    public function privilege(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductPrivilegeModel::class, 'product_id', 'id');
    }

    public function rights(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductRightMapModel::class, 'product_id', 'id')->where('status' ,ProductRightMapModel::STATUS_LISTED);
    }

    public function pays(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PayMapModel::class, 'product_id', 'id');
    }


    static function isBuyCoinMonthCard($uuid)
    {
        $order = OrdersModel::where('uuid', $uuid)
            ->where('order_type', ProductModel::GOODS_TYPE_SPECIAL_COIN_CARD)
            ->where('status', OrdersModel::PAY_STAT_SUCCESS)
            // ->where('updated_at', '>', Carbon::now()->subDays(30))
            ->first();
        if ($order) {
            $product = ProductModel::find($order->product_id);
            if ($product) {
                if ($order->updated_at > Carbon::now()->subDays($product->valid_date)) {
                    return [
                        'orderId' => $order->id,
                        'valid_date' => $product->valid_date,
                        'getCoins' => $product->coins,
                        'valid_date' => Carbon::parse($order->updated_at)->addDays($product->valid_date)->toDateTimeString()
                    ];
                }
            }
            return false;
        }
        return false;
    }
}
