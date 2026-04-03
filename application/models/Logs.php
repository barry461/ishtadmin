<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class LogsModel
 *
 * @property int $id
 * @property string $type 类型，error,success
 * @property string $ip
 * @property string $ua
 * @property string $http_refer_host sever_refer_host
 * @property string $http_refer_url sever_refer_url
 * @property string $browser_referer document.refer
 * @property string $line_url line url
 * @property string $line_host line host
 * @property string $date
 * @property int $created
 *
 * @author xiongba
 * @date 2022-11-04 09:04:14
 *
 * @mixin \Eloquent
 */
class LogsModel extends Model
{

    protected $table = "logs";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'type',
            'ip',
            'ua',
            'http_refer_host',
            'http_refer_url',
            'browser_referer',
            'line_url',
            'line_host',
            'date',
            'created',
        ];

    protected $guarded = 'id';

    public $timestamps = false;

    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';
    const TYPE
        = [
            self::TYPE_ERROR => '线路探测失败',
            self::TYPE_SUCCESS => '线路探测成功',
        ];


}
