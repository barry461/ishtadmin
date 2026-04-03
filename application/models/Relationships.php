<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class RelationshipsModel
 *
 * @property int $cid 文章id
 * @property int $mid metas表的的id
 *
 * @property MetasModel $meta
 *
 * @mixin \Eloquent
 */
class RelationshipsModel extends BaseModel
{

    protected $table = "relationships";

    protected $primaryKey = 'cid';

    public $incrementing = false;

    protected $fillable = ['mid', 'cid'];

    protected $guarded = 'cid';

    public $timestamps = false;


    const CK_RELATIONSHIPS_MID = 'ck:relationships:mid:%d';
    const GP_RELATIONSHIPS_MID = 'gp:relationships:mid';

    public function content(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentsModel::class, 'cid', 'cid');
    }

    public function meta(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MetasModel::class, 'mid', 'mid');
    }

    public static function getCidArrByMid($mid){
        return cached(sprintf(self::CK_RELATIONSHIPS_MID, $mid))
            ->group(self::GP_RELATIONSHIPS_MID)
            ->fetchJson(function () use ($mid){
                return RelationshipsModel::where('mid', $mid)->get()->pluck('cid')->toArray();
            });
    }
}
