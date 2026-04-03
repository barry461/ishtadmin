<?php


/**
 * class AppointmentModel
 *
 * @property int $aff
 * @property int $created_at
 * @property int $freeze_money 冻结元宝
 * @property int $id
 * @property int $info_id
 * @property int $status
 * @property int $updated_at
 *
 * @property MemberModel $member
 * @property InfoVipModel $info
 *
 * @mixin \Eloquent
 */
class AppointmentModel extends BaseModel
{

    protected $table = "appointment";

    protected $primaryKey = 'id';

    protected $fillable = ['aff', 'created_at', 'coupon_id','freeze_money','info_id', 'admin_name', 'status','updated_at'];
    protected $appends = ['created_str', 'updated_str', 'status_str'];

    const STATUS_INIT = 1;
    const STATUS_FINISH = 2;
    const STATUS_CANCEL = 3;
    const STATUS_CONFIRM = 4;
    public $timestamps = false;
    const STATUS = [
        self::STATUS_INIT    => '初始',
        self::STATUS_FINISH  => '完成',
        self::STATUS_CANCEL  => '取消',
        self::STATUS_CONFIRM => '确认',
    ];

    const SOURCE = [
        1 => '雅间经纪人',
        2 => '茶女郎',
    ];

    public function getUpdatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['updated_at'] ?? 0);
    }

    public function getCreatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
    }

    public function getStatusStrAttribute()
    {
        return self::STATUS[$this->attributes['status'] ?? self::STATUS_INIT];
    }

    public function info()
    {
        return $this->hasOne(InfoVipModel::class, 'id', 'info_id');
    }

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }


}