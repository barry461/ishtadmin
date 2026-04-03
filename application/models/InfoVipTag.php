<?php



/**
 * class InfoVipTagModel
 *
 * @property int $id 
 * @property int $info_id 雅间资源id
 * @property int $tag_id 标签id
 * @property int $created_at 
 * @mixin \Eloquent
 */
class InfoVipTagModel extends BaseModel
{

    protected $table = "info_vip_tag";

    protected $primaryKey = 'id';

    protected $fillable = ['info_id', 'tag_id', 'created_at'];

    const UPDATED_AT = NULL;



}