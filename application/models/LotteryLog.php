<?php

/**
 * @property int $log_id 日志ID
 * @property int $uid 用户uid
 * @property int $item_id 奖品奖项id
 * @property string $item_name 奖品名称
 * @property string $item_icon 奖品图片
 * @property int $lottery_id 抽奖id
 * @property int $giveaway_type 赠品类型
 * @property int $giveaway_id 赠品类型
 * @property int $giveaway_num 赠品数量
 * @property string $created_at 创建时间
 * @property string $nickname 用户昵称
 * @property int $pay_time 付费类型 0免费次数 1金币
 * @property int $coins 消耗金币数
 * @property string $format_date
 *
 * @mixin \Eloquent
 */
class LotteryLogModel extends BaseModel
{
    protected $table = 'lottery_log';

    protected $fillable = [
        'log_id',
        'uid',
        'item_id',
        'item_name',
        'item_icon',
        'lottery_id',
        'giveaway_type',
        'giveaway_id',
        'giveaway_num',
        'created_at',
        'nickname',
        'pay_time',
        'coins',
        'format_date',
    ];

    protected $primaryKey = 'log_id';
    public $timestamps = false;

    const PAY_FREE_NUM = 0;
    const PAY_FREE_COINS = 1;
    const PAY_TYPE_TIPS = [
        self::PAY_FREE_NUM => '免费次数',
        self::PAY_FREE_COINS => '金币',
    ];

    public function lottery(){
        return $this->hasOne(LotteryModel::class, 'id', 'lottery_id');
    }

    public static function createBy(MemberModel $member, LotteryItemModel $item, $pay_time)
    {
        return self::create(
            [
                'uid'           => $member->uid,
                'nickname'      => $member->nickname,
                'item_id'       => $item->item_id,
                'item_name'     => $item->item_name,
                'item_icon'     => '',
                'lottery_id'    => $item->lottery_id,
                'giveaway_type' => $item->giveaway_type,
                'giveaway_id'   => $item->giveaway_id,
                'giveaway_num'  => $item->giveaway_num,
                'pay_time'      => $pay_time,
                'coins'         => $pay_time == self::PAY_FREE_COINS ? 10 : 0,
                'created_at'    => \Carbon\Carbon::now(),
                'format_date'   => date('Y-m-d'),
            ]
        );
    }

    public static function list($uid, $lottery_id){
        return self::selectRaw('item_name as text,created_at as time')
            ->where('uid', $uid)
            ->where('lottery_id', $lottery_id)
            ->where('giveaway_type', '!=', LotteryItemModel::GIVEAWAY_TYPE_NONE)
            ->orderByDesc('log_id')
            ->limit(50)
            ->get()
            ->map(function (LotteryLogModel $model){
                $model->time = date('Y/m/d H:i:s', strtotime($model->time));
                return $model;
            });
    }
}
