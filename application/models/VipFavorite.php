<?php


class VipFavoriteModel extends BaseModel
{

    protected $table = 'vip_favorite';

    protected $fillable = [
        'id', 'aff', 'created_at', 'info_id'
    ];
    const UPDATED_AT = NULL;
    const REDIS_KEY_FAVORITE_LIST = 'favorite:vip:list:';

    public function user()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }
}