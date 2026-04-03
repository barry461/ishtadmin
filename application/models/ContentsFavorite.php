<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class ContentsFavoriteModel
 *
 *
 * @property int $id
 * @property int $aff 用户aff
 * @property int $cid 数据id
 * @property string $created_at 创建时间
 *
 * @property ContentsModel $contents
 * @mixin \Eloquent
 */
class ContentsFavoriteModel extends BaseModel
{
    protected $table = 'contents_favorite';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'aff', 'cid', 'created_at'];
    protected $guarded = 'id';
    public $timestamps = false;

    public static function generateID(string $aff): string
    {
        return "tb:c-favorite:" . $aff;
    }

    public function contents(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentsModel::class, 'cid', 'cid');
    }

}