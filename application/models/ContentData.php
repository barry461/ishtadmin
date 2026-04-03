<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class ContentsModel
 *
 *
 * @property int $cid
 * @property string $title
 * @property string $slug
 * @property \Carbon\Carbon $created
 * @property int $modified
 * @property string $text
 * @property int $order
 * @property int $authorId
 * @property string $template
 * @property string $type
 * @property string $status
 * @property string $password
 * @property int $commentsNum
 * @property string $allowComment 允许评论
 * @property string $allowPing 允许被引用
 * @property string $allowFeed 允许在聚合中出现
 * @property int $parent
 * @property int $is_home 是否在首页展示
 * @property int $home_top
 * @property int $is_slice 默认处于切片状态
 * @property int $app_hide app端隐藏
 * @property int $favorite_num 搜索数据
 * @property int $web_show
 * @property int $view 浏览量
 *
 *
 * @mixin \Eloquent
 */
class ContentDataModel extends BaseModel
{
    protected $table = 'content_data';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'id',
            'title',
            'text',
            'created',
            'order',
            'type',
            'status',
            'commentsNum',
            'is_home',
            'home_top',
            'is_slice',
            'authorId',
            'authorName',
            'category',
            'tags',
            'app_hide',
            'view'
        ];
    protected $guarded = 'id';
    public $timestamps = true;
    protected $dateFormat = 'U';
    const CREATED_AT = 'created';
    protected $casts
        = [
            'created'  => 'datetime:Y-m-d H:i:s'
        ];
}