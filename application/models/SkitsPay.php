<?php

/**
 * class SkitsPayModel
 *
 *
 * @property int $id
 * @property int $type 类型 1单集 2合集
 * @property int $cid 单集ID/合集ID
 * @property int $aff Aff
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class SkitsPayModel extends BaseModel
{
    protected $table = 'skits_pay';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'type',
        'cid',
        'aff',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const TYPE_EPISODE = 1;
    const TYPE_SKITS = 2;
    const TYPE_TIPS = [
        self::TYPE_EPISODE  => '单集',
        self::TYPE_SKITS    => '合集',
    ];

    public static function generateSkitsRk(string $aff): string
    {
        return 'rk-skits-aff:' . $aff;
    }

    public static function generateEpisodeRk(string $aff): string
    {
        return 'rk-episode-aff:' . $aff;
    }
}