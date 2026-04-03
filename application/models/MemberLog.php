<?php

/**
 * class MemberLogModel
 *
 *
 * @property string $app_version app版本号
 * @property string $created_at
 * @property int $id
 * @property string $lastactivity
 * @property string $lastip
 * @property string $oauth_type 设备类型
 * @property int $oltime 在线小时数
 * @property int $pageviews
 * @property string $uuid
 *
 * @mixin \Eloquent
 */
class MemberLogModel extends BaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'members_log';
    protected $fillable
        = [
            'app_version',
            'created_at',
            'id',
            'lastactivity',
            'lastip',
            'oauth_type',
            'oltime',
            'pageviews',
            'uuid',
        ];
    const UPDATED_AT = null;

    public static function updateSession(MemberModel $member)
    {
        $session = null;
        if ($member->relationLoaded('session')) {
            $session = $member->session;
        }
        if (empty($session)) {
            $when = strtotime($member->regdate) > strtotime('-1 hours');
            $session = MemberModel::where('uuid', $member->uuid)
                ->when($when, function ($query) { $query->useWritePdo(); })
                ->first();
            if (empty($session)) {
                $session = new MemberLogModel();
                $session->uuid = $member->uuid;
                $session->oauth_type = $member->oauth_type;
            }
        }
        if ($session->exists){
            $session->timestamps = false;
        }
        $session->lastip = $member->lastip;
        $session->lastactivity = $member->lastactivity;
        $session->app_version = $member->app_version;
        $session->save();
    }

    public static function initSession(MemberModel $member)
    {
        $session = new MemberLogModel();
        $session->uuid = $member->uuid;
        $session->oauth_type = $member->oauth_type;
        $session->app_version = $member->app_version;
        $session->lastactivity = $member->lastactivity;
        $session->lastip = $member->lastip;
        $session->save();
    }
}