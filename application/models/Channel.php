<?php

/**
 * class ChannelModel
 *
 * @property int $id
 * @property string $channel_num 渠道编号
 * @property string $channel_id 渠道唯一标识
 * @property string $name 渠道名称
 * @property string $rate 分成比例
 * @property int $status 状态
 * @property int $aff 渠道归属aff
 * @property string $parent_channel
 * @property int $agent_level
 * @property int $created_at
 * @property int $updated_at
 * @property int $web_stat
 *
 * @mixin \Eloquent
 */
class ChannelModel extends BaseModel
{
    protected $table = 'channel';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'channel_num',
            'channel_id',
            'name',
            'rate',
            'status',
            'aff',
            'parent_channel',
            'agent_level',
            'created_at',
            'updated_at',
            'web_stat',
        ];

    public static function findByChanId($s , $writePdo = false): ?ChannelModel
    {
        $query = ChannelModel::query();
        if ($writePdo){
            $query->useWritePdo();
        }
        /** @var self $model */
        $model = $query->where('channel_id' , $s)->first();
        return $model;
    }

    public static function findByAff($s , $writePdo = false): ?ChannelModel
    {
        $query = ChannelModel::query();
        if ($writePdo){
            $query->useWritePdo();
        }
        /** @var self $model */
        $model = $query->where('aff' , $s)->first();
        return $model;
    }

}