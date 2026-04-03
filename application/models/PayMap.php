<?php

/**
 * class PayMapModel
 *
 * @property int $id
 * @property int $product_id
 * @property int $type_id
 * @property int $way_id
 *
 * @property PayTypeModel $type
 * @property ProductModel $product
 * @property PayWayModel $way
 *
 * @mixin \Eloquent
 */
class PayMapModel extends BaseModel
{
    protected $table = "pay_map";

    protected $primaryKey = 'id';

    protected $fillable = ['product_id', 'type_id', 'way_id'];

    public $dateFormat = 'U';

    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const STATUS = [
        self::STATUS_YES => '启用',
        self::STATUS_NO => '关闭',
    ];

    public $timestamps = false;

    public function type(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PayTypeModel::class, 'id', 'type_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

    public function way(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PayWayModel::class, 'id', 'way_id')->where('status', PayWayModel::STATUS_YES);
    }


}