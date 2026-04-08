<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActorModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $hash_id
 * @property string $name
 * @property string $name_cn
 * @property string $name_ja
 * @property string $name_en
 * @property string $avatar
 * @property int $mv_count
 * @property int $hot_value
 * @property string $desc_zh
 * @property string $desc_en
 * @property string $desc_jp
 * @property int $top_show
 * @property int $top_sort
 * @property string $created_at
 * @property string $updated_at
 * @property string $name_initials
 * @property string $actor_tag
 */
class ActorModel extends BaseModel
{
    protected $table = 'sq_actor';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'hash_id',
        'name',
        'name_cn',
        'name_ja',
        'name_en',
        'avatar',
        'mv_count',
        'hot_value',
        'desc_zh',
        'desc_en',
        'desc_jp',
        'top_show',
        'top_sort',
        'created_at',
        'updated_at',
        'name_initials',
        'actor_tag',
    ];

    // 关联视频
    public function mvs()
    {
        return $this->hasMany(MvActorConnModel::class, 'actor_id', 'id');
    }

    // 热门女优
    public static function getHotActors($limit = 10, $offset = 0)
    {
        return self::where('top_show', 1)
            ->orderBy('top_sort', 'desc')
            ->orderBy('hot_value', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
