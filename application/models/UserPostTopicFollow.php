<?php

/**
 * @property int $id
 * @property string $aff 用户AFF
 * @property string $related_id 被关注对象 0用户的AFF 1帖子的话题ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @mixin \Eloquent
 */
class UserPostTopicFollowModel extends BaseModel
{
    protected $table = 'user_post_topic_follow_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'related_id',
        'created_at',
        'updated_at',
    ];

    const FOLLOW_TOPICS_LIST_KEY = 'user:follow:topics:list:%s';
    const FOLLOW_TOPICS_LIST_PREG_KEY = 'user:follow:topics:list:*';

    // 获取用户关注的话题
    public static function listFollowTopicIds($aff)
    {
        $cacheKey = sprintf(self::FOLLOW_TOPICS_LIST_KEY, $aff);
        return cached($cacheKey)
            ->expired(3600)
            ->serializerPHP()
            ->fetch(function () use ($aff) {
                $data = self::where('aff', $aff)
                    ->get()
                    ->toArray();
                return array_column($data, 'related_id');
            });
    }

    public static function getRecordByParam($aff, $relateId)
    {
        return self::where('aff', $aff)
            ->where('related_id', $relateId)
            ->first();
    }

    public static function clearFollowCache($aff)
    {
        $cacheKey = sprintf(self::FOLLOW_TOPICS_LIST_KEY, $aff);
        redis()->del($cacheKey);
    }

    public static function clearCache()
    {
        redis()->bulkDel(self::FOLLOW_TOPICS_LIST_PREG_KEY);
    }
}
