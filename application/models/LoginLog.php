<?php

/**
 * class LoginLogModel
 *
 * @property int $id 
 * @property int $aff 
 * @property string $oauth_type 
 * @property string $oauth_id 
 * @property string $created_at 
 * @mixin \Eloquent
 */
class LoginLogModel extends BaseModel
{
    protected $table = "login_log";

    protected $primaryKey = 'id';

    protected $fillable = ['aff', 'oauth_type', 'oauth_id', 'created_at'];
	
	const OAUTH_TYPE_IOS = 'ios';
    const OAUTH_TYPE_ANDROID = 'android';
    const OAUTH_TYPE_ = '';
    const UPDATED_AT = null;
    static function log($aff,$oauth_id,$oauth_type){
        self::create(['aff'=>$aff,
        'oauth_type'=>$oauth_type,
        'oauth_id'=>$oauth_id]);
    }
}