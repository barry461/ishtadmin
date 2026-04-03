<?php

/**
 * class BigEventModel
 *
 *
 * @property int $id
 * @property string $title 标题
 * @property string $desc 介绍
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class BigEventModel extends BaseModel
{
    protected $table = 'big_event';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'title',
        'desc',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const CK_BIG_EVENT_DETAIL = 'ck:big:event:%d';
    const GP_BIG_EVENT_DETAIL = 'gp:big:event';
    const CN_BIG_EVENT_DETAIL = '大事件详情';


    public static function findById($sid){
        $key = sprintf(self::CK_BIG_EVENT_DETAIL, $sid);
        return cached($key)
            ->group(self::GP_BIG_EVENT_DETAIL)
            ->chinese(self::CN_BIG_EVENT_DETAIL)
            ->fetchPhp(function () use ($sid){
                return self::selectRaw('id, title, `desc`')->where('id', $sid)->first();
            });
    }
}