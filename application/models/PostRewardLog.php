<?php

/**
 * @property int $id
 * @property string $aff 用户AFF
 * @property int $post_id 帖子ID
 * @property int $amount 打赏金额
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @mixin \Eloquent
 */
class PostRewardLogModel extends BaseModel
{
    protected $table = 'post_reward_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'post_id',
        'post_aff',
        'amount',
        'created_at',
        'updated_at',
    ];

    public static function clearCache()
    {
    }
}
