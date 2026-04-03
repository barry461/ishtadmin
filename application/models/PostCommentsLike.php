<?php

/**
 * class PostCommentsLikeModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property int $cid 评论ID
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class PostCommentsLikeModel extends BaseModel
{
    protected $table = 'post_comments_like';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'cid',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const POST_COMMENTS_LIKE = 'post:comments:like:%s';
}