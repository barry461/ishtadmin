<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class SysTotalModel
 * 
 * 
 * @property int $id  
 * @property string $name 减值 
 * @property int $value 统计 
 * @property string $date 日期 
 * 
 * 
 *
 * @mixin \Eloquent
 */
class SysTotalModel extends BaseModel
{
    /**
     * @var int|mixed
     */
    public static $indentation = 1;
    protected $table = 'sys_total';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'name', 'value', 'date'];
    protected $guarded = 'id';
    public $timestamps = false;
    public static function incrBy($name, $value = 1, $date = null) : int
    {
        // return 0;
        if ($value == 0) {
            return 0;
        }
        if ($date === null) {
            $date = date('Y-m-d');
        }
        $ret = redis()->hIncrBy("sys-total:{$date}", $name, $value);
        return $ret;
    }

    public static function crontabToDb($date){
        redis()->lock('sys-lock:' . date('d'), function () use($date) {
            // 服务器集群模式下，每台服务的时间不统一时候，数据会不准曲
            $key = "sys-total:{$date}";
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
            $msg .= json_encode($all, JSON_UNESCAPED_UNICODE);
            error_log($msg . PHP_EOL, 3, APP_PATH . '/storage/logs/sys_total.log');
            self::_add2db($all , $date);

            // 数据有问题
            $msg = '结束时间:' . time() . PHP_EOL;
            $msg .= '================' . PHP_EOL;
            error_log($msg . PHP_EOL, 3, APP_PATH . '/storage/logs/sys_total.log');
        });
    }

    public static function _add2db($items, $date)
    {
        if (!is_array($items) || empty($items)) {
            return;
        }
        $data = [];
        foreach ($items as $name => $value) {
            $value = (int)$value;
            $data[] = ['date' => $date, 'name' => $name, 'value' => $value];
        }
        $chunks = array_chunk($data, 100);
        foreach ($chunks as $chunk) {
            SysTotalModel::insert($chunk);
        }
    }

    public static function getValueBy($name, $date = null)
    {
        $date = \Carbon\Carbon::parse($date)->toDateString();
        if ($date == date('Y-m-d')) {
            $value = redis()->hGet("sys-total:{$date}", $name);
        } else {
            $value = self::where(['name' => $name, 'date' => $date])->value('value');
        }
        return div_allow_zero((int) $value, self::$indentation);
    }

    public static function getRangeValue($name, array $dates)
    {
        $list = self::where('name', $name)->whereIn('date', $dates)->get()->keyBy('date');
        foreach ($dates as $date) {
            if (!$list->offsetExists($date)) {
                if ($date == date('Y-m-d')) {
                    $v = (int) redis()->hGet("sys-total:{$date}", $name);
                    $object = SysTotalModel::make(['name' => $name, 'value' => $v, 'date' => $date]);
                } else {
                    $object = SysTotalModel::make(['name' => $name, 'value' => 0, 'date' => $date]);
                }
                $list->offsetSet($date, $object);
            }
        }
        return $list;
    }
}