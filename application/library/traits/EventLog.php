<?php

namespace traits;


use service\AdsService;
use Throwable;

trait EventLog
{

    public function newEloquentBuilder($query)
    {
        return new \tools\LibBuilder($query);
    }

    protected function bootIfNotBooted()
    {
        if (self::$dispatcher === null) {
            if (class_exists('Illuminate\Events\Dispatcher')) {
                self::$dispatcher = new \Illuminate\Events\Dispatcher();
            }
        }
        parent::bootIfNotBooted();
    }

    protected static function booted()
    {
        static::created(function ($model) {
            $old = [];
            $new = $model->getAttributes();
            self::logInsert($model, 'created', "新加了{{$model->table}}的数据", $old, $new, $old, $new);
        });
        static::updated(function (self $model) {
            $attributes = $model->getAttributes();
            $original = $model->getOriginal();
            $attrDiff = array_diff_assoc($attributes, $original);
            $oriDiff = array_diff_assoc($original, $attributes);
            $keys = array_merge(array_keys($attrDiff), array_keys($oriDiff));
            $diff = array_filter(array_unique($keys));
            $new = $old = ['_pk' => $attributes[$model->primaryKey]];
            foreach ($diff as $_ => $key) {
                $new[$key] = $attributes[$key] ?? null;
                $old[$key] = $original[$key] ?? null;
            }
            self::logInsert($model, 'updated', "修改了{{$model->table}}的数据", $old, $new, $original, $attributes);
        });
        static::deleted(function (self $model) {
            $old = $model->getAttributes();
            $new = [];
            self::logInsert($model, 'deleted', "删除了{{$model->table}}的数据", $old, $new, $old, []);
        });
    }

    protected static function logInsert($model, $action, $name, $old, $new, $old_attr, $new_attr)
    {
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        if (strpos($uri, 'admin') !== false) {
            if ($model instanceof \AdminLogModel) {
                return;
            }
            $username = $_SERVER['username'] ?? '';
            $context = json_encode(['table' => $model->getTable(), 'new' => $new, 'old' => $old, 'time' => time()]);
            $data = [
                'username'   => $username,
                'action'     => $action,
                'ip'         => USER_IP,
                'log'        => $username . ' ' . $name,
                'referrer'   => $uri,
                'context'    => $context,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $record_id = \AdminLogModel::query()->insertGetId($data);
            try {
                // 广告更新上报 $table, $action, $model, $record_id, $username, $old, $new
                AdsService::eventCall($model->getTable(), $action, $model, $record_id, $username, $old_attr, $new_attr);
            } catch (Throwable $e) {
                trigger_log($e);
            }
        }
    }
}