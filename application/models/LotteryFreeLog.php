<?php

/**
 * @property string $id
 * @property string $aff 用户aff
 * @property string $type
 * @property string $value 充值金额/邀请人数
 * @property string $lottery_num 抽奖次数
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class LotteryFreeLogModel extends BaseModel
{
    protected $table = 'lottery_free_log';
    protected $fillable = [
        'id',
        'aff',
        'type',
        'value',
        'lottery_num',
        'created_at',
        'updated_at'
    ];
    protected $primaryKey = 'id';
    public $timestamps = true;

    const TYPE_INVITE = 1;
    const TYPE_RECHARGE = 2;

    //邀请赠送免费次数
    public static function invite($aff, $num = 1){
        $lottery_id = (int)setting('cj_active_id', 0);
        if ($lottery_id == 0){
            return null;
        }
        $is_active = LotteryModel::isActive($lottery_id);
        if (!$is_active) {
            return null;
        }

        /** @var self $model */
        $model = self::where('aff', $aff)
            ->where('type', self::TYPE_INVITE)
            ->first();
        if (empty($model)){
            $model = self::make();
            $model->aff = $aff;
            $model->type = self::TYPE_INVITE;
            $model->value = $num;
            $model->lottery_num = 0;
            $model->created_at = \Carbon\Carbon::now();
            $model->updated_at = \Carbon\Carbon::now();
            $model->save();
            $before_num = 0;
            $after_num = intval($num / 2);
        }else{
            $model->increment('value', $num);
            $before_num = $model->lottery_num;
            $after_num = intval($model->value / 2);
        }
        //已经有7次了
        if ($before_num >= 7){
            return null;
        }
        $after_num = min($after_num, 7);
        //计算次数
        $add_num = $after_num - $before_num;
        if ($add_num > 0){
            $model->lottery_num = $after_num;
            $model->save();
            /** @var LotteryUserModel $lottery_user */
            $lottery_user = LotteryUserModel::where('aff', $aff)->where('lottery_id', $lottery_id)->first();
            if (empty($lottery_user)){
                $lottery_user = LotteryUserModel::make();
                $lottery_user->aff = $aff;
                $lottery_user->lottery_id = $lottery_id;
                $lottery_user->val = $add_num;
                $lottery_user->total = $add_num;
                $lottery_user->created_at = \Carbon\Carbon::now();
                $lottery_user->updated_at = \Carbon\Carbon::now();
                $lottery_user->save();
            }else{
                $lottery_user->increment('val', $add_num, ['total' => \DB::raw('total + ' . $add_num)]);
            }
        }
    }

    //充值赠送免费次数
    public static function recharge($aff, $amount){
        $lottery_id = (int)setting('cj_active_id');
        if ($lottery_id == 0){
            return null;
        }
        $is_active = LotteryModel::isActive($lottery_id);
        if (!$is_active) {
            return null;
        }

        /** @var self $model */
        $model = self::where('aff', $aff)
            ->where('type', self::TYPE_RECHARGE)
            ->first();
        if (empty($model)){
            $model = self::make();
            $model->aff = $aff;
            $model->type = self::TYPE_RECHARGE;
            $model->value = $amount;
            $model->lottery_num = 0;
            $model->created_at = \Carbon\Carbon::now();
            $model->updated_at = \Carbon\Carbon::now();
            $model->save();
            $before_num = 0;
            $after_num = intval($amount / 100);
        }else{
            $model->increment('value', $amount);
            $before_num = $model->lottery_num;
            $after_num = intval($model->value / 100);
        }
        //计算次数
        $add_num = $after_num - $before_num;
        if ($add_num > 0){
            $model->lottery_num = $after_num;
            $model->save();
            /** @var LotteryUserModel $lottery_user */
            $lottery_user = LotteryUserModel::where('aff', $aff)->first();
            if (empty($lottery_user)){
                $lottery_user = LotteryUserModel::make();
                $lottery_user->aff = $aff;
                $lottery_user->lottery_id = $lottery_id;
                $lottery_user->val = $add_num;
                $lottery_user->total = $add_num;
                $lottery_user->created_at = \Carbon\Carbon::now();
                $lottery_user->updated_at = \Carbon\Carbon::now();
                $lottery_user->save();
            }else{
                $lottery_user->increment('val', $add_num, ['total' => \DB::raw('total + ' . $add_num)]);
            }
        }
    }
}
