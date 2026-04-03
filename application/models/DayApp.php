<?php

/**
 * class AdsAppModel
 *
 * @property int $id
 * @property string $date 统计时间
 * @property string $app_id 记录ID
 * @property string $app_name APP名称
 * @property string $app_thumb APP图标
 * @property int $click_num 点击次数
 * @property int $created_at 创建时间
 * @mixin \Eloquent
 */
class DayAppModel extends BaseModel
{
    protected $table = "day_app";

    protected $primaryKey = 'id';

    protected $fillable = ['date', 'app_id', 'app_name', 'app_thumb', 'click_num', 'created_at', 'updated_at'];

    public function setAppThumbAttribute($value)
    {
        $this->resetSetPathAttribute('app_thumb', $value);
    }

    public function getAppThumbAttribute(): string
    {
        return url_image($this->attributes['app_thumb'] ?? '');
    }

    public static function incrNum($id, $value = 1): int
    {
        $date = date('Y-m-d');
        $ret = redis()->hIncrBy("day-app:{$date}", $id, $value);
        if ($ret <= $value) {
            // 服务器集群模式下，每台服务的时间不统一时候，数据会不准曲
            redis()->lock('day-app-lock:' . date('d'), function () use ($date) {
                $date = date('Y-m-d', strtotime('-1 days', strtotime($date)));
                $key = "day-app:{$date}";
                $all = redis()->hGetAll($key);
                if (empty($all)) {
                    return;
                }
                redis()->del($key);

                // 数据有问题
                $msg = '================' . PHP_EOL;
                $msg .= '开始时间:' . time() . PHP_EOL;
                $msg .= '统计时间:' . $date . PHP_EOL;
                $msg .= '删除KEY:' . $key . PHP_EOL;
                $msg .= print_r($all, true);
                error_log($msg . PHP_EOL, 3,
                    APP_PATH . '/storage/logs/day_app.log');

                jobs([self::class, '_add2db'], [$all, $date]);

                // 数据有问题
                $msg = '结束时间:' . time() . PHP_EOL;
                $msg .= '================' . PHP_EOL;
                error_log($msg . PHP_EOL, 3,
                    APP_PATH . '/storage/logs/day_app.log');
            });
        }
        return $ret;
    }

    public static function _add2db($items, $date)
    {
        if (!is_array($items) || empty($items)) {
            return;
        }
        trigger_log("jobs 入库 " . __METHOD__);
        foreach ($items as $name => $value) {
            $value = (int)$value;
            $rs = AppModel::where('id', $name)->first();
            try {
                retry(2, function () use ($date, $name, $value, $rs) {
                    DayAppModel::lock()->updateOrInsert([
                        'date'   => $date,
                        'app_id' => $name,
                    ], [
                        'app_thumb'  => $rs ? parse_url($rs->thumb, PHP_URL_PATH) : '',
                        'app_name'   => $rs ? $rs->name : '',
                        'click_num'  => DB::raw("`click_num`+$value"),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                });
            } catch (\Throwable $e) {
                trigger_log($e);
            }
        }
    }
}