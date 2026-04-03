<?php
use Carbon\Carbon;

/**
 * class ChargeAdminModel
 *
 * @property int $addtime 添加时间
 * @property string $admin 管理员
 * @property string $des
 * @property int $id
 * @property string $ip IP
 * @property int $score 分数
 * @property int $score_type 分数类型
 * @property int $to_aff 用户aff
 * @property int $type
 *
 * @author xiongba
 * @date 2020-08-14 09:45:57
 *
 * @mixin \Eloquent
 */
class ChargeAdminModel extends BaseModel
{
    protected $table = "charge_admin";

    protected $primaryKey = 'id';

    protected $fillable = ['addtime', 'admin', 'des', 'ip', 'score', 'score_type', 'to_aff', 'type'];

    protected $guarded = 'id';

    const CREATED_AT = 'addtime';
    const UPDATED_AT = null;

    const TYPE_SUB = 1;
    const TYPE_ADD = 2;
    const TYPE = [
        self::TYPE_ADD => '上分',
        self::TYPE_SUB => '下分',
    ];

    const SCORE_TYPE_MONEY = 1;
    const SCORE_TYPE_INCOME = 2;
    const SCORE_TYPE_ROYALTIES = 3;

    const SCORE_TYPE = [
        self::SCORE_TYPE_MONEY => '金币',
        self::SCORE_TYPE_INCOME => '收益(可提现-非代理)',
        self::SCORE_TYPE_ROYALTIES => '稿费',
    ];

    protected $appends = ['type_str', 'score_type_str'];


    public function getTypeStrAttribute()
    {
        return self::TYPE[$this->attributes['type']] ?? '未知';
    }

    public function getScoreTypeStrAttribute()
    {
        return self::SCORE_TYPE[$this->attributes['score_type']] ?? '未知';
    }

    public function getAddtimeAttribute($value)
    {
        $date = Carbon::parse($value);
        $date->timezone = 'Asia/Shanghai';
        return $date->format('Y-m-d H:i:s');
    }

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'to_aff');
    }
}
