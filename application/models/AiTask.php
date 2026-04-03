<?php

/**
 * @property int $id
 * @property int $media_width 媒体宽度
 * @property int $media_height 媒体高度
 * @property string $media_url 媒体图片
 * @property string $media_1 脱衣1
 * @property string $media_2 脱衣2
 * @property int $status 状态 0-待处理 1-处理中 2-处理完成 3失败
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $pay_type 付费方式
 * @property int $aff 用户
 * @property string $times 脱衣重试次数
 * @property int $refunded 退款标记
 * @property int $is_delete 是否删除
 * @mixin \Eloquent
 */
class AiTaskModel extends BaseModel
{
    protected $table = 'ai_task';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'media_url',
        'media_width',
        'media_height',
        'media_1',
        'media_2',
        'status',
        'created_at',
        'updated_at',
        'pay_type',
        'aff',
        'times',
        'refunded',
        'is_delete',
    ];

    const STATUS_WAIT = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_FINISHED = 2;
    const STATUS_FAILD = 3;
    const STATUS_TIPS = [
        self::STATUS_WAIT       => '待处理',
        self::STATUS_PROCESSING => '处理中',
        self::STATUS_FINISHED   => '已完成',
        self::STATUS_FAILD      => '失败',
    ];
    const PAY_FREE = 0;
    const PAY_TIMES = 1;
    const PAY_COINS = 2;
    const PAY_TYPE =[
        self::PAY_FREE => '免费',
        self::PAY_TIMES => '次数',
        self::PAY_COINS => '金币',
    ];

    const DELETE_NO = 0;
    const DELETE_OK = 1;
    const DELETE_TIPS = [
        self::DELETE_NO => '未删除',
        self::DELETE_OK => '已删除',
    ];

    public function getMedia2Attribute()
    {
        $uri = $this->attributes['media_2'] ?? '';
        return $uri ? url_image($uri) : '';
    }
    public function getMediaUrlAttribute()
    {
        $uri = $this->attributes['media_url'] ?? '';
        return $uri ? url_image($uri) : '';
    }




}