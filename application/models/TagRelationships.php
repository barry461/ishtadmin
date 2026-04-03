<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class TagRelationshipsModel
 *
 * @property int $cid
 * @property int $tag_id
 *
 * @mixin \Eloquent
 */
class TagRelationshipsModel extends Model
{
    protected $table = 'tag_relationships';

    protected $primaryKey = null; // 因为是联合主键
    public $incrementing = false;

    protected $fillable = ['cid', 'tag_id'];

    public $timestamps = false;

    // 定义关联内容
    public function content()
    {
        return $this->belongsTo(ContentsModel::class, 'cid', 'cid');
    }

    // 定义关联标签
    public function tag()
    {
        return $this->belongsTo(TagsModel::class, 'tag_id', 'id');
    }
}
