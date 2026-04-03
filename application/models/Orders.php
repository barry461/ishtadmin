<?php

use service\UserService;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * class OrdersModel
 *
 * @property int $id
 * @property int $amount 订单金额，单位分
 * @property string $app_order 第三方订单号
 * @property string $build_id 渠道标识
 * @property string $channel 订单支付渠道来源1-自有渠道
 * @property int $created_at
 * @property string $desc_img 上传成功截图
 * @property string $descp 我方对订单简单说明
 * @property int $expired_at 过期时间 天数
 * @property int $gift_diamond 赠送钻石
 * @property string $goods_info 商品快照
 * @property string $msg 支付接口返回的状态说明
 * @property string $oauth_type 设备
 * @property string $order_id 唯一订单号
 * @property int $order_type 订单类型 同产品类型
 * @property int $pay_amount 实付金额,单位分
 * @property string $pay_type online线上充值/agent代理充值
 * @property string $pay_url 支付链接
 * @property string $payway 支付方式
 * @property int $product_id
 * @property int $status 订单状态: 0-未支付，2-支付中，3-支付完成，99-交易失败
 * @property int $updated_at
 * @property string $uuid 用户uuid标识
 * @date 2020-02-20 10:40:33
 *
 * @property-read ProductModel $product
 * @property-read ProductModel $product_snapshot
 * @property-read MemberModel $member
 *
 * @mixin \Eloquent
 */
class OrdersModel extends BaseModel
{
    protected $table = 'orders';

    const REDIS_KEY_ORDER_LIST = 'order:list:';
    const REDIS_STAT_KEY = 'admin_stat';
    const REDIS_INIT_COUNT = 'user:init:';

    const PAY_STAT_TO_PAY = 0;
    const PAY_STAT_PAY_ING = 2;
    const PAY_STAT_SUCCESS = 3;
    const PAY_STAT_FAIL = 99;


    const PAY_STAT = [
        self::PAY_STAT_TO_PAY  => "未支付",
        self::PAY_STAT_PAY_ING => "支付中",
        self::PAY_STAT_SUCCESS => "支付完成",
        self::PAY_STAT_FAIL  => "交易失败",
    ];

    const OAUTH_DEVICE_OTHER = 'other';
    const OAUTH_DEVICE_IOS = 'ios';
    const OAUTH_DEVICE_ANDROID = 'android';
    const OAUTH_DEVICE_WEB = 'web';

    const OAUTH_DEVICE = [
        self::OAUTH_DEVICE_IOS => '苹果',
        self::OAUTH_DEVICE_ANDROID => '安卓',
        self::OAUTH_DEVICE_WEB => 'web',
        self::OAUTH_DEVICE_OTHER => '其它',
    ];

    protected $fillable = array(
        'uuid',
        'oauth_type',
        'product_id',
        'order_id',
        'app_order',
        'descp',
        'order_type',
        'amount',
        'pay_amount',
        'payway',
        'pay_url',
        'status',
        'msg',
        'channel',
        'pay_type_sdk',
        'updated_at',
        'created_at',
        'expired_at',
        'is_callback',
        'pay_type',
        'desc_img',
        'gift_diamond',
        'goods_info',
        'build_id',
    );

    protected $appends = ['amount_yuan', 'pay_amount_yuan' , 'product_snapshot'];


    // public function getCreatedStrAttribute()
    // {
    //     return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
    // }
    // public function getUpdatedStrAttribute()
    // {
    //     return date('Y-m-d H:i:s', $this->attributes['updated_at'] ?? 0);
    // }
    public function getAmountYuanAttribute()
    {
        if (!isset($this->attributes['amount'])) {
            // trigger_error('没有找到amount的值'.var_export($this->attributes,true));
            return 0;
        } else {
            return sprintf("%.2f",    div_allow_zero($this->attributes['amount'], 10000));
        }
    }
    public function getProductSnapshotAttribute()
    {
        $good = $this->attributes['goods_info'] ?? null;
        if (empty($good)){
            return null;
        }
        $good = is_string($good) ? json_decode($good , true) : null;
        if (empty($good)){
            return null;
        }
        /** @var ProductModel $object */
        $object = ProductModel::make();
        $object->setRawAttributes($good , true);
        $object->exists = true;
        return $object;
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class , 'uuid' , 'uuid');
    }


    public function getPayAmountYuanAttribute()
    {
        if (!isset($this->attributes['pay_amount'])) {
            // trigger_error('没有找到amount的值'.var_export($this->attributes,true));
            return 0;
        } else {
            return sprintf("%.2f",  div_allow_zero($this->attributes['pay_amount'], 10000));
        }
    }

    public function product()
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

    public function user()
    {
        return $this->hasOne(MemberModel::class, 'uuid', 'uuid');
    }

    public function getSeriesData($where, $name, $extWhere = [])
    {
        $query = $this->select(DB::raw("count(id) as c,FROM_UNIXTIME(created_at, '%Y-%m-%d') as date"))->groupBy(DB::raw('date'));
        $array = $query->where($where)->where($extWhere)->get();
        $orderSeries = [];
        foreach ($array as $item) {
            $orderSeries[$item->date] = $item->c;
        }
        return [
            'name' => $name,
            'data' => $orderSeries
        ];
    }

    public function getSeriesDataAmount($where, $name, $extWhere = [])
    {
        $query = $this->select(DB::raw("sum(pay_amount) as c,FROM_UNIXTIME(created_at, '%Y-%m-%d') as date"))->groupBy(DB::raw('date'));
        $array = $query->where($where)->where($extWhere)->get();
        $orderSeries = [];
        foreach ($array as $item) {
            $orderSeries[$item->date] = sprintf("%.2f", div_allow_zero($item->c, 10000));
        }
        return [
            'name' => $name,
            'data' => $orderSeries
        ];
    }


    static function reportChannel($member, $value)
    {
        $timestamp = time();
        $today = Carbon::today()->format('Ymd');
        $channel = \ChannelModel::query()->where('channel_id', $member->channel)->first();
        // return $channel->channel_num ?? $channel_num;//兼容错误的channel_id
        $data = [
            'type'      => 'addOrder',
            'content'   => [
                'product_id'    => 12,
                'order_sn'      => 'hbcg-' . $member->aff . $today . $timestamp,
                'order_type'    => '0', // 0 vip  1 j金币
                'uuid'          => $member->uuid,
                'invite_by'    => $member->invited_by,
                'channel'       => $channel->channel_num,
                'order_amount'  => $value, // 单位为元，请注意
                'status'        => 0,
                'created_at'    => $timestamp,
                'phone'    => '',
            ]
        ];
        $rs = \tools\Channel::manSend($data);
        // var_dump($rs);exit;
        $data = [
            'type' => 'updateOrder',
            'content' => [
                'product_id' => 12,
                'order_sn' => 'hbcg-' . $member->aff . $today . $timestamp,
                'pay_amount' => $value, // 单位为元，请注意
                'status' => '1',
                'updated_at' => $timestamp + 10,
            ]
        ];
        \tools\Channel::manSend($data);
    }
}
