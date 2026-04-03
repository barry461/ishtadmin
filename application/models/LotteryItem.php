<?php


/**
 * @property int $item_id
 * @property string $item_title 奖品标题
 * @property string $item_name 奖品名称
 * @property string $item_rate 奖品概率
 * @property string $item_icon 奖品图片
 * @property int $item_sort 排序
 * @property int $item_status 状态
 * @property int $lottery_id 抽奖id
 * @property int $giveaway_type 赠品类型
 * @property int $giveaway_id 赠品位置ID
 * @property int $giveaway_num 金币数量/会员ID
 * @property int $is_show 是否展示
 * @property int $is_win 是否中奖
 * @property int $total_lucky 奖品总数
 *
 * @mixin \Eloquent
 */
class LotteryItemModel extends BaseModel
{
    protected $table = 'lottery_item';
    protected $fillable = [
        'item_id',
        'item_title',
        'item_name',
        'item_rate',
        'item_icon',
        'item_sort',
        'item_status',
        'lottery_id',
        'giveaway_type',
        'giveaway_id',
        'giveaway_num',
        'is_show',
        'is_win',
        'total_lucky',
    ];
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    const STATUS_NO = 0;
    const STATUS_OK = 1;
    const STATUS_TIPS = [
        self::STATUS_NO => '关闭',
        self::STATUS_OK => '开启',
    ];

    const SHOW_NO = 0;
    const SHOW_OK = 1;
    const SHOW_TIPS = [
        self::SHOW_NO => '否',
        self::SHOW_OK => '是',
    ];

    const WIN_NO = 0;
    const WIN_OK = 1;
    const WIN_TIPS = [
        self::WIN_NO => '否',
        self::WIN_OK => '是',
    ];

    const GIVEAWAY_TYPE_NONE = 'none';
    const GIVEAWAY_TYPE_VIP_JK = 'vip_jk';
    const GIVEAWAY_TYPE_COINS = 'coins';
    const GIVEAWAY_TYPE_MANUAL = 'manual';
    const GIVEAWAY_TYPE = [
        self::GIVEAWAY_TYPE_NONE                => '未中奖',
        self::GIVEAWAY_TYPE_VIP_JK              => '季卡',
        self::GIVEAWAY_TYPE_COINS               => '金币',
        self::GIVEAWAY_TYPE_MANUAL              => '人工处理',
    ];

    public function lottery(){
        return $this->hasOne(LotteryModel::class, 'id', 'lottery_id');
    }

    public static function decLuckyNum($id, $num = 1){
        return self::find($id)->decrement('total_lucky', $num);
    }

    public static function bad($lottery_id){
        return cached('lottery:bad:' . $lottery_id)
            ->group('lottery')
            ->fetchPhp(function () use ($lottery_id){
                return self::where('lottery_id', $lottery_id)
                    ->where('item_status', self::STATUS_OK)
                    ->where('giveaway_type', self::GIVEAWAY_TYPE_NONE)
                    ->orderByDesc('item_id')
                    ->first();
            },60);
    }

    public static function info($id){
        return cached('lottery:' . $id)
            ->group('lottery')
            ->fetchPhp(function () use ($id){
                return self::find($id);
            }, 60);
    }

    public static function draw($lottery_id, $num)
    {
        $lottery_items = cached('lottery_item_' . $lottery_id)
            ->fetchPhp(function () use ($lottery_id){
                return self::where('lottery_id', $lottery_id)
                    ->where('item_status', self::STATUS_OK)
                    ->where('is_win', self::WIN_OK)
                    ->get()
                    ->keyBy('item_id');
            }, 120);
        $items = [];
        $_self_items = $lottery_items->each(function (self $item) use (&$items) {
            $item_id = $item->item_id;
            if ($item->total_lucky == 0){
                $item_id = setting('lottery_xxfg_id', 9);
            }
            for ($i = 0; $i < $item->item_rate; $i++) {
                $items[] = $item_id;
            }
        });
        $arr = [];
        for ($i = 1; $i<= $num; $i++){
            $item_id = collect($items)->random();
            $arr[] = $_self_items[$item_id];
        }
        return $arr;
    }
}
