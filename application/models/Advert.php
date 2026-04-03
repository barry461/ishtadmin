<?php


/**
 * class AdvertModel
 *
 * @property int $id
 * @property string $title
 * @property string $link
 * @property string $img_url
 * @property string $position
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 * @property int $sort
 * @mixin \Eloquent
 */
class AdvertModel extends BaseModel
{
    protected $table = 'advert';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'id',
            'title',
            'link',
            'img_url',
            'position',
            'created_at',
            'updated_at',
            'status',
            'sort',
            'ads_code'
        ];
    protected $guarded = 'id';
    public $timestamps = false;

    // 禁用日期转换,避免Carbon依赖
    protected $casts = [];
    protected $dates = [];

    // 覆盖BaseModel的日期处理方法,避免Carbon依赖
    public function getCreatedAtAttribute($value): string
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $timestamp = is_numeric($value) ? $value : strtotime($value);
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function getUpdatedAtAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    const POSITION_HOME_POP = 'home_pop_ads';
    const POSITION_APP_CENTER_POP = 'app_center_pop_ads';
    const POSITION_HORIZONTAL_ADS = 'horizontal_ads';
    const POSITION_ARTICLE_TOP_BTN = 'article_top_btn_ads';
    const POSITION_ARTICLE_BOTTOM_BTN = 'article_bottom_btn_ads';
    const POSITION_ARTICLE_TOP = 'article_top_ads';
    const POSITION_ARTICLE_BOTTOM = 'article_bottom_ads';
    const POSITION_ARTICLE_TOP_TEXT = 'article_top_txt';
    const POSITION_ARTICLE_BOTTOM_TEXT = 'article_bottom_txt';
    const POSITION_WEBSITE_BOTTOM = 'website_bottom_ads';
    const STATUS_ON = 1;
    const STATUS_OFF = 0;

    const POSITION_OPT = [
        self::POSITION_HOME_POP => '首页弹窗广告',
        self::POSITION_APP_CENTER_POP => '应用中心弹窗',
        self::POSITION_HORIZONTAL_ADS => '首页底部横幅图片广告',
        self::POSITION_ARTICLE_TOP_BTN => '文章详情顶部图标广告',
        self::POSITION_ARTICLE_BOTTOM_BTN => '文章详情底部图标广告',
        self::POSITION_ARTICLE_TOP => '文章详情顶部横幅广告',
        self::POSITION_ARTICLE_BOTTOM => '文章详情底部横幅广告',
        self::POSITION_ARTICLE_TOP_TEXT => '文章详情顶部文字按钮',
        self::POSITION_ARTICLE_BOTTOM_TEXT => '文章详情底部文字按钮',
        self::POSITION_WEBSITE_BOTTOM => '网站底部浮动横幅广告',
    ];

    const STATUS_OPT = [
        self::STATUS_ON => '启用',
        self::STATUS_OFF => '禁用',
    ];

    const ADVERT_CATEGORY = [
        ['id' => 1, 'name' => '热门应用'],
        ['id' => 2, 'name' => '最新上架'],
        ['id' => 3, 'name' => '必备精品'],
    ];

    const CK_ADVERT_LIST = 'advert:list';
    const GP_ADVERT_LIST = 'gp:advert-list';
    const CN_ADVERT_LIST = '广告全站列表';

    public static function getAdvertCategoryOptions(): array
    {
        $options = [];
        foreach (self::ADVERT_CATEGORY as $cat) {
            $options[$cat['id']] = $cat['name'];
        }
        return $options;
    }

    protected static function getAll()
    {
        static $data = null;
        if ($data === null) {
            $key = 'advert:list';
            $data = cached(self::CK_ADVERT_LIST)
                ->group(self::GP_ADVERT_LIST)
                ->chinese(self::CN_ADVERT_LIST)
                ->fetchPhp(function () {
                    return self::query()
                        ->where('status', 1)
                        ->orderByDesc('sort')
                        ->orderByDesc('id')
                        ->get();
                });
        }
        return $data;
    }
    // component/article_img_top.php

    public static function getAdsByPosition($pos = '', $replace = false)
    {
        $data = self::getAll();
        $data = collect($data)->groupBy('position');
        return $data[$pos] ?? [];
    }

    public static function getByPos($pos): \Illuminate\Support\Collection
    {
        $data = self::getAll();
        $data = collect($data)->groupBy('position');
        return $data[$pos];
    }

    public function getImgUrlAttribute()
    {
        $url = $this->attributes['img_url'] ?? '';
        return url_image($url);
    }

    public function url(): ?ParseUrl
    {
        return new ParseUrl($this->link);
    }

    /**
     * 分页获取广告列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [$list, $total]
     */
    public static function getPageList(array $params, int $limit, int $offset): array
    {
        $query = self::query()->orderByDesc('sort')->orderByDesc('id');

        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }

        // 位置筛选
        if (!empty($params['position'])) {
            $query->where('position', $params['position']);
        }

        // 标题搜索
        if (!empty($params['keyword'])) {
            $query->where('title', 'like', '%' . $params['keyword'] . '%');
        }

        $total = $query->count();

        $list = $query->offset($offset)->limit($limit)->get()->map(function ($item) {
            $data = $item->getAttributes();
            
            // 转换状态和位置
            $data['status_str'] = self::STATUS_OPT[$item->getRawOriginal('status')] ?? '未知状态';
            $data['position_str'] = self::POSITION_OPT[$item->position] ?? '未知位置';
            $data['img_url_full'] = url_image($item->getRawOriginal('img_url'));

            // 处理创建时间，先试试访问器，不行就用原始值
            $createdAt = $item->created_at;
            if (empty($createdAt)) {
                $createdAtRaw = $item->getRawOriginal('created_at') ?? null;
                if (!empty($createdAtRaw) && $createdAtRaw != '0000-00-00 00:00:00' && $createdAtRaw != 0) {
                    $timestamp = is_numeric($createdAtRaw) ? (int)$createdAtRaw : strtotime($createdAtRaw);
                    if ($timestamp && $timestamp > 0) {
                        $createdAt = date('Y-m-d H:i:s', $timestamp);
                    }
                }
            }
            $data['created_at'] = $createdAt ?: '';

            // 查询分类
            $cid = AdsCategoryModel::where('aid', $item->id)->value('cid') ?? 0;
            $categoryName = '未分类';
            foreach (self::ADVERT_CATEGORY as $cat) {
                if ($cat['id'] == $cid) {
                    $categoryName = $cat['name'];
                    break;
                }
            }
            $data['category'] = $cid;
            $data['category_name'] = $categoryName;
            
            // 保留其他字段
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $data['link'] = $item->link;
            $data['img_url'] = $item->img_url;
            $data['position'] = $item->position;
            $data['status'] = $item->status;
            $data['sort'] = $item->sort;

            return $data;
        })->all();

        return [$list, $total];
    }

    /**
     * 保存广告
     * @param array $data 广告数据
     * @return int 广告ID
     */
    public static function saveAdvert(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $category = (int) ($data['category'] ?? 0);

        $advertData = [
            'title' => $data['title'] ?? '',
            'link' => $data['link'] ?? '',
            'img_url' => $data['img_url'] ?? '',
            'position' => $data['position'] ?? '',
            'status' => (int) ($data['status'] ?? 0),
            'sort' => (int) ($data['sort'] ?? 0),
        ];

        if ($id) {
            self::where('id', $id)->update($advertData);
        } else {
            // 新建的时候记录创建时间
            $advertData['created_at'] = time();
            $id = self::insertGetId($advertData);
        }

        // 处理分类
        if ($category) {
            AdsCategoryModel::updateOrInsert(['aid' => $id], ['cid' => $category]);
        } else {
            AdsCategoryModel::where('aid', $id)->delete();
        }

        return $id;
    }

    /**
     * 批量替换链接
     * @param string $from 原链接
     * @param string $to 新链接
     * @return int 影响行数
     */
    public static function batchReplaceLink(string $from, string $to): int
    {
        return self::where('link', $from)->update(['link' => $to]);
    }
}