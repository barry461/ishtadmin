<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property string $title 轮播图标题
 * @property string $description 图片描述
 * @property string $img_url 轮播图片路径
 * @property int $status 0-禁用，1-启用
 * @property string $created_at 创建时间
 * @property string $updated_at 创建时间
 * @property string $sort 越大越前
 * @property string $end_at
 * @property string $start_at
 * @mixin \Eloquent
 */
class RotateImagesModel extends BaseModel
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
    const STATUS = [
        self::STATUS_SUCCESS => '启用',
        self::STATUS_FAIL => '禁用',
    ];

    protected $table = 'rotate_images';

    protected $fillable = [
        'id',
        'title',
        'description',
        'img_url',
        'status',
        'created_at',
        'updated_at',
        'start_at',
        'end_at',
        'sort'
    ];

    protected $appends = [
        'img_url',
    ];

    const REDIS_FEEDBACK_KEY = 'rotate:list:';

    public static function listRotate($limit = 3)
    {
        $key = self::REDIS_FEEDBACK_KEY;
        return cached($key)
            ->group('lunbotu')
            ->chinese('落地页轮播列表')
            ->fetchPhp(function () use ($limit) {
                return self::where('status', self::STATUS_SUCCESS)
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->limit($limit)
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            });
    }


    public static function queryBase(...$args)
    {
        return parent::queryBase(...$args)->where('status', self::STATUS_SUCCESS);
    }

    public function getImgUrlAttribute(): string
    {
        $url = $this->attributes['img_url'];
        return url_image($url);
    }

}