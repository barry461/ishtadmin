<?php

/**
 * class TaskEmailModel
 *
 *
 * @property int $id
 * @property string $title 任务名称
 * @property int $user_type 用户类型
 * @property string $send_time 推送开始时间
 * @property int $cid 文章ID
 * @property string $send_title 自定义标题
 * @property string $send_content 自定义内容
 * @property string $img_url 图片
 * @property int $status 状态
 * @property int $suc_ct 成功条数
 * @property int $fail_ct 失败数
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class TaskEmailModel extends BaseModel
{
    protected $table = 'task_email';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'title',
        'user_type',
        'send_time',
        'cid',
        'send_title',
        'send_content',
        'img_url',
        'status',
        'suc_ct',
        'fail_ct',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const STATUS_INIT = 0;
    const STATUS_WAIT = 1;
    const STATUS_PROGRESS = 2;
    const STATUS_FINISH = 3;
    const STATUS_TIPS = [
        self::STATUS_INIT => '未开启',
        self::STATUS_WAIT => '等待执行',
        self::STATUS_PROGRESS => '进行中',
        self::STATUS_FINISH => '完成'
    ];

    const USER_TYPE_ALL = 0;
    const USER_TYPE_SUBSCRIBE = 1;
    const USER_TYPE_TIPS = [
        self::USER_TYPE_ALL       => "全部用户(未开发)",
        self::USER_TYPE_SUBSCRIBE => "订阅用户",

    ];

    public function getImgUrlAttribute(): string
    {
        return url_image($this->attributes['img_url'] ?? '');
    }
}