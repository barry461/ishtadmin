<?php

/**
 * class AdsCategoryModel
 *
 * @property int $aid
 * @property string $aid 广告id
 * @property int $cid 分类名称
 *
 * @date 2020-01-08 17:09:02
 *
 * @mixin \Eloquent
 */
class AdsContentsModel extends BaseModel
{
    protected $table = "ads_contents";

    protected $primaryKey = 'ads_code';

    protected $fillable = ['ads_code', 'cid'];
    public $timestamps = false;

    public static function getAdsAll( $code = '' )
    {
        static $data = null;
        if(is_null($data)){
            $list = self::query()->select(['ads_code', 'cid'])->get();
            if( $list ){
                $list = $list->toArray();
                foreach ($list as $row){
                    $data[$row['ads_code']] = $row['cid'];
                }
            }
        }

        return empty($code) ? $data : (isset($data[$code])?$data[$code]:'');
    }

}
