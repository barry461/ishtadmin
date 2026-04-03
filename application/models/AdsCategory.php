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
class AdsCategoryModel extends BaseModel
{
    protected $table = "ads_categories";

    protected $primaryKey = 'aid';

    protected $fillable = ['aid', 'cid'];
    public $timestamps = false;
    const CK_ADSCATEGORY_LIST = 'advert:adscategory';
    const GP_ADSCATEGORY_LIST = 'gp:advert-adscategory';
    const CN_ADSCATEGORY_LIST = '广告分类关系列表';

    public static function getAll( $cid = '' )
    {
        static $data = null;
        if ($data === null) {
            $data = cached(self::CK_ADSCATEGORY_LIST)
                ->group(self::GP_ADSCATEGORY_LIST)
                ->chinese(self::CN_ADSCATEGORY_LIST)
                ->fetchPhp(function () {
                    $data = self::query()
                        ->get();
                    $return = [];
                    foreach ($data as $row){
                        $return[$row->cid][] = $row->aid;
                    }
                    return $return;
                }, 86400);
        }
        return empty($cid) ? $data : ($data[$cid]??[]);
    }

    /**
     * 按分类获取
     * @param $btn_bottom_ads
     * @return array
     */
    public static function getAdsByCate( $btn_bottom_ads )
    {
        $adsByCategory = [];
        $ad_cates = self::getAll();
        $btn1 = $btn2 = $btn3 = [];
        foreach ($btn_bottom_ads as $row){
            if(!empty($ad_cates[1]) && in_array($row['id'], $ad_cates[1])){
                $btn1[] = $row;
            }elseif(!empty($ad_cates[2]) && in_array($row['id'], $ad_cates[2])){
                $btn2[] = $row;
            }elseif(!empty($ad_cates[3]) && in_array($row['id'], $ad_cates[3])){
                $btn3[] = $row;
            }
        }

        foreach (AdvertModel::ADVERT_CATEGORY as $cat) {
            $cid = $cat['id'];
            $cname = $cat['name'];

            $btn = "btn".$cid;
            $adsByCategory[$cid] = [
                'name' => $cname,
                'ads'  => $$btn,
            ];
        }
        return $adsByCategory;
    }
}
