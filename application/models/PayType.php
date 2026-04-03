<?php

/**
 * class PayTypeModel
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $order
 * @property string $ch_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @mixin \Eloquent
 */
class PayTypeModel extends BaseModel
{

    protected $table = "pay_type";

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'status', 'order', 'ch_name', 'created_at', 'updated_at'];

    protected $hidden = ['created_at' , 'updated_at'];

    protected $guarded = 'id';

    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const STATUS = [
        self::STATUS_YES => '启用',
        self::STATUS_NO => '关闭',
    ];
    public $dateFormat = 'U';

}
