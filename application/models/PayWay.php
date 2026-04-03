<?php

/**
 * class PayWayModel
 *
 * @property int $id
 * @property string $channel
 * @property string $name
 * @property int $order
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @mixin \Eloquent
 */
class PayWayModel extends BaseModel
{

    protected $table = "pay_way";

    protected $primaryKey = 'id';

    protected $fillable = ['channel', 'name', 'order', 'status', 'created_at', 'updated_at'];

    protected $hidden = ['created_at' , 'updated_at'];

    protected $guarded = 'id';

    public $dateFormat = 'U';

    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const STATUS = [
        self::STATUS_YES => '启用',
        self::STATUS_NO  => '关闭',
    ];

}
