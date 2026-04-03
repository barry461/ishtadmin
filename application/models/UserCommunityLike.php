<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property int $aff
 * @property int $related_id
 * @property int $type
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class UserCommunityLikeModel extends BaseModel
{
    protected $table = 'user_community_like_log';
    protected $fillable = [
        'id',
        'aff',
        'related_id',
        'type',
        'created_at',
        'updated_at'
    ];

    const TYPE_POST = 0;
    const TYPE_COMMENT = 1;
    const TYPE_TIPS = [
        self::TYPE_POST => '帖子',
        self::TYPE_COMMENT => '评论'
    ];

    const USER_POST_LIKE_LIST = 'user:community:post:like:list:%s';
    const USER_COMMENT_LIKE_LIST = 'user:community:comment:list:%s';

    const USER_POST_LIKE_PREG_LIST = 'user:community:post:like:list:*';
    const USER_COMMENT_LIKE_PREG_LIST = 'user:community:comment:list:*';

    public static function listLikePostIds($aff)
    {
        $cacheKey = sprintf(self::USER_POST_LIKE_LIST, $aff);
        return cached($cacheKey)
            ->fetchJson(function () use ($aff) {
                $data = self::where('type', self::TYPE_POST)
                    ->where('aff', $aff)
                    ->get()
                    ->toArray();
                return array_column($data, 'related_id');
            });
    }

    public static function listLikeCommentIds($aff)
    {
        $cacheKey = sprintf(self::USER_COMMENT_LIKE_LIST, $aff);
        return cached($cacheKey)
            ->fetchPhp(function () use ($aff) {
                $data = self::where('type', self::TYPE_COMMENT)
                    ->where('aff', $aff)
                    ->get()
                    ->toArray();
                return array_column($data, 'related_id');
            });
    }

    public static function getIdsById($type, $aff, $id)
    {
        return self::where('type', $type)
            ->where('aff', $aff)
            ->where('related_id', $id)
            ->first();
    }

    public static function clearCacheByAff($type, $aff)
    {
        $rule = $type == self::TYPE_POST ? self::USER_POST_LIKE_LIST : self::USER_COMMENT_LIKE_LIST;
        $cacheKey = sprintf($rule, $aff);
        cached($cacheKey)->clearCached();
    }

    public static function clearCache()
    {
        //redis()->bulkDel(self::USER_COMMENT_LIKE_PREG_LIST);
        //redis()->bulkDel(self::USER_POST_LIKE_PREG_LIST);
    }
}
