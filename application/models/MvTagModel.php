<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvTagModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $p_id
 * @property string $name_zh
 * @property string $name_jp
 * @property string $name_en
 * @property string $another_name
 * @property string $desc_zh
 * @property string $desc_jp
 * @property string $desc_en
 * @property int $is_show
 * @property int $is_hot
 * @property int $count
 * @property int $top_show
 * @property int $top_sort
 */
class MvTagModel extends BaseModel
{
    protected $table = 'sq_mv_tag';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'p_id',
        'name_zh',
        'name_jp',
        'name_en',
        'another_name',
        'desc_zh',
        'desc_jp',
        'desc_en',
        'is_show',
        'is_hot',
        'count',
        'top_show',
        'top_sort',
    ];

    // 关联视频
    public function mvs()
    {
        return $this->hasMany(MvTagConnModel::class, 'tag_id', 'id');
    }

    // 热门标签
    public static function getHotTags($limit = 10, $offset = 0)
    {
        return self::where('is_show', 1)
            ->where('is_hot', 1)
            ->orderBy('top_sort', 'desc')
            ->orderBy('count', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
