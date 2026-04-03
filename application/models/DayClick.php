<?php

/**
 * class DayAdsModel
 *
 * @property int $id
 * @property string $date 统计时间
 * @property string $type 类型
 * @property string $record_id 记录ID
 * @property string $record_name APP名称
 * @property string $record_thumb APP图标
 * @property string $record_url 跳转地址
 * @property string $record_position 位置
 * @property int $click_num 点击次数
 * @property int $created_at 创建时间
 * @mixin \Eloquent
 */
class DayClickModel extends BaseModel
{
    protected $table = "day_click";

    protected $primaryKey = 'id';

    protected $fillable = ['date', 'type', 'record_id', 'record_name', 'record_thumb', 'record_url', 'record_position', 'click_num', 'created_at', 'updated_at'];

    const TYPE_ADS = 0;
    const TYPE_NOTICE = 1;
    const TYPE_APP = 3;
    const TYPE_NOTICE_APP = 4;
    const TYPE_TIPS = [
        self::TYPE_ADS    => '固定位广告',
        self::TYPE_NOTICE => '弹框广告',
        self::TYPE_APP    => '福利APP',
        self::TYPE_NOTICE_APP    => '弹窗APP',
    ];
    const TYPE_PREFIXS = [
        self::TYPE_ADS    => 'day-ads',
        self::TYPE_NOTICE => 'day-notice',
        self::TYPE_APP    => 'day-app',
        self::TYPE_NOTICE_APP => 'day-notice-app'
    ];
    const KEY_RULE = '%s:%s';
    const KEY_LOCK_RULE = '%s-lock:%s';
    protected $attributes
        = [
            'record_name' => '',
        ];

    public function setRecordThumbAttribute($value)
    {
        $this->resetSetPathAttribute('record_thumb', $value);
    }

    public function getRecordThumbAttribute(): string
    {
        return url_image($this->attributes['record_thumb'] ?? '');
    }

    public static function incrNum($type, $id, $value = 1)
    {
        $date = date('Y-m-d', TIMESTAMP);
        $key_type = self::TYPE_PREFIXS[$type];
        $cur_key = sprintf(self::KEY_RULE, $key_type, $date);
        redis()->hIncrBy($cur_key, $id, $value);
    }

    public static function _add_task($type, $date)
    {
        $key_type = self::TYPE_PREFIXS[$type];
        $d = date('d', strtotime('+1 days', strtotime($date)));
        $lock_key = sprintf(self::KEY_LOCK_RULE, $key_type, $d);
        $prev_date = date('Y-m-d', strtotime($date));
        $prev_key = sprintf(self::KEY_RULE, $key_type, $prev_date);

        redis()->lock($lock_key, function () use ($type, $prev_key, $prev_date, $key_type) {
            $all = redis()->hGetAll($prev_key);
            trigger_log('当前KEY:' . $prev_key . PHP_EOL . '当前数据' . print_r($all, true));
            if (empty($all)) {
                return;
            }
            redis()->del($prev_key);

            // 数据有问题
            $msg = '================' . PHP_EOL;
            $msg .= '开始时间:' . time() . PHP_EOL;
            $msg .= '统计时间:' . $prev_date . PHP_EOL;
            $msg .= '删除KEY:' . $prev_key . PHP_EOL;
            $msg .= print_r($all, true);
            error_log($msg . PHP_EOL, 3, APP_PATH . '/storage/logs/' . $key_type . '.log');

            jobs([self::class, '_add2db'], [$type, $all, $prev_date]);

            // 数据有问题
            $msg = '结束时间:' . time() . PHP_EOL;
            $msg .= '================' . PHP_EOL;
            error_log($msg . PHP_EOL, 3, APP_PATH . '/storage/logs/' . $key_type . '.log');
        });
    }

    public static function replace_url($url)
    {
        return json_decode(replace_share(json_encode($url)));
    }

    private static function get_type_data($type, $id)
    {
        $record_thumb = '';
        $record_name = '';
        $record_position_str = '';
        $record_url = '';
        switch ($type) {
            case self::TYPE_ADS:
                $rs = AdsModel::where('id', $id)->first();
                if ($rs) {
                    $record_thumb = parse_url($rs->img_url, PHP_URL_PATH);
                    $record_name = $rs->title;
                    $record_position_str = AdsModel::POSITION[$rs->position] ?? '';
                    $record_url = self::replace_url($rs->url_str);
                }
                break;
            case self::TYPE_NOTICE:
                $rs = NoticeModel::where('id', $id)->first();
                if ($rs) {
                    $record_thumb = parse_url($rs->img_url, PHP_URL_PATH);
                    $record_name = $rs->title;
                    $record_position_str = NoticeModel::POS[$rs->pos] ?? '';
                    $record_url = self::replace_url($rs->url_str);
                }
                break;
            case self::TYPE_APP:
                $rs = AppModel::where('id', $id)->first();
                if ($rs) {
                    $record_thumb = parse_url($rs->thumb, PHP_URL_PATH);
                    $record_name = $rs->name;
                    $record_position_str = '';
                    $record_url = self::replace_url($rs->url);
                }
                break;
        }
        return [$record_name, $record_thumb, $record_position_str, $record_url];
    }

    public static function _add2db($type, $items, $date)
    {
        if (!is_array($items) || empty($items)) {
            return;
        }
        trigger_log("jobs 入库:" . __METHOD__);
        foreach ($items as $name => $value) {
            $value = (int)$value;
            list($record_name, $record_thumb, $record_position_str, $record_url) = self::get_type_data($type, $name);
            $condition = [
                'date'      => $date,
                'type'      => $type,
                'record_id' => $name,
            ];
            $data = [
                'record_thumb'    => $record_thumb,
                'record_name'     => $record_name,
                'record_position' => $record_position_str,
                'record_url'      => $record_url,
                'click_num'       => DB::raw("`click_num`+$value"),
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s')
            ];
            trigger_log('入库数据:' . print_r(array_merge($condition, $data), true));
            try {
                retry(2, function () use ($condition, $data) {
                    self::lock()->updateOrInsert($condition, $data);
                });
            } catch (\Throwable $e) {
                trigger_log($e);
            }
        }
    }
}