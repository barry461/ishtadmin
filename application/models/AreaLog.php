<?php

/**
 * class AreaLogModel
 *
 * @property int $id 
 * @property string $uuid 
 * @property string $url 检测域名
 * @property string $ip 检测ip
 * @property string $area 
 * @property int $sick 返回状态
 * @property string $type login 打开app  av视频 xiao 小视频
 * @property int $created_at 检测时间
 *
 
 * @date 2020-01-08 17:09:04
 *
 * @mixin \Eloquent
 */
class AreaLogModel extends BaseModel
{
    protected $table = "area_log";

    protected $primaryKey = 'id';

    protected $fillable = ['uuid', 'url', 'ip', 'area','oauth_type', 'sick', 'type', 'created_at'];

    protected $guarded = 'id';
}
