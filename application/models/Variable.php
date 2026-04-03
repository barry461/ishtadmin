<?php

/**
 * class SettingModel
 *
 * @property int $id
 * @property string $title 配置名
 * @property string $var_name 配置key名称
 * @property string $value 配置值
 * @property string $remark 配置备注
 * @property string $status 状态
 * @author xiongba
 * @date 2020-02-26 12:59:00
 * @mixin \Eloquent
 */
class VariableModel extends BaseModel
{
    protected $table = "variable";

    protected $primaryKey = 'id';

    protected $fillable = ['title', 'var_name', 'value', 'remark', 'status'];

    protected $guarded = 'id';

    public $timestamps = false;

    const STATUS_YES = 'yes';
    const STATUS_NO = 'no';
    const STATUS
        = [
            self::STATUS_YES => 'yes',
            self::STATUS_NO  => 'no',
        ];

    // const REDIS_KEY = 'system:variable';

    /**
     * 设置变量值
     *
     * @param  string|int  $key  变量名
     * @param  mixed  $val  变量值
     *
     * @return void
     * @throws \Exception 当变量名重复时抛出异常
     */
    public static function set($key, $val)
    {
        $model = self::where(['var_name' => $key])->first();
        if (empty($model)) {
            $exists = self::where('var_name', $key)->exists();
            if ($exists) {
                throw new \Exception("变量名 '{$key}' 已存在");
            }

            self::create([
                'var_name' => $key,
                'value'    => $val,
                'remark'   => '',
                'status'   => self::STATUS_YES,
            ]);
        } else {
            $model->value = $val;
            $model->save();
        }
        // self::pushCached();
    }

    public static function clearCached()
    {
        cached('x-variable')->touch(-100);
    }


    public static function variables()
    {
        $data = yac()->fetch('x-variable', function () {
            return cached('x-variable')->fetchPhp(function () {
                return VariableModel::where('status', VariableModel::STATUS_YES)
                    ->pluck('value', 'var_name')
                    ->toArray();
            });
        }, 300);
        if (empty($data)) {
            return [];
        }
        if (!defined('\\GLOBAL_VARIABLES')) {
            return $data;
        }
        foreach ($data as $datum => $val) {
            $data[$datum] = GLOBAL_VARIABLES[$val] ?? $val;
        }
        return $data;
    }


    // public static function pushCached()
    // {
    //     $all = self::where('status', '=', static::STATUS_YES)
    //         ->useWritePdo()
    //         ->get();
    //         redis()->del(self::REDIS_KEY);
    //     $all->map(function ($model) {
    //         /** @var self $model */
    //         redis()->hSet(self::REDIS_KEY, $model->var_name, $model->value);
    //     });
    // }
}