<?php

/**
 * @property int $id
 * @property string $date 日期
 * @property int $aff 用户AFF
 * @property int $channel self / ug-xxx
 * @property int $ip_ct 邀请的人的IP数
 * @property int $add_ct 邀请的人数
 * @property float $recharge 邀请的人的今日充值金额 单位分
 * @property int $day1_retain 1日留存
 * @property int $day3_retain 3日留存
 * @property int $day7_retain 7日留存
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @mixin \Eloquent
 */
class DayInviteModel extends BaseModel
{
    protected $table = 'day_invite';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'date',
        'aff',
        'channel',
        'ip_ct',
        'add_ct',
        'recharge',
        'day1_retain',
        'day3_retain',
        'day7_retain',
        'created_at',
        'updated_at',
    ];

    const DAY_INVITE_RECORD_KEY = 'day:invite:record:%s:%s:%s';// hash
    const DAY_INVITE_ALL_KEY = 'day:invite:all:%s';// set
    const DAY_INVITE_IP_KEY = 'day:invite:ip:%s:%s';// set
    const DAY_INVITE_ALL_KEY_EXPIRE = 90000;// 25个小时
    const DAY_INVITE_RECORD_KEY_EXPIRE = 90000;// 25个小时
    const DAY_INVITE_IP_KEY_EXPIRE = 90000;// 25个小时
    const DAY_1 = 1;
    const DAY_3 = 3;
    const DAY_7 = 7;

    // 加入统计集合
    public static function all_record($all_key, $key)
    {
        redis()->sAdd($all_key, $key);
        redis()->ttl($all_key) == -1 && redis()->expire($all_key, self::DAY_INVITE_ALL_KEY_EXPIRE);
    }

    private static function set_ttl($key, $expire)
    {
        redis()->ttl($key) == -1 && redis()->expire($key, $expire);
    }

    // 添加IP统计
    private static function ip($record_key, $date, $aff, $ip)
    {
        $ip_key = sprintf(self::DAY_INVITE_IP_KEY, $aff, $date);
        if (redis()->sAdd($ip_key, $ip)) {
            redis()->hIncrBy($record_key, 'ip_ct', 1);
        }
        self::set_ttl($ip_key, self::DAY_INVITE_IP_KEY_EXPIRE);
    }

    // 添加邀请人数统计
    private static function add($record_key)
    {
        redis()->hIncrBy($record_key, 'add_ct', 1);
        self::set_ttl($record_key, self::DAY_INVITE_RECORD_KEY_EXPIRE);
    }

    // 添加邀请人充值统计
    private static function recharge($record_key, $value)
    {
        redis()->hIncrBy($record_key, 'recharge', $value);
        self::set_ttl($record_key, self::DAY_INVITE_RECORD_KEY_EXPIRE);
    }

    // 添加1日留存统计
    private static function day1_retain($record_key)
    {
        redis()->hIncrBy($record_key, 'day1_retain', 1);
        self::set_ttl($record_key, self::DAY_INVITE_RECORD_KEY_EXPIRE);
    }

    // 添加3日留存统计
    private static function day3_retain($record_key)
    {
        redis()->hIncrBy($record_key, 'day3_retain', 1);
        self::set_ttl($record_key, self::DAY_INVITE_RECORD_KEY_EXPIRE);
    }

    // 添加7日留存统计
    private static function day7_retain($record_key)
    {
        redis()->hIncrBy($record_key, 'day7_retain', 1);
        self::set_ttl($record_key, self::DAY_INVITE_RECORD_KEY_EXPIRE);
    }

    private static function base_info($aff, $channel)
    {
        $date = date('Y-m-d');
        $all_key = sprintf(self::DAY_INVITE_ALL_KEY, $date);
        $record_key = sprintf(self::DAY_INVITE_RECORD_KEY, $aff, $channel, $date);
        return [$date, $all_key, $record_key];
    }

    private static function wl($name, $args)
    {
        $msg = $name . ':' . print_r($args, true) . PHP_EOL;
        error_log($msg, 3, APP_PATH . '/storage/logs/day-invite.log');
    }

    // 邀请完成时
    public static function invite($aff, $channel, $ip)
    {
        self::wl(__FUNCTION__, func_get_args());
        list($date, $all_key, $record_key) = self::base_info($aff, $channel);
        self::ip($record_key, $date, $aff, $ip);// 今日邀请的IP数
        self::add($record_key);// 今日邀请多少人
        self::all_record($all_key, $record_key);
    }

    // 支付回调时
    public static function pay($aff, $channel, $value)
    {
        self::wl(__FUNCTION__, func_get_args());
        list($date, $all_key, $record_key) = self::base_info($aff, $channel);
        self::recharge($record_key, $value);
        self::all_record($all_key, $record_key);
    }

    // 日活更新时
    public static function retain($type, $aff, $channel)
    {
        self::wl(__FUNCTION__, func_get_args());
        list($date, $all_key, $record_key) = self::base_info($aff, $channel);
        switch ($type) {
            case self::DAY_1:
                self::day1_retain($record_key);
                break;
            case self::DAY_3:
                self::day3_retain($record_key);
                break;
            case self::DAY_7:
                self::day7_retain($record_key);
                break;
        }
        self::all_record($all_key, $record_key);
    }

    public static function import2db($date)
    {
        $all_key = sprintf(self::DAY_INVITE_ALL_KEY, $date);
        $all_keys = redis()->sMembers($all_key);
        foreach ($all_keys as $key) {
            $replace_str = str_replace('%s:%s:%s', '', self::DAY_INVITE_RECORD_KEY);
            $info = trim(str_replace($replace_str, '', $key));
            $info = explode(":", $info);
            test_assert(count($info) == 3, '缓存KEY异常');
            $all_info = redis()->hGetAll($key);
            if (!$all_info) {
                continue;
            }
            $data = [
                'aff'         => $info[0] ?? 0,
                'date'        => $info[2] ?? '',
                'channel'     => $info[1] ?? 0,
                'ip_ct'       => $all_info['ip_ct'] ?? 0,
                'add_ct'      => $all_info['add_ct'] ?? 0,
                'recharge'    => $all_info['recharge'] ?? 0,
                'day1_retain' => $all_info['day1_retain'] ?? 0,
                'day3_retain' => $all_info['day3_retain'] ?? 0,
                'day7_retain' => $all_info['day7_retain'] ?? 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            $isOk = self::create($data);
            test_assert($isOk, '系统异常');
            redis()->del($key);
            $ip_key = sprintf(self::DAY_INVITE_IP_KEY, $info[0], $info[2]);
            redis()->del($ip_key);
            $msg = '时间:' . $date . PHP_EOL;
            $msg .= '入库数据:' . PHP_EOL . print_r($data, true) . PHP_EOL;
            $msg .= '删除KEY:' . $key . PHP_EOL;
            $msg .= '删除KEY:' . $ip_key . PHP_EOL;
            error_log($msg, 3, APP_PATH . '/storage/logs/day-invite.log');
        }
        redis()->del($all_key);
        $msg = '删除KEY:' . $all_key . PHP_EOL;
        error_log($msg, 3, APP_PATH . '/storage/logs/day-invite.log');
    }
}