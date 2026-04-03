<?php


/**
 * class AgentTmpPayingModel
 *
 * @property int $id
 * @property int $aff 用户aff
 * @property int $product_id 产品id
 * @property int $price 价格
 * @property string $created_at
 *
 *
 * @property ProductModel $product
 *
 * @mixin \Eloquent
 */
class AgentTmpPayingModel extends BaseModel
{

    protected $table = "agent_tmp_paying";

    protected $primaryKey = 'id';

    protected $fillable = ['aff', 'product_id', 'price', 'created_at'];

    protected $guarded = 'id';

    const UPDATED_AT = null;

    public static function createBy(MemberModel $member, ProductModel $product,$pay_amount)
    {
        return self::create([
            'aff'        => $member->aff,
            'product_id' => $product->id,
            'price'      => $pay_amount,
        ]);
    }


    public function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

}
