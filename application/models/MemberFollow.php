<?php

use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * class MemberFollowModel
 *
 * @property int $aff 用户自己
 * @property string $created_at 关注时间
 * @property int $id
 * @property int $to_aff 关注的人
 *
 * @property MemberModel $member
 * @property MemberModel $follow
 *
 * @mixin \Eloquent
 */
class MemberFollowModel extends BaseModel
{

    protected $table = "member_follow";

    protected $primaryKey = 'id';

    protected $fillable = [
        'aff',
        'created_at',
        'to_aff'
    ];

    protected $guarded = 'id';

    const UPDATED_AT = null;

    public static function generateId($aff): string
    {
        return 'user:follow:' . $aff;
    }

    public function follow(): HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'to_aff');
    }

    public function member(): HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }


}
