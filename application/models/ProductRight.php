<?php


/**
 * class ProductRightModel
 *
 * @property int $id
 * @property string $name
 * @property string $img
 * @property string $desc
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class ProductRightModel extends BaseModel
{

    protected $table = "product_right";

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'img', 'desc', 'created_at', 'updated_at'];

    protected $hidden = ['created_at' , 'updated_at'];

    protected $guarded = 'id';

    public function getImgAttribute():string
    {
        return  url_image($this->attributes['img'] ?? '');
    }

    public function setImgAttribute($value)
    {
        $this->resetSetPathAttribute('img' , $value);
    }

}
