<?php

/**
 * class ProductRightMapModel
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_right_id
 * @property int $status
 * @property string $created_at
 *
 * @property ProductRightModel $right
 * @property ProductModel $product
 *
 * @mixin \Eloquent
 */
class ProductRightMapModel extends BaseModel
{

    protected $table = "product_right_map";

    protected $primaryKey = 'id';

    protected $fillable = ['product_id', 'product_right_id', 'status', 'created_at'];

    protected $guarded = 'id';

    const UPDATED_AT = null;

    //产品状态 0:未上架 1:上架 2:下架
    const STATUS_NOT_LISTED = 0;
    const STATUS_LISTED = 1;
    const STATUS_DOWN_LISTED = 2;
    const STATUS = [
        self::STATUS_NOT_LISTED => '未上架',
        self::STATUS_LISTED => '上架',
        self::STATUS_DOWN_LISTED => '下架'
    ];

    public function right(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductRightModel::class, 'id', 'product_right_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

}
