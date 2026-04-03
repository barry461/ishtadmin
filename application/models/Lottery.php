<?php

/**
 * @property string $id
 * @property string $lottery_name 抽奖的名称
 * @property string $lottery_begin 抽奖的开始时间
 * @property string $lottery_end 抽奖的结束时间
 * @property string $lottery_num 被抽奖了多少次
 * @property string $lottery_status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @mixin \Eloquent
 */
class LotteryModel extends BaseModel
{
    protected $table = 'lottery';
    protected $fillable = [
        'id',
        'lottery_name',
        'lottery_begin',
        'lottery_end',
        'lottery_num',
        'lottery_status',
        'created_at',
        'updated_at'
    ];
    protected $primaryKey = 'id';
    public $timestamps = true;

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS_TIPS = [
        self::STATUS_NO => '否',
        self::STATUS_YES => '是',
    ];

    public static function addLottery($id, $num = 1){
        self::find($id)->increment('lottery_num', $num);
    }

    /**
     * @param $id
     * @return LotteryModel ? null
     */
    public static function info($id){
        return cached('lottery:' . $id)
            ->group('lottery')
            ->fetchPhp(function () use ($id){
                return self::find($id);
            }, 60);
    }

    public static function isActive($id){
        $model = self::info($id);
        if (empty($model)){
            return false;
        }
        //下架
        if (!$model->lottery_status){
            return false;
        }
        //未开始
        if ($model->lottery_begin > \Carbon\Carbon::now()){
            return false;
        }
        //已结束
        if ($model->lottery_end < \Carbon\Carbon::now()){
            return false;
        }
        return true;
    }
}
