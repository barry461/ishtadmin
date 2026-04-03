<?php

use Illuminate\Support\Collection;

/**
 * class PcAppModel
 *
 * @property int $id
 * @property string $name 名称
 * @property string $intro 描述
 * @property string $thumb 图片地址
 * @property int $category_id 类型ID
 * @property int $type 产品类型 0:内部产品 1:外部产品
 * @property int $click_num 点击量
 * @property int $status 0-禁用，1-启用
 * @property int $sort 排序
 * @property int $created_at 创建时间
 * @property int $updated_at 创建时间
 * @mixin \Eloquent
 */
class PcAppModel extends BaseModel
{
    protected $table = "pc_app";

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'intro', 'type', 'click_num', 'category_id', 'thumb', 'url', 'status', 'sort', 'created_at', 'updated_at'];

    const STATUS_OK = 1;
    const STATUS_NO = 0;
    const STATUS_TIPS = [
        self::STATUS_OK => '启用',
        self::STATUS_NO => '禁用'
    ];

    const TYPE_1 = 0;
    const TYPE_2 = 1;
    const TYPE_TIPS = [
        self::TYPE_1 => '内部产品',
        self::TYPE_2 => '外部产品'
    ];


    protected $appends = ['clicked','report_id','report_type'];

    const REDIS_KEY = 'pc:category:app:%s:%s:%s';
    const REDIS_KEY_GROUP = 'pc:gp:category:app';

    public function setThumbAttribute($value)
    {
        $this->resetSetPathAttribute('thumb', $value);
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function getClickedAttribute(): int
    {
        return rand(100000, 999999);
    }

    public static function listApps($id, $page, $ix, $limit)
    {
        $cacheKey = sprintf(self::REDIS_KEY, $id, $page, $limit);
        return cached($cacheKey)
            ->group(self::REDIS_KEY_GROUP)
            ->chinese("PC福利APP列表")
            ->fetchPhp(function () use ($id, $page, $ix, $limit) {
                return self::where('status', self::STATUS_OK)
                    ->where('category_id', $id)
                    ->orderByDesc('sort')
                    ->forpage($page, $limit)
                    ->get();
            });
    }

    public static function clearCache()
    {
        cached('')->clearGroup(self::REDIS_KEY_GROUP);
    }

    public function getReportIdAttribute(): int
    {
        return (int)($this->attributes['id'] ?? 0);
    }

    public function getReportTypeAttribute(): int
    {
        return DayClickModel::TYPE_APP;
    }

    public static function incrNum($id, $num = 1)
    {
        return self::where('id', $id)->increment('click_num', $num);
    }
}