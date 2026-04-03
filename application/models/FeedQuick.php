<?php

/**
 * class UserModelModel
 *
 * @property int $id
 * @property string $title
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @author xiongba
 * @date 2020-06-05 07:56:43
 *
 * @mixin \Eloquent
 */
class FeedQuickModel extends BaseModel
{
    const REDIS_FEED_ITEMS = 'feed:items';

    protected $table = 'user_model';

    protected $fillable = [
        'title',
        'updated_at',
        'created_at',
    ];

    public static function getHuifuSelectOptions()
    {
        $all = self::get();
        $results = [];
        foreach ($all as $item) {
            $results[$item->title] = replace_share($item->title);
        }
        return $results;
    }
}