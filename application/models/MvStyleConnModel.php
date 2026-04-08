<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvStyleConnModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $style_id
 * @property int $mv_id
 * @property string $created_at
 * @property string $update_at
 */
class MvStyleConnModel extends BaseModel
{
    protected $table = 'sq_mv_style_conn';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'style_id',
        'mv_id',
        'created_at',
        'update_at',
    ];

    // 关联视频
    public function mv()
    {
        return $this->belongsTo(MvModel::class, 'mv_id', 'id');
    }

    // 关联主题
    public function style()
    {
        return $this->belongsTo(MvStyleModel::class, 'style_id', 'id');
    }
}
