<?php

use Carbon\Carbon;

/**
 * class EpisodeModel
 *
 *
 * @property string $id
 * @property int $p_id 剧集ID
 * @property string $title 标题
 * @property string $thumb 封面
 * @property int $thumb_width
 * @property int $thumb_height
 * @property string $play_url 播放地址
 * @property int $coins 价格
 * @property int $buy_num 购买次数
 * @property int $buy_coins 购买总金额
 * @property int $order 排序
 * @property string $free_time 免费时间
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property int $is_pre
 * @property string $release_time 发布时间
 *
 *
 *
 * @mixin \Eloquent
 */
class EpisodeModel extends BaseModel
{
    protected $table = 'episode';
    protected $fillable = [
        'id',
        'p_id',
        'title',
        'thumb',
        'thumb_width',
        'thumb_height',
        'play_url',
        'coins',
        'buy_num',
        'buy_coins',
        'order',
        'free_time',
        'status',
        'created_at',
        'updated_at',
        'is_pre',
        'release_time',
    ];
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $appends = [
        'is_pay'
    ];

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS_TIPS = [
        self::STATUS_NO => '下架',
        self::STATUS_YES => '上架',
    ];

    const PRE_NO = 0;
    const PRE_YES = 1;
    const PRE_TIPS = [
        self::PRE_NO => '否',
        self::PRE_YES => '是',
    ];

    const CK_EPISODE_LIST = 'ck:episode:list:%d:%s';
    const GP_EPISODE_LIST = 'gp:episode:list';
    const CN_EPISODE_LIST = '短剧列表';

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb']);
    }

    public function setThumbAttribute($value)
    {
        parent::resetSetPathAttribute('thumb', $value);
    }

    public function setPlayUrlAttribute($value){
        parent::resetSetPathAttribute('play_url', $value);
    }

    public function getPlayUrlAttribute(): string
    {
        $uri = $this->attributes['play_url'] ?? '';
        $uri = trim($uri,'/');
        if (substr($uri, -4) == '.mp4' && APP_MODULE == 'staff') {
            return config('mp4.visit') . $uri;
        }
        $uri = ltrim($uri, '/');
        return url_video_sns('/' . $uri,2);
    }

    public function getIsPayAttribute(): int
    {
        static $ary = null;
        static $ery = null;
        if (APP_MODULE == 'staff') {
            return 1;
        }
        if (isset($this->attributes['is_pay'])) {
            return $this->attributes['is_pay'];
        }
        $aff = self::$watchUser ? self::$watchUser->aff : 0;
        if (empty($aff)) {
            return 0;
        }
        //判断免费时间和金币设置
        if ($this->attributes['coins'] == 0 || Carbon::now()->gte($this->attributes['free_time'])){
            return 1;
        }
        //判断是否有会员权限
        $hasPrivilege = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
            ProductPrivilegeModel::PRIVILEGE_TYPE_VIEW);
        if ($hasPrivilege){
            return 1;
        }

        //判断合集购买没有
        $rk = SkitsPayModel::generateSkitsRk($aff);
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (in_array($this->attributes['p_id'], $ary)) {
            return 1;
        }
        //判断单集购买没有
        $erk = SkitsPayModel::generateEpisodeRk($aff);
        if ($ery === null) {
            $ery = redis()->sMembers($erk);
        }
        if (empty($ery) || !is_array($ery) || !in_array($this->attributes['id'], $ery)) {
            return 0;
        }
        return 1;
    }

    public static function queryBase(...$args)
    {
        return parent::queryBase(...$args)->where('status', self::STATUS_YES);
    }

    public static function list($sid, $version){
        $key = sprintf(self::CK_EPISODE_LIST, $sid, $version);
        return cached($key)
            ->group(self::GP_EPISODE_LIST)
            ->chinese(self::CN_EPISODE_LIST)
            ->fetchPhp(function () use ($sid, $version){
                return self::queryBase()
                    ->selectRaw('id, title, thumb, thumb_width, thumb_height, play_url, coins, p_id, free_time, is_pre')
                    ->when(version_compare($version, '2.5.0', '<='),function ($q){
                        $q->where('is_pre', self::PRE_NO);
                    })
                    ->where('p_id', $sid)
                    ->orderBy('order')
                    ->get();
            });
    }
}