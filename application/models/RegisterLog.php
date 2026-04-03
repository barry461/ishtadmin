<?php

/**
 * class RegisterLogModel
 *
 * @property int $id 
 * @property int $uid 
 * @property int $aff
 * @property int $old_uid
 * @property string $oauth_type 
 * @property string $phone 
 * @property string $oauth_id 
 * @property string $new_uuid 用户唯一id
 * @property string $created_at 
 * @mixin \Eloquent
 */
class RegisterLogModel extends BaseModel
{
    protected $table = "register_log";

    protected $primaryKey = 'id';

    protected $fillable = ['uid', 'old_uid', 'oauth_type', 'phone', 'oauth_id', 'aff', 'created_at', 'updated_at'];

}