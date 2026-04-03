<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class ContentsLikeModel
 *
 *
 * @property int $id
 * @property int $aff 用户aff
 * @property int $cid 数据id
 * @property string $created_at 创建时间
 *
 *
 *
 * @mixin \Eloquent
 */
class ContentsLikeModel extends Model
{
    protected $table = 'contents_like';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'aff',
        'cid',
        'created_at'
    ];
    protected $guarded = 'id';
    public $timestamps = false;

    public static function generateID(string $aff): string
    {
        return "tb:c-like:" . $aff;
    }

    public function contents(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentsModel::class, 'cid', 'cid');
    }
}