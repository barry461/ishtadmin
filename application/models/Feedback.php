<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property int $type 反馈分类
 * @property string $description 反馈详情
 * @property string $img_url1 反馈图片路径
 * @property string $img_url2 反馈图片路径
 * @property string $img_url3 反馈图片路径
 * @property int $status 0-禁用，1-启用
 * @property string $created_at 创建时间
 * @property string $user_agent 用户ua
 * @mixin \Eloquent
 */
class FeedbackModel extends BaseModel
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
    const STATUS = [
        self::STATUS_SUCCESS => '启用',
        self::STATUS_FAIL => '禁用',
    ];

    const TYPE_OTHERS = 0;
    const TYPE_NO_DOWNLOAD = 1;
    const TYPE_NO_OPEN = 2;
    const TYPE = [
        self::TYPE_OTHERS => '其他',
        self::TYPE_NO_DOWNLOAD => '无法下载',
        self::TYPE_NO_OPEN => '无法进入',
    ];

    protected $table = 'feedback';

    protected $fillable = [
        'id',
        'type',
        'description',
        'img_url1',
        'img_url2',
        'img_url3',
        'status',
        'created_at',
        'user_agent',
    ];

    public $timestamps = false;

//    protected $appends = [
//        'img_url1',
//        'img_url2',
//        'img_url3',
//    ];

    public static function queryBase(...$args)
    {
        return parent::queryBase(...$args)->where('status', self::STATUS_SUCCESS);
    }

//    public function getImgUrl1Attribute(): string
//    {
//        return url_image($this->attributes['img_url1'] ?? '');
//    }
//
//    public function getImgUrl2Attribute(): string
//    {
//        return url_image($this->attributes['img_url2'] ?? '');
//    }
//
//    public function getImgUrl3Attribute(): string
//    {
//        return url_image($this->attributes['img_url3'] ?? '');
//    }
//


}