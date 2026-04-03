<?php

/**
 * class SmsLogModel
 *
 * @property int $id
 * @property string $uuid
 * @property string $prefix 国家码
 * @property string $mobile 手机号
 * @property int $code 短信验证码
 * @property string $ip IP地址
 * @property int $status 使用状态 0未用 1已用
 * @property int $type 类型 1绑定手机 2手机解绑 3找回账号
 * @property string $created_at
 * @property string $updated_at
 *
 *
 * @mixin \Eloquent
 */
class SmsLogModel extends BaseModel
{
    protected $table = "sms_log";

    protected $primaryKey = 'id';

    protected $fillable = ['uuid', 'prefix', 'mobile', 'code', 'ip', 'status', 'type', 'created_at', 'updated_at'];

    protected $guarded = 'id';

    const SMS_TYPE_LOGIN = 1;
    const SMS_TYPE_BIND = 2;
    const SMS_TYPE_FIND = 3;
    const SMS_TYPE_CHANGE = 4;
    const SMS_TYPE_REGISTER = 5;
    const SMS_TYPE_REG_LOGIN = 6;

    /**
     * @param int $number 生成位数   建议 4- 6 位
     * @return int
     */
    static function genSmsCode($number = 6)
    {
        $numbers = range(0, 9);
        shuffle($numbers);
        $result = array_slice($numbers, 0, $number);
        if($result[0] == 0){
            $result[0] = mt_rand(1,9);
        }
        return implode('', $result);
    }


    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    const TYPE = [
        self::SMS_TYPE_BIND   => '绑定',
        self::SMS_TYPE_FIND   => '找回账号',
        self::SMS_TYPE_LOGIN   => '登陆账号',
        self::SMS_TYPE_CHANGE   => '交换手机账号',
        self::SMS_TYPE_REGISTER   => '手机注册',
        self::SMS_TYPE_REG_LOGIN => '手机注册登录',
    ];

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS = [
        self::STATUS_NO  => '未用',
        self::STATUS_YES => '已用',
    ];

}
