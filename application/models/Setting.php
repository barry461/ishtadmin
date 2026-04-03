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
 *
 * @author xiongba
 * @date 2020-02-26 12:59:00
 *
 * @mixin \Eloquent
 */
class SettingModel extends BaseModel
{
    protected $table = "setting";

    protected $primaryKey = 'id';

    protected $fillable = ['title', 'var_name', 'value', 'remark', 'status'];

    protected $guarded = 'id';

    public $timestamps = false;
    
    const STATUS_YES = 'yes';
    const STATUS_NO = 'no';
    const STATUS = [
        self::STATUS_YES => 'yes',
        self::STATUS_NO  => 'no',
    ];

    const REDIS_KEY = 'system:setting';
     
    public static function set($key, $val)
    {
        $model = self::where(['var_name' => $key])->first();
        if (empty($model)) {
            self::create([
                'title'    => $key,
                'var_name' => $key,
                'value'    => $val,
                'remark'   => '',
                'status'   => self::STATUS_YES
            ]);
        } else {
            $model->value = $val;
            $model->save();
        }
        self::pushCached();
    }

    public static function pushCached()
    {
        $all = self::where('status', '=', static::STATUS_YES)
            ->useWritePdo()
            ->get();
            redis()->del(self::REDIS_KEY);
        $all->map(function ($model) {
            /** @var self $model */
            redis()->hSet(self::REDIS_KEY, $model->var_name, $model->value);
        });
    }
}