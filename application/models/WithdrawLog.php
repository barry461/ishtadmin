<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class WithdrawLogModel
 *
 *
 * @property int $id
 * @property string $uuid
 * @property int $aff
 * @property string $cash_id 提现订单号
 * @property int $type 提现方式 0：支付宝 1：银行卡 2：USDT
 * @property string $account 提现账号
 * @property string $name 提现姓名
 * @property int $amount 提现金额单位元
 * @property int $charge 申请的扣款的手续费汇率，避免退款的时候。用户申请的和退款的汇率不一致
 * @property int $withdraw_from 提现的涞源
 * @property int $status 提现状态 0:审核中;1:已完成;2:未通过
 * @property string $descp 状态说明
 * @property string $third_id
 * @property string $channel
 * @property string $ip
 * @property string $local
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property int $coins 提现时候扣除的相应的币
 *
 * @property string $status_str
 * @property string $type_str
 * @property string $withdraw_from_str
 *
 * @property ?MemberModel $member
 *
 * @mixin \Eloquent
 */
class WithdrawLogModel extends BaseModel
{
    protected $table = 'withdraw_log';
    protected $fillable
        = [
            'id',
            'uuid',
            'aff',
            'cash_id',
            'type',
            'account',
            'name',
            'amount',
            'charge',
            'withdraw_from',
            'status',
            'descp',
            'third_id',
            'channel',
            'ip',
            'local',
            'created_at',
            'updated_at',
            'coins',
        ];
    protected $primaryKey = 'id';
    protected $hidden = ['third_id', 'charge', 'uuid', 'aff', 'cash_id'];
    protected $appends = ['status_str', 'type_str', 'withdraw_from_str'];
    const REDIS_KEY_WITHDRAW_LIST = 'withdraw:list:';
    const DRAW_WAY = [1 => 'bankcard', 2 => 'alipay', 5 => 'game'];
    const DRAW_STATUS_WAIT = 1;
    const TYPE
        = [
            0 => '支付宝',
            1 => '银行卡',
            2 => 'USDT',
//            2 => '转会员',
//            3 => '买浪币',
//            5 => '游戏提现',
        ];
    const WITHDRAW_FROM_PROXY = 1;
    const WITHDRAW_FROM_INCOME = 2;
    const WITHDRAW_FROM_GAOFEI = 3;
    const WITHDRAW_FROM
        = [
            self::WITHDRAW_FROM_PROXY  => '全民代理',
            self::WITHDRAW_FROM_INCOME => '收益提现',
            self::WITHDRAW_FROM_GAOFEI => '投稿提现',
        ];
    const STATUS_INIT = 0;
    const STATUS_PASS = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_UNFREEZE = 3;
    const STATUS_FAILURE = 4;
    const STATUS_FREEZE = 5;
    const STATUS_PERSON_FAILURE = 6;
    const STATUS
        = [
            self::STATUS_INIT           => '未审核',
            self::STATUS_PASS           => '审核通过',
            self::STATUS_SUCCESS        => '提现成功',
            self::STATUS_UNFREEZE       => '解冻',
            self::STATUS_FAILURE        => '提现拒绝',
            self::STATUS_FREEZE         => '冻结',
            self::STATUS_PERSON_FAILURE => '手动拒绝',
        ];
    const CHARGE = 0.1;
    const MONEY_CHARGE = 0.33333333;
    // 1 人名币换多少金币
    const COINS_RATIO = 10;
    const DRAW_STATUS
        = [
            self::STATUS_INIT           => "待审核",
            self::STATUS_PASS           => "审核通过",
            self::STATUS_SUCCESS        => "已完成",
            self::STATUS_UNFREEZE       => "已解冻",
            self::STATUS_FAILURE        => "提现失败",
            self::STATUS_FREEZE         => "冻结中",
            self::STATUS_PERSON_FAILURE => "提现失败",
        ];

    public function getStatusStrAttribute(): string
    {
        return self::DRAW_STATUS[$this->attributes['status'] ?? -1] ?? '未知';
    }

    public function getTypeStrAttribute(): string
    {
        return self::TYPE[$this->attributes['type'] ?? -1] ?? '未知';
    }

    public function getWithdrawFromStrAttribute(): string
    {
        return self::WITHDRAW_FROM[$this->attributes['withdraw_from'] ?? -1] ??
            '未知';
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public static function calc($amount): int
    {
        $formula = setting('withdraw:calc','');
        if (empty($formula)) {
            $formula = "0";
        }

        $formula = str_replace('$amount' , $amount , $formula);
        $ret = (float)calc_formula($formula);
        return ceil($ret);
    }

}