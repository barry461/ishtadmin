<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvTagConnModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $tag_id
 * @property int $mv_id
 * @property string $created_at
 * @property string $updated_at
 */
class MvTagConnModel extends BaseModel
{
    protected $table = 'sq_mv_tag_conn';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'mv_id',
        'created_at',
        'updated_at',
    ];

    // 关联视频
    public function mv()
    {
        return $this->belongsTo(MvModel::class, 'mv_id', 'id');
    }

    // 关联标签
    public function tag()
    {
        return $this->belongsTo(MvTagModel::class, 'tag_id', 'id');
    }
}
