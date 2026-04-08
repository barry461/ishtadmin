<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvActorConnModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $mv_id
 * @property int $actor_id
 * @property string $update_at
 * @property string $created_at
 */
class MvActorConnModel extends BaseModel
{
    protected $table = 'sq_mv_actor_conn';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'mv_id',
        'actor_id',
        'update_at',
        'created_at',
    ];

    // 关联视频
    public function mv()
    {
        return $this->belongsTo(MvModel::class, 'mv_id', 'id');
    }

    // 关联演员
    public function actor()
    {
        return $this->belongsTo(ActorModel::class, 'actor_id', 'id');
    }
}
