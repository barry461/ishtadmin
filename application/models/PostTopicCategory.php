<?php

/**
 * class PostTopicCategoryModel
 *
 *
 * @property string $created_at 创建时间
 * @property int $id
 * @property string $name 类型名称
 * @property int $sort 排序 越大越前
 * @property int $status 是否显示 0不显示 1显示
 * @property string $updated_at 更新时间
 *
 *
 *
 * @mixin \Eloquent
 */
class PostTopicCategoryModel extends BaseModel
{
    protected $table = 'post_topic_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'created_at',
        'name',
        'sort',
        'status',
        'updated_at'
    ];
    const POST_TOPIC_CATEGORY_KEY = 'post:topic:category:list';
    const STATUS_HIDE = 0;
    const STATUS_NORMAL = 1;
    const STATUS_TIPS = [
        self::STATUS_HIDE => '屏蔽',
        self::STATUS_NORMAL => '正常'
    ];

    // 话题类型
    public static function listCates()
    {
        return cached(self::POST_TOPIC_CATEGORY_KEY)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_NORMAL)
                    ->orderByDesc('sort')
                    ->get();
        });
    }

    public static function clearCache()
    {
        cached(self::POST_TOPIC_CATEGORY_KEY)->clearCached();
    }
}