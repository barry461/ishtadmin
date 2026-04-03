<?php

/**
 * class AreaModel
 *
 * @property int $id
 * @property string $name 类型名称
 * @property int $status 状态  1启用 2未启用
 * @property int $sort 排序 越大越前
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 *
 * @date 2020-01-08 17:09:02
 *
 * @mixin \Eloquent
 */
class AppCategoryModel extends BaseModel
{
    protected $table = "app_category";

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'status', 'sort', 'created_at', 'updated_at'];

    protected $guarded = 'id';

    const STATUS_OK = 1;
    const STATUS_NO = 2;
    const STATUS_TIPS = [
        self::STATUS_OK => '启用',
        self::STATUS_NO => '禁用',
    ];
    const REDIS_KEY = 'app:categories';
    const REDIS_KEY_GROUP = 'gp:app:categories';

    public static function listCategories()
    {
        return cached(self::REDIS_KEY)
            ->group(self::REDIS_KEY_GROUP)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_OK)
                    ->orderBy('sort', 'desc')
                    ->get();
            });
    }

    public static function clearCache()
    {
        cached('')->clearGroup(self::REDIS_KEY_GROUP);
    }
}
