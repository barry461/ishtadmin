<?php

/**
 * class OfficialAccountModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property string $created_at
 * @property string $updated_at
 *
 * @property MemberModel $member
 *
 * @mixin \Eloquent
 */
class OfficialAccountModel extends BaseModel
{
    protected $table = 'official_account';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const OFFICIAL_ACCOUNT_SET = 'official:account:set';

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class , 'aff' , 'aff');
    }
}