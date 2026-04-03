<?php



/**
 * class AbroadCityModel
 *
 * @property string $country 
 * @property int $id 
 * @property string $name 
 * @mixin \Eloquent
 */
class AbroadCityModel extends BaseModel
{

    protected $table = "abroad_city";

    protected $primaryKey = 'id';

    protected $fillable = ['country', 'name'];

    protected $appends = ['cityCode'];

    const OFFSET = 1000000000;
    
    public function getCityCodeAttribute($value)
    {
        return $this->attributes['id']+self::OFFSET;
    }
    static function getCityByCode($cityCode){
        return self::where('id',$cityCode-self::OFFSET)->first();
    }



}