<?php

/**
 * @property int $id
 * @property int $product_id
 * @property int $privilege_id
 * @property int $resource_type
 * @property int $privilege_type
 * @property int $value
 * @property string $created_at
 *
 * @property ProductModel $product
 *
 * @mixin \Eloquent
 */
class ProductPrivilegeModel extends BaseModel
{
    protected $table = 'product_privilege';

    protected $fillable = [
        'id',
        'product_id',
        'privilege_id',
        'resource_type',
        'privilege_type',
        'value',
        'created_at'
    ];

    protected $primaryKey = 'id';
    const UPDATED_AT = null;

    const RESOURCE_TYPE_SYSTEM = 1;
    const RESOURCE_TYPE_SECRET = 2;
    const RESOURCE_TYPE_SKITS = 3;
    const RESOURCE_TYPE_POST = 4;
    const RESOURCE_TYPE = [
        self::RESOURCE_TYPE_SYSTEM => '系统',
        self::RESOURCE_TYPE_SECRET => '封禁秘闻',
        self::RESOURCE_TYPE_SKITS => '短剧',
        self::RESOURCE_TYPE_POST => '社区订阅',
    ];
    const RESOURCE_TYPE_NUM = [
        self::RESOURCE_TYPE_SYSTEM,
        self::RESOURCE_TYPE_SECRET,
        self::RESOURCE_TYPE_SKITS,
        self::RESOURCE_TYPE_POST,
    ];

    const PRIVILEGE_TYPE_VIEW = 1;
    const PRIVILEGE_TYPE_DOWNLOAD = 2;
    const PRIVILEGE_TYPE_COMMENT = 3;
    const PRIVILEGE_TYPE_DISCOUNT = 4;
    const PRIVILEGE_TYPE_UNLOCK = 5;
    const PRIVILEGE_TYPE_SETTING = 6;
    const PRIVILEGE_TYPE_FEED = 7;
    const PRIVILEGE_TYPE = [
        self::PRIVILEGE_TYPE_VIEW => '观看',
        self::PRIVILEGE_TYPE_DOWNLOAD => '下载',
        self::PRIVILEGE_TYPE_COMMENT => '评论',
        self::PRIVILEGE_TYPE_DISCOUNT => '折扣',
        self::PRIVILEGE_TYPE_UNLOCK => '免费解锁',
        self::PRIVILEGE_TYPE_SETTING => '设置个人信息',
        self::PRIVILEGE_TYPE_FEED => '使用工单',
    ];
    const PRIVILEGE_TYPE_NUM = [
        self::PRIVILEGE_TYPE_VIEW,
        self::PRIVILEGE_TYPE_DOWNLOAD,
        self::PRIVILEGE_TYPE_COMMENT,
        self::PRIVILEGE_TYPE_DISCOUNT,
        self::PRIVILEGE_TYPE_UNLOCK,
        self::PRIVILEGE_TYPE_SETTING,
        self::PRIVILEGE_TYPE_FEED,
    ];

    protected $appends = ['privilege_type_str', 'resource_type_str'];
    
    public function getPrivilegeTypeStrAttribute(): string
    {
        return self::PRIVILEGE_TYPE[$this->attributes['privilege_type'] ?? -1] ?? '未知';
    }

    public function getResourceTypeStrAttribute(): string
    {
        return self::RESOURCE_TYPE[$this->attributes['resource_type'] ?? -1] ?? '未知';
    }


    public function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

}
