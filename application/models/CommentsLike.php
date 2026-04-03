<?php

/**
 * class CommentsLikeModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property int $coid 评论ID
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class CommentsLikeModel extends BaseModel
{
    protected $table = 'comments_like';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'coid',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const CONTENTS_COMMENTS_LIKE = 'contents:comments:like:%s';
}