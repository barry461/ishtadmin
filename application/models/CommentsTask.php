<?php

/**
 * class CommentsTaskModel
 *
 *
 * @property int $id
 * @property int $cid 帖子的ID
 * @property string $content 评论内容
 * @property int $begin 开始时间
 * @property int $end 结束时间
 * @property int $is_run 是否执行
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class CommentsTaskModel extends BaseModel
{
    protected $table = 'comments_task';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cid',
        'content',
        'begin',
        'end',
        'is_run',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const RUN_WAIT = 0;
    const RUN_SUCCESS = 1;
    const RUN_GIVE_UP = 2;
}