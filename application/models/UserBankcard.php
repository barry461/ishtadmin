<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class UserBankcardModel
 *
 * @property int $aff 用户aff
 * @property string $bank 银行
 * @property string $card 卡号
 * @property string $card_type 卡类型
 * @property string $created_at 创建时间
 * @property int $id
 * @property string $ip 最后操作ip
 * @property int $is_default 是否默认
 * @property string $name 首款人名称
 * @property string $updated_at 修改时间
 * @property int $type 类型 0银行卡 1usdt
 *
 * @author xiongba
 * @date 2022-03-08 02:31:47
 *
 * @mixin \Eloquent
 */
class UserBankcardModel extends BaseModel
{

    protected $table = "user_bankcard";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'aff',
            'bank',
            'card',
            'card_type',
            'created_at',
            'ip',
            'is_default',
            'name',
            'updated_at',
            'type',
        ];

    const TYPE_BANK = 0;
    const TYPE_USDT = 1;
    const type_tips = [
        self::TYPE_BANK => '银行卡',
        self::TYPE_USDT => 'USDT'
    ];

    protected $guarded = 'id';


    public static function findByAff($aff , $useWritePdo = false): ?self
    {
        $query = self::query();
        if ($useWritePdo){
            $query->useWritePdo();
        }
        /** @var ?self $member */
        $member = $query->where('aff', '=', $aff)->first();
        return $member;
    }
}
