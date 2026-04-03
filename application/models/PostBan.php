<?php

use Illuminate\Support\Collection;

/**
 * class PostBanModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property int $num 违规次数
 * @property int $created_at 创建时间
 * @property int $updated_at 创建时间
 *
 *
 *
 * @mixin \Eloquent
 */
class PostBanModel extends BaseModel
{
    const REDIS_POST_COMMENT_BAN = "post:comment:ban:%s";
    protected $table = 'post_ban';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aff',
        'num',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['ban_status'];

    public function getBanStatusAttribute()
    {
        $num = $this->attributes['num'];
        $aff = $this->attributes['aff'];
        if ($num >= 3){
            return "永久禁言";
        }else{
            $cacheKey = sprintf(self::REDIS_POST_COMMENT_BAN, $aff);
            if (\tools\RedisService::get($cacheKey)) {
                $seconds = \tools\RedisService::ttl($cacheKey);
                return "剩余禁言时间:{$seconds}秒";
            }else{
                return "禁言解除";
            }
        }
    }

    public static function setBan($aff)
    {
        $model = self::where('aff', $aff)->first();
        $cacheKey = sprintf(self::REDIS_POST_COMMENT_BAN, $aff);
        if (!$model) {
            self::create([
                'aff' => $aff,
                'num' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            //禁言一天
            \tools\RedisService::set($cacheKey, 1, 24 * 3600);
        } else {
            if ($model->num == 1) {
                //禁言3天
                \tools\RedisService::set($cacheKey, 1, 3 * 24 * 3600);
            } else {
                //永久禁言
                $member = MemberModel::findByAff($aff);
                $member->ban_post = MemberModel::BAN_POST_YES;
                $member->save();

                if ($member->auth_status == MemberModel::AUTH_STATUS_YES){
                    $creator = PostCreatorModel::findByAff($aff);
                    if ($creator){
                        $creator->ban_post = MemberModel::BAN_POST_YES;
                        $creator->save();
                    }
                }
            }
            $model->num = $model->num + 1;
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
        }
    }

    public static function verifyCommentBan($aff)
    {
        $cacheKey = sprintf(self::REDIS_POST_COMMENT_BAN, $aff);
        if (\tools\RedisService::get($cacheKey)) {
            throw new RuntimeException('评论失败，你已被封禁！等待自动解禁，恶意刷评论打广告会被永久封禁。');
        }
    }
}