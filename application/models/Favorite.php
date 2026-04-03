<?php


class FavoriteModel extends BaseModel
{

    protected $table = 'favorite';

    protected $fillable = [
        'id', 'uid', 'created_at', 'info_id'
    ];
    const UPDATED_AT = NULL;
    const REDIS_KEY_FAVORITE_LIST = 'favorite:list:';

    public function user()
    {
        return $this->hasOne(MemberModel::class, 'uid', 'uid');
    }
}