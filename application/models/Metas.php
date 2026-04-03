<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class MetasModel
 *
 *
 * @property int $mid
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $description
 * @property int $count
 * @property int $order 排序
 * @property int $parent
 * @property string $sort_type
 * @property string $sort_column
 *
 *
 * @property array<mid>|\Illuminate\Database\Eloquent\Collection $relationships
 *
 * @mixin \Eloquent
 */
class MetasModel extends BaseModel
{
    protected $table = 'metas';
    protected $primaryKey = 'mid';
    protected $fillable
        = [
            'name',
            'slug',
            'type',
            'description',
            'count',
            'order',
            'parent',
            'sort_type',
            'sort_column',
        ];
    protected $guarded = 'mid';
    public $timestamps = false;
    const TYPE_TAG = 'tag';
    const TYPE_CATEGORY = 'category';
    const TYPE = [self::TYPE_TAG => 'tag', self::TYPE_CATEGORY => 'category'];

    const CK_METAS_MID = 'ck:metas:mid:%d';
    const GP_METAS_MID = 'gp:metas:mid';

    public function relationships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('mid', 'mid');
    }

    public static function getMetaByMid($mid)
    {
        return cached(sprintf(self::CK_METAS_MID, $mid))
            ->group(self::GP_METAS_MID)
            ->fetchPhp(function () use ($mid){
                return  self::find($mid);
            });
    }

    public function url(): ?ParseUrl
    {
        return new ParseUrl(url('category',['slug'=>$this->slug]));
    }

}