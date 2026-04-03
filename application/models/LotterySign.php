<?php


/**
 * @property string $id
 * @property string $aff 用户aff
 * @property string $date 时间
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class LotterySignModel extends BaseModel
{
    protected $table = 'lottery_sign';

    protected $fillable = [
        'id',
        'aff',
        'date',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';
    public $timestamps = true;
}
