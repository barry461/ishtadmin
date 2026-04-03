<?php

/**
 * @property int $id
 * @property int $aff 用户AFF
 * @property string $update 更新信息
 * @property int $status 状态 0待审核 1未通过 2已通过
 * @property string $refuse_reason 拒绝原因
 * @property string $created_at 更新信息
 * @property string $updated_at 更新信息
 *
 * @property PostMediaModel[]|\Illuminate\Support\Collection $medias
 *
 * @mixin \Eloquent
 */
class MemberUpdateLogModel extends BaseModel
{
    protected $table = 'member_update_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'update',
        'status',
        'refuse_reason',
        'created_at',
        'updated_at',
    ];

    const STATUS_WAIT = 0;
    const STATUS_REJECT = 1;
    const STATUS_PASS = 2;
    const STATUS_TIPS = [
        self::STATUS_WAIT   => '待审核',
        self::STATUS_REJECT => '未通过',
        self::STATUS_PASS   => '已通过',
    ];

    public static function createRecord($aff, $update)
    {
        return self::create([
            'aff'        => $aff,
            'update'     => json_encode($update),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function hasMonthRecord($aff){
        return MemberUpdateLogModel::where('aff', $aff)
            ->where('created_at','>', \Carbon\Carbon::now()->subDay(30)->toDateTimeString())
            ->first();
    }
}
