<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class InfoPicModel
 *
 * @property int $id
 * @property string $url 地址
 * @property-read  string $img_url 地址
 * @property int $info_id 资源id
 * @property int $sort 排序
 * @property int $created_at 创建时间
 * @property int $type
 * @mixin \Eloquent
 */
class InfoPicModel extends BaseModel
{

    protected $table = "info_pic";

    protected $primaryKey = 'id';

    protected $fillable = ['url', 'info_id', 'sort', 'created_at', "type"];

    protected $appends = ['img_url'];

    protected $guarded = 'id';
    const UPDATED_AT = null;

    const WATER_MARK_POSITION = ['top-left', 'top-center', 'top-right', 'center-left', 'center', 'center-right', 'bottom-left', 'bottom-center', 'bottom-right']; 
    public function getImgUrlAttribute($value)
    {
        return url_image($this->attributes['url']);
    }

    const TYPE_PHOTO = 0;
    const TYPE_SCREENSHOT = 1;

}