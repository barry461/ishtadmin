<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvStyleModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name_zh
 * @property string $name_jp
 * @property string $name_en
 * @property string $desc_zh
 * @property string $desc_jp
 * @property string $desc_en
 * @property string $iconv
 * @property int $count
 * @property int $sort
 * @property int $check_num
 * @property string $created_at
 * @property string $update_at
 * @property int $top_show
 * @property int $top_sort
 */
class MvStyleModel extends BaseModel
{
    protected $table = 'sq_mv_style';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'name_zh',
        'name_jp',
        'name_en',
        'desc_zh',
        'desc_jp',
        'desc_en',
        'iconv',
        'count',
        'sort',
        'check_num',
        'created_at',
        'update_at',
        'top_show',
        'top_sort',
    ];

    // 关联视频
    public function mvs()
    {
        return $this->hasMany(MvStyleConnModel::class, 'style_id', 'id');
    }

    // 主题列表
    public static function getThemeList($limit = 10, $offset = 0)
    {
        return self::where('top_show', 1)
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
