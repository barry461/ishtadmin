<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class InfoTypeModel
 *
 * @property int $id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $type
 * @property int $status 状态
 * @property int $sort 排序
 * @mixin \Eloquent
 */
class InfoTypeModel extends BaseModel
{

    protected $table = "info_type";

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'type', 'created_at', 'updated_at', 'category', 'status','sort'];

    protected $guarded = 'id';



    const TYPE_STORE = 0;
    const TYPE_PERSONAL = 1;
    const TYPE = [
        self::TYPE_STORE    => '探店',
        self::TYPE_PERSONAL => '个人',
    ];


    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    const STATUS_ARR = [
        self::STATUS_OFF=> '下线',
        self::STATUS_ON => '上线',
    ];


}