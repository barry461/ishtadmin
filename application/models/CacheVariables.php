<?php

/**
 * @property int $id
 * @property string $name 缓存名称
 * @property string $key 缓存key
 * @property int $created_at
 * @property int $updated_at
 * @mixin \Eloquent
 */
class CacheVariablesModel extends BaseModel
{
    protected $table = 'cache_variables';

    public function newEloquentBuilder($query)
    {
        return new \Illuminate\Database\Eloquent\Builder($query);
    }

    protected $fillable = [
        'name', 'key', 'created_at', 'updated_at'
    ];

    public $timestamps = true;
    protected $dateFormat = 'U';
    protected $casts
        = [
            'created' => 'datetime:Y-m-d H:i:s',
            'modified' => 'datetime:Y-m-d H:i:s'
        ];


    public static function adder($name, $key): \Illuminate\Database\Eloquent\Model
    {
        if ($key instanceof \tools\CacheDb){
            $key = $key->generateKeyname();
        }
        $data = [
            'name' => $name,
            'key' => $key,
        ];
        if ($name !== 'group_list'){
            self::adder('group_list', $name);
        }
        return CacheKeysModel::updateOrCreate($data);
    }

    protected static function logInsert($model, $action, $name, $old, $new, $old_attr, $new_attr)
    {
    }


}