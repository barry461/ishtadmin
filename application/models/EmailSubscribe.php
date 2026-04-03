<?php

/**
 * class EmailSubscribeModel
 *
 *
 * @property int $id
 * @property int $aff 订阅aff
 * @property string $email 邮箱
 * @property int $send_ct 邮件发送次数
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class EmailSubscribeModel extends BaseModel
{
    protected $table = 'email_subscribe';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'email',
        'send_ct',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    public static function hasSubscribe($aff){
        return self::where('aff', $aff)->exists() ? 1 : 0;
    }
}