<?php

/**
 * class MemberPostScoreModel
 *
 *
 * @property int $id
 * @property int $aff 评分aff
 * @property int $to_aff 被评分人aff
 * @property int $score 分数
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class MemberPostScoreModel extends BaseModel
{
    protected $table = 'member_post_score';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'to_aff',
        'score',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    public static function findByAff($aff, $to_aff){
        return self::where('aff', $aff)->where('to_aff', $to_aff)->first();
    }

    public static function sumScore($aff){
        return self::where('to_aff', $aff)->sum('score');
    }

    public static function countAff($aff){
        return self::where('to_aff', $aff)->count('aff');
    }
}