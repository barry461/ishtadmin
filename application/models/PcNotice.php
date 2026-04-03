<?php

/**
 * class PCNoticeModel
 *
 * @property int $aff 如：0 所有人
 * @property string $content 内容
 * @property int $created_at
 * @property int $width
 * @property int $height
 * @property int $id
 * @property string $img_url 图片地址
 * @property int $status 1显示 0 关闭
 * @property string $title 标题
 * @property string $type 公告类型 url 跳转链接  route 路由
 * @property int $visible_type 可见类型
 * @property int $sort 排序
 * @property int $pos
 * @property string $router
 * @property string $url 跳转地址
 * @property string $start_at
 * @property string $end_at
 * @property string $clicked 点击量
 * @mixin \Eloquent
 */
class PcNoticeModel extends BaseModel
{
    protected $table = 'pc_notice';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'aff',
        'visible_type',
        'sort',
        'url',
        'title',
        'router',
        'width',
        'pos',
        'height',
        'content',
        'status',
        'created_at',
        'img_url',
        'start_at',
        'end_at',
        'clicked'
    ];
    const UPDATED_AT = null;
    protected $appends = ['url_str', 'report_id', 'report_type'];

    const REDIS_KEY_NOTICE_LIST = 'pc:notice:list:';
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
    const STATUS = [
        self::STATUS_FAIL    => '关闭',
        self::STATUS_SUCCESS => '开启'
    ];

    const TYPE_URL = 'url';
    const TYPE_ROUTE = 'router';
    const TYPE = [
        self::TYPE_URL   => '跳转链接',
        self::TYPE_ROUTE => '路由',
    ];

    const POS_HOME = 1;
    const POS_DETAIL = 2;
    const POS = [
        self::POS_HOME   => '首页',
        self::POS_DETAIL => '文章详情页',
    ];

    const VISIBLE_TYPE_ALL = 0;
    const VISIBLE_TYPE_NEWCOMER = 1;
    const VISIBLE_TYPE_VIP = 2;
    const VISIBLE_TYPE_NOTVIP = 3;
    const VISIBLE_TYPE_NEWCOMER_VIP = 4;
    const VISIBLE_TYPE_NEWCOMER_NOTVIP = 5;
    const VISIBLE_TYPE = [
        self::VISIBLE_TYPE_ALL             => '全部',
        self::VISIBLE_TYPE_NEWCOMER        => '新用户',
        self::VISIBLE_TYPE_VIP             => '会员',
        self::VISIBLE_TYPE_NOTVIP          => '不是会员',
        self::VISIBLE_TYPE_NEWCOMER_VIP    => '新用户&&是会员',
        self::VISIBLE_TYPE_NEWCOMER_NOTVIP => '新用户&&不是会员',
    ];


    public function setImgUrlAttribute($value)
    {
        $this->resetSetPathAttribute('img_url', $value);
    }

    public function getImgUrlAttribute(): string
    {
        return url_image($this->attributes['img_url'] ?? null);
    }

    public function getUrlStrAttribute(): string
    {
        if ($this->attributes['type'] == self::TYPE_URL) {
            return $this->attributes['url'] ?? '';
        }
        $value = $this->attributes['url'] ?? '';
        $router = $this->attributes['router'] ?? '';
        return FlutterRouterModel::parseRouterUri($value, $router);
    }

    public function getReportIdAttribute(): int
    {
        return (int)($this->attributes['id'] ?? 0);
    }

    public function getReportTypeAttribute(): int
    {
        return DayClickModel::TYPE_NOTICE;
    }

    public static function incrNum($id, $num = 1)
    {
        return self::where('id', $id)->increment('clicked', $num);
    }
}