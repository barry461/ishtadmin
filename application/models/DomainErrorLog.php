<?php

/**
 * class DomainErrorLogModel
 * 
 * 
 * @property int $id  
 * @property string $ip IP 
 * @property string $position 位置 
 * @property string $text 收集的信息 
 * @property string $created_at  
 * @property string $city 城市 
 * @property string $scr_img 截屏地址 
 * @property int $aff aff 
 * 
 * 
 *
 * @mixin \Eloquent
 */
class DomainErrorLogModel extends BaseModel
{
    protected $table = 'domain_error_log';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'ip', 'position', 'text', 'created_at', 'city', 'scr_img', 'aff'];
    protected $guarded = 'id';
    public $timestamps = false;

    public function setScrImgAttribute($value)
    {
        parent::resetSetPathAttribute('scr_img', $value);
    }

    public function getScrImgAttribute()
    {
        $uri = $this->attributes['scr_img'] ?? '';
        return $uri ? url_image($uri) : '';
    }
}