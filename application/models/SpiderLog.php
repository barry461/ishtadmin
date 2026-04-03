<?php

/**
 * 蜘蛛访问记录模型
 *
 * @property int $id
 * @property string $spider_name 蜘蛛名称
 * @property string $user_agent UA
 * @property string $request_uri 访问 URI
 * @property string $referer 来源
 * @property string $ip IP 地址
 * @property string $http_method 请求方法
 * @property int $status HTTP 状态码
 * @property int $created_at 访问时间（时间戳）
 *
 * @mixin \Eloquent
 */
class SpiderLogModel extends BaseModel
{
    protected $table = 'spiderlog';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'spider_name',
        'user_agent',
        'request_uri',
        'referer',
        'ip',
        'http_method',
        'status',
        'created_at',
    ];
}

