<?php

/**
 * class AreaCnModel
 *
 * @property int $id ID
 * @property string $areaname 栏目名
 * @property int $parentid 父栏目
 * @property string $shortname
 * @property string $lng
 * @property string $lat
 * @property int $level 1.省 2.市 3.区 4.镇
 * @property string $position
 * @property int $sort 排序
 *
 * @mixin \Eloquent
 */
class AreaCnModel extends BaseModel
{

    protected $table = "area_cn";

    protected $primaryKey = 'id';

    protected $fillable = ['areaname', 'parentid', 'shortname', 'lng', 'lat', 'level', 'position', 'sort'];

    protected $guarded = 'id';

    const REDIS_KEY_USER_AREA = 'user:area:';

    public static function getProvinceWithCityName(int $cityCode)
    {
        return self::hashCached('cityZname' , $cityCode , function () use($cityCode){
            $cityInfo = self::where(['id' => $cityCode])->first(['shortname', 'parentid']);
            if (empty($cityInfo)){
                return '火星';
            }
            $province = self::find($cityInfo->parentid);
            if (empty($province)){
                return $cityInfo->shortname;
            }
            return  $province->shortname .'-' . $cityInfo->shortname;
        });
    }


}