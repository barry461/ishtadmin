<?php

/**
 * @property int $id
 * @property string $url 跳转链接
 *
 * @property string $url_str
 *
 * @property string $img_url 图片url
 * @property int $type 类型 1内部  2外部
 *
 * @property int $router
 *
 * @property int $status 状态 0下架 1上架
 * @property string $created_at
 * @property string $updated_at
 * @property string $start_at
 * @property string $end_at
 * @mixin \Eloquent
 */
class BannerModel extends BaseModel
{
    protected $table = 'banner';

    protected $fillable
        = [
            'id',
            'url',
            'img_url',
            'name',
            'img_width',
            'img_height',
            'type',
            'router',
            'status',
            'created_at',
            'updated_at',
            'start_at',
            'end_at'
        ];
    protected $primaryKey = 'id';

    protected $hidden = ['router'];

    protected $appends = ['url_str'];

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    const TYPE = [
        self::TYPE_IN => '内部跳转',
        self::TYPE_OUT => '外部跳转'
    ];

    const STATUS_DOWN = 0;
    const STATUS_UP = 1;
    const STATUS = [
        self::STATUS_DOWN => '下架',
        self::STATUS_UP => '上架',
    ];

    public function setImgUrlAttribute($value)
    {
        $this->resetSetPathAttribute('img_url', $value);
    }

    public function getImgUrlAttribute(): string
    {
        return url_image($this->attributes['img_url'] ?? null);
    }

    public function getUrlStrAttribute(): string
    {
        if ($this->attributes['type'] == self::TYPE_OUT) {
            return $this->attributes['url'] ?? '';
        }
        $value = $this->attributes['url'] ?? '';
        $router = $this->attributes['router'] ?? '';
        return FlutterRouterModel::parseRouterUri($value , $router);
    }
}
