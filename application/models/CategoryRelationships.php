<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class CategoryRelationshipsModel
 *
 * @property int $cid
 * @property int $category_id
 *
 * @mixin \Eloquent
 */
class CategoryRelationshipsModel extends Model
{
    protected $table = 'category_relationships';

    protected $primaryKey = null; 
    public $incrementing = false;

    protected $fillable = ['cid', 'category_id'];

    public $timestamps = false;

    // 与内容表的关联
    public function content()
    {
        return $this->belongsTo(ContentsModel::class, 'cid', 'cid');
    }

    // 与分类表的关联
    public function category()
    {
        return $this->belongsTo(CategoriesModel::class, 'category_id', 'id');
    }

     /**
     * 与 TagRelationshipsModel 建立一对多关系：
     * 一个标签可以关联多条 content（通过中间表 typecho_tag_relationships）
     */
    public function relationships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            TagRelationshipsModel::class,
            'tag_id',
            'id'
        );
    }
}
