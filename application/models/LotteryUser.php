<?php

/**
 * @property int $id
 * @property int $aff aff
 * @property int $lottery_id 活动ID
 * @property int $val 抽奖次数
 * @property int $total 总抽奖次数
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @mixin \Eloquent
 */
class LotteryUserModel extends BaseModel
{
    protected $table = 'lottery_user';

    protected $fillable = [
        'id',
        'aff',
        'lottery_id',
        'val',
        'total',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    public function lottery(){
        return $this->hasOne(LotteryModel:: class, 'id', 'lottery_id');
    }

    public static function addUserLottery(MemberModel $member, $val, $lottery_id){
        /** @var LotteryUserModel $userLottery */
        $userLottery = self::where('aff', $member->aff)->where('lottery_id', $lottery_id)->first();
        if (!$userLottery){
            self::addLog($member->aff, $val, $lottery_id);
            return $val;
        }else{
            $userLottery->val = max($userLottery->val + $val, 0);
            if ($val > 0){
                $userLottery->total += $val;
            }
            $userLottery->updated_at = \Carbon\Carbon::now();
            $userLottery->save();
            return $userLottery->val;
        }
    }

    public static function addLog($aff, $val, $lottery_id){
        self::create(
            [
                'aff'           => $aff,
                'lottery_id'    => $lottery_id,
                'val'           => max($val, 0),
                'total'         => max($val, 0),
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now(),
            ]
        );
    }

    public static function getInfoByAff($aff, $lottery_id){
        return self::onWriteConnection()->where('aff', $aff)->where('lottery_id', $lottery_id)->first();
    }
}
