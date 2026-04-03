<?php
use Illuminate\Events\Dispatcher;

/**
 * 雅间资源评价模型
 *
 * @Author:       zhoukai
 * @Date:         2020-07-09 10:28:48
 * @LastEditTime: 2020-07-12 17:20:30
 * @FilePath:     /laochaguan/application/models/InfoVipConfirm.php
 */


/**
 * Class InfoVipConfirmModel
 *
 * @property int $aff
 * @property int $created_at
 * @property int $girl_face 颜值
 * @property int $girl_service 服务
 * @property int $id
 * @property int $info_id
 * @property int $p_id
 *
 *
 * @property InfoVipModel $info
 * @property MemberModel $member
 *
 * @mixin \Eloquent
 */
class InfoVipConfirmModel extends BaseModel
{

    protected $table = "info_vip_confirm";

    protected $primaryKey = 'id';

    protected $fillable = [
        'aff',
        'p_id',
        'created_at',
        'girl_face',
        'girl_service',
        'info_id',
        'desc',
        'is_real',
        'status'
    ];

    const UPDATED_AT = null;

    const STATUS_INIT = 0;
    const STATUS_NORMAL = 1;
    const STATUS_BANNED = 2;
    const STATUS = [
        self::STATUS_INIT   => '未审核',
        self::STATUS_NORMAL   => '普通的',
        self::STATUS_BANNED => '禁止'
    ];

    const REDIS_KEY_INFO_VIP_CONFIRM_LIST = 'info:vip:confirm:list:';

    protected $appends = ['created_str'];


//    protected static function booted()
//    {
//        static::setEventDispatcher(new Dispatcher());
//        static::saved(function ($confirm) {
//            /** @var self $confirm */
//            bg_run(function () use($confirm){
//                redis()->bulkDel(config("redis.prefix"). InfoVipConfirmModel::REDIS_KEY_INFO_VIP_CONFIRM_LIST . $confirm->info_id . "*");
//            });
//        });
//    }

    public function getCreatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
    }

    public function info()
    {
        return $this->hasOne(InfoVipModel::class, 'id', 'info_id');
    }

    public function member()
    {
        return $this->belongsTo(MemberModel::class, 'uid', 'aff');
    }
}