<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class ExchangeModel
 *
 * @property int $aff 
 * @property int $amount 
 * @property string $created_at 
 * @property int $id 
 * @property int $new_vip_level 
 * @property int $old_vip_level 
 * @property int $product_id 
 * @mixin \Eloquent
 * @mixin \Eloquent
 */
class ExchangeModel extends Model
{

    protected $table = "exchange";

    protected $primaryKey = 'id';

    protected $fillable = ['aff', 'amount', 'created_at', 'new_vip_level', 'old_vip_level', 'product_id'];

    CONST UPDATED_AT = NULL;


}