<?php

/**
 * class EmailLogModel
 *
 *
 * @property int $id
 * @property string $aff
 * @property string $email 手机号
 * @property string $code 邮箱验证码
 * @property string $ip IP地址
 * @property int $status 使用状态 0未用 1已用
 * @property int $type 类型 1绑定邮箱 2手机解绑 3找回账号
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class EmailLogModel extends BaseModel
{
    protected $table = 'email_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'email',
        'code',
        'ip',
        'status',
        'type',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const EMAIL_TYPE_LOGIN = 1;
    const EMAIL_TYPE_BIND = 2;
    const EMAIL_TYPE_FIND = 3;
    const EMAIL_TYPE_CHANGE = 4;
    const EMAIL_TYPE_REGISTER = 5;
    const EMAIL_TYPE_REG_LOGIN = 6;


    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    const TYPE = [
        self::EMAIL_TYPE_LOGIN   => '绑定',
        self::EMAIL_TYPE_BIND   => '找回账号',
        self::EMAIL_TYPE_FIND   => '登陆账号',
        self::EMAIL_TYPE_CHANGE   => '交换手机账号',
        self::EMAIL_TYPE_REGISTER   => '手机注册',
        self::EMAIL_TYPE_REG_LOGIN => '手机注册登录',
    ];

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS_FAIL = 2;
    const STATUS = [
        self::STATUS_NO  => '未用',
        self::STATUS_YES => '已用',
        self::STATUS_FAIL => '发送失败',
    ];

    /**
     * @param int $number 生成位数   建议 4- 6 位
     * @return int
     */
    public static function genEmailCode($number = 6)
    {
        $numbers = range(0, 9);
        shuffle($numbers);
        $result = array_slice($numbers, 0, $number);
        if($result[0] == 0){
            $result[0] = mt_rand(1,9);
        }
        return implode('', $result);
    }

    public static function findByAff($email, $aff, $type){
        return self::where('aff', $aff)->where('email', $email)->where('type', $type)->orderByDesc('id')->first();
    }

    public static function getEmailCode($email, $aff, $type)
    {
        return self::where('aff', $aff)
            ->where('email', $email)
            ->where('type', $type)
//            ->where('status', 0)
            ->orderByDesc('id')
            ->first();
    }

    //邮件发送
    public static function send($email, $code){
        $emailData = [
            'app_name' => config('pay.app_name'),
            'email'    => $email,
            'subject'  => register('site.app_name'),
            'body'     => register('site.bind_email_address_content'),
        ];
        $emailData['sign'] = self::makeSign($emailData, config('pay.pay_signkey'));
        $curl = new \tools\HttpCurl();
        $rs = $curl->post(config('mail.url'), $emailData);
        trigger_log("邮件发送结果:" . $rs . PHP_EOL);
        $rs = json_decode($rs, true);
        if ($rs && $rs['success']) {
            return [
                'code' => 200,
                'msg'  => '发送成功'
            ];
        }
        return [
            'code' => 0,
            'msg'  => '发送失败'
        ];
    }

    public static function makeSign($data, $signKey){
        ksort($data);
        $string = '';
        foreach ($data as $key => $datum) {
            $string .= "{$key}={$datum}&";
        }
        $string .= 'key=' . $signKey;
        return md5($string);
    }

    //邮件发送
    public static function sendContent($email, $subject, $body){
        $emailData = [
            'app_name'  => config('pay.app_name'),
            'email'   => $email,
            'subject' => $subject,
            'body' => $body,
        ];
        $emailData['sign'] = self::makeSign($emailData, config('pay.pay_signkey'));
        $curl = new \tools\HttpCurl();
        $rs = $curl->post(config('mail.url'), $emailData);
        $rs = json_decode($rs, true);
        if ($rs && $rs['success']) {
            return true;
        }
        return false;
    }
}