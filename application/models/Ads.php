<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property string $title 广告标题
 * @property string $description 广告词
 * @property string $img_url 图片地址
 * @property string $url_config 广告跳转地址/QQ号/微信号
 * @property string $position 广告位
 * @property string $android_down_url
 * @property string $ios_down_url
 * @property string $router
 * @property int $type 广告类型 0：下载链接 1：跳转qq 2:跳转微信
 * @property int $product_type 产品类型 0: 内部 1:外部
 * @property int $status 0-禁用，1-启用
 * @property int $oauth_type 广告设备类型 0所有 1iOS 2 android
 * @property string $mv_m3u8 视频m3u8
 * @property string $channel 渠道
 * @property string $created_at 创建时间
 * @property string $start_at
 * @property string $end_at
 * @property string $sort 越大越前
 * @property string $clicked 点击量
 * @mixin \Eloquent
 */
class AdsModel extends BaseModel
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
    const STATUS = [
        self::STATUS_SUCCESS => '启用',
        self::STATUS_FAIL    => '禁用',
    ];
    const DEVICE_ALL = 0;
    const DEVICE_IOS = 1;
    const DEVICE_ANDROID = 2;
    const DEVICE_TYPE = [
        self::DEVICE_ALL     => '所有',
        self::DEVICE_IOS     => 'ios',
        self::DEVICE_ANDROID => 'android',
    ];

    const REDIS_ADS_KEY = 'ads:positions_';

    const POSITION_SCREEN = 1; // 启动页广告
    const POSITION_DETAIL = 201; // 详情-底部banner
    const POSITION_REDBAG = 202; // 详情
    const POSITION_QUN = 203; // 详情
    const POSITION_USER_POS_1 = 251; // 详情
    const POSITION_USER_POS_2 = 252; // 详情
    const POSITION_USER_POS_3 = 253; // 详情
    const POSITION_EMAIL_SUB = 254; // 短剧详情邮箱订阅
    const POS_SEARCH_BANNER = 901; // 搜索页面banner
    const POS_CONTENT_LIST = 1002; // 社区广告
    const POS_APP_CENTER_BANNER = 1004; // 视频夹杂广告
    const POS_WEB_HOME_POP = 2001; // 网站首页弹窗广告
    const POS_WEB_HOME_MAC_POP = 2002; // mac-网站首页弹窗广告
    const POS_WEB_HOME_WIN_POP = 2003; // win-网站首页弹窗广告
    const POS_POST_RECOMMEND = 3000; //社区推荐页广告
    const POS_POST_DETAIL = 3001; //帖子详情也banner
    const POS_SKITS_TOP_BANNER = 4000; //短剧顶部banner
    const POS_SKITS_MID_AD = 4001; //短剧中部广告
    const POS_INFO_BOTTOM = 5000; //包养列表顶部banner
    const POS_INFO_DETAIL = 5001; //包养详情中间banner

    const POSITION
        = [ // 广告位置
            self::POS_APP_CENTER_BANNER => '应用中心广告',
            self::POSITION_SCREEN       => '启动页广告',
            self::POSITION_DETAIL       => '内容详情',
            self::POSITION_REDBAG       => '内容页红包广告',
            self::POSITION_EMAIL_SUB    => '短剧页红包广告',
            self::POSITION_QUN          => '内容页群广告',
            self::POSITION_USER_POS_1   => '用户中心-领取奖励',
            self::POSITION_USER_POS_2   => '用户中心-求瓜',
            self::POSITION_USER_POS_3   => '用户中心-投稿',
            self::POS_SEARCH_BANNER     => '搜索页BANNER',
            self::POS_CONTENT_LIST      => '主页内容列表BANNER',
            self::POS_WEB_HOME_POP      => '网站首页弹窗广告',
            self::POS_WEB_HOME_MAC_POP  => 'MAC-网站首页弹窗广告',
            self::POS_WEB_HOME_WIN_POP  => 'WIN-网站首页弹窗广告',
            self::POS_POST_RECOMMEND    => '社区推荐页广告',
            self::POS_POST_DETAIL       => '帖子详情BANNER',
            self::POS_SKITS_TOP_BANNER  => '内容详情顶部banner',
            self::POS_SKITS_MID_AD      => '短剧中部广告',
            self::POS_INFO_BOTTOM       => '包养顶部banner',
            self::POS_INFO_DETAIL       => '包养详情中间banner',
        ];

    // 尺寸提示
    const SIZE_TIPS
        = [
            self::POS_APP_CENTER_BANNER => '700 X 300',
            self::POSITION_SCREEN       => '750 X 1334',
            self::POSITION_DETAIL       => '700 X 300',
            self::POSITION_REDBAG       => '122 X 122',
            self::POSITION_EMAIL_SUB    => '122 X 122',
            self::POSITION_QUN          => '122 X 122',
            self::POSITION_USER_POS_1   => '350 X 55',
            self::POSITION_USER_POS_2   => '175 X 55',
            self::POSITION_USER_POS_3   => '175 X 55',
            self::POS_SEARCH_BANNER     => '700 X 300',
            self::POS_CONTENT_LIST      => '700 X 300',
        ];

    //  展示用户群体
    const SHOW_USER = [
        0 => '全部用户',
        1 => '注册时间48小时内用户',
        2 => '注册时间48小时后用户'
    ];

    // 广告类型
    const ADS_TYPE = [
        1 => '外部连接',
        2 => '路由跳转',
        3 => '邮箱订阅',
    ];

    const PRODUCT_TYPE_1 = 0;
    const PRODUCT_TYPE_2 = 1;
    const PRODUCT_TYPE_3 = 2;
    const PRODUCT_TYPE_TIPS = [
        self::PRODUCT_TYPE_1 => '内部产品',
        self::PRODUCT_TYPE_2 => '外部产品',
        self::PRODUCT_TYPE_3 => '其他'
    ];

    const ADS_TYPE_DOWNLOAD = 5;

    protected $table = 'ads';

    protected $fillable = [
        'id',
        'title',
        'description',
        'img_url',
        'url_config',
        'position',
        'android_down_url',
        'ios_down_url',
        'router',
        'type',
        'product_type',
        'status',
        'oauth_type',
        'mv_m3u8',
        'channel',
        'created_at',
        'start_at',
        'end_at',
        'sort',
        'clicked',
    ];

    protected $appends = [
        'url_str',
        'link_url',
        'url',
        'resource_url',
        'redirect_type',
        'report_id',
        'report_type'
    ];

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    const TYPE = [
        self::TYPE_IN  => '内部跳转',
        self::TYPE_OUT => '外部跳转'
    ];
    const UPDATED_AT = null;

    public static function listPos($pos, $limit = 15)
    {
        $version = $_POST['version'];
        $key = 'ad:list:' . $pos . '-' . $limit. ':' . $version;;
        return cached($key)
            ->chinese('广告列表')
            ->fetchPhp(function () use ($pos, $limit, $version) {
                return self::where('position', $pos)
                    ->where('status', self::STATUS_SUCCESS)
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->when(version_compare($version, '2.3.0', '<='), function ($q){
                        $q->whereIn('type', [1, 2]);
                    })
                    ->limit($limit)
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            });
    }

    public static function onePos($pos)
    {
        $version = $_POST['version'];
        $key = 'ad:list:' . $pos . ':' . $version;
        return cached($key)
            ->chinese('广告列表')
            ->fetchPhp(function () use ($pos, $version) {
                return self::where('position', $pos)
                    ->where('status', self::STATUS_SUCCESS)
                    ->where('start_at', '<=', \Carbon\Carbon::now())
                    ->where('end_at', '>=', \Carbon\Carbon::now())
                    ->when(version_compare($version, '2.3.0', '<='), function ($q){
                        $q->whereIn('type', [1, 2]);
                    })
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->first();
            });
    }

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($ads) {
            redis()->del(self::REDIS_ADS_KEY . $ads->position);
        });
    }

    public static function queryBase(...$args)
    {
        return parent::queryBase(...$args)->where('status', self::STATUS_SUCCESS);
    }

    public function getImgUrlAttribute(): string
    {
        return url_image($this->attributes['img_url'] ?? '');
    }

    public function setImgUrlAttribute($value)
    {
        parent::resetSetPathAttribute('img_url', $value);
    }

    public function getResourceUrlAttribute(): string
    {
        return $this->getImgUrlAttribute();
    }

    public function getRedirectTypeAttribute(): int
    {
        switch ($this->attributes['type']) {
            case 1:
                return BannerModel::TYPE_OUT;
            default:
                return BannerModel::TYPE_IN;
        }
    }

    public function getUrlAttribute(): string
    {
        return $this->getUrlStrAttribute();
    }

    public function getLinkUrlAttribute(): string
    {
        return $this->getUrlStrAttribute();
    }

    public function getUrlStrAttribute(): string
    {
        if ($this->getRedirectTypeAttribute() == BannerModel::TYPE_OUT) {
            return $this->attributes['url_config'] ?? '';
        }
        $value = $this->attributes['url_config'] ?? '';
        $router = $this->attributes['router'] ?? '';
        return FlutterRouterModel::parseRouterUri($value, $router);
    }


    public function getPositionStrAttribute()
    {
        return $this->resolveConstantValue(self::POSITION, 'position');
    }

    public function getTypeStrAttribute()
    {
        return $this->resolveConstantValue(self::ADS_TYPE, 'type');
    }

    public function getReportIdAttribute(): int
    {
        return (int)($this->attributes['id'] ?? 0);
    }

    public function getReportTypeAttribute(): int
    {
        return DayClickModel::TYPE_ADS;
    }

    public static function incrNum($id, $num = 1)
    {
        return self::where('id', $id)->increment('clicked', $num);
    }
}