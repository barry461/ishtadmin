<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class WithdrawBlackModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property string $reason 拉黑理由
 * @property string $remark 备注
 * @property int $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 *
 * @property ?MemberModel $member
 *
 * @mixin \Eloquent
 */
class WithdrawBlackModel extends BaseModel
{
    const REASON_LIST = [
        1 => '官方账号，不予提现。',
        2 => '多次违规，不予提现。',
        3 => '其他',
    ];

    const STAUS_OK = 1;
    const STAUS_NO = 0;
    const STATUS_LIST = [
        self::STAUS_NO => '未拉黑',
        self::STAUS_OK => '已拉黑'
    ];
    protected $table = 'withdraw_black';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aff',
        'reason',
        'remark',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = false;

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public static function isBlack($aff){
        /* @var WithdrawBlackModel $black **/
        $black = self::query()->where('aff',$aff)->first();
        if ($black && $black->status == self::STAUS_OK){
            return 1;
        }

        return 0;
    }
}