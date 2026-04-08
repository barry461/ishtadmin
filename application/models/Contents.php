<?php

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Capsule\Manager as DBManager;
use Illuminate\Database\Schema\Blueprint;

/**
 * class ContentsModel
 *
 * @property int $cid
 * @property string $title
 * @property string $slug
 * @property \Carbon\Carbon $created
 * @property \Carbon\Carbon $modified
 * @property string $text
 * @property int $order
 * @property int $authorId
 * @property string $template
 * @property string $type
 * @property string $status
 * @property string $password
 * @property int $commentsNum
 * @property string $allowComment 允许评论
 * @property string $allowPing 允许被引用
 * @property string $allowFeed 允许在聚合中出现
 * @property int $parent
 * @property int $is_home 是否在首页展示
 * @property int $home_top
 * @property int $is_slice 默认处于切片状态
 * @property int $app_hide app端隐藏
 * @property int $favorite_num 收藏数
 * @property int $web_show
 * @property int $view 浏览量
 * @property int $fake_view 显示浏览量
 * @property int $like_num 点赞数
 * @property string $content
 *
 * @property array<FieldsModel>|\Illuminate\Database\Eloquent\Collection $fields
 * @property ?UsersModel $author
 * @property array<CategoryRelationshipsModel>|\Illuminate\Database\Eloquent\Collection $relationships
 * @property array<CategoriesModel>|\Illuminate\Database\Eloquent\Collection $categories
 *
 * @xproperty array<RelationshipsModel>|\Illuminate\Database\Eloquent\Collection $relationships
 * @property array<MetasModel> $tags
 * @mixin \Eloquent
 */
class ContentsModel extends BaseModel
{
    protected $table = 'contents';
    protected $primaryKey = 'cid';
    protected $fillable
        = [
            'cid',
            'title',
            'slug',
            'created',
            'modified',
            'text',
            'order',
            'authorId',
            'template',
            'type',
            'status',
            'password',
            'commentsNum',
            'allowComment',
            'allowPing',
            'allowFeed',
            'parent',
            'is_home',
            'home_top',
            'is_slice',
            'app_hide',
            'favorite_num',
            'web_show',
            'view',
            'fake_view',
            'like_num',
        ];
    protected $guarded = 'cid';
    public $timestamps = false;
    protected $dateFormat = 'U';
    const UPDATED_AT = 'modified';
    const CREATED_AT = 'created';
    protected $casts = [];

    /**
     * 获取 created 属性 - 使用原生 PHP 格式化避免 Carbon 依赖
     * @param mixed $value
     * @return string|null
     */
    public function getCreatedAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return date('Y-m-d H:i:s', is_numeric($value) ? $value : strtotime($value));
    }

    /**
     * 获取 modified 属性 - 使用原生 PHP 格式化避免 Carbon 依赖
     * @param mixed $value
     * @return string|null
     */
    public function getModifiedAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return date('Y-m-d H:i:s', is_numeric($value) ? $value : strtotime($value));
    }
    const TYPE_POST = 'post';
    const TYPE_ATTACHMENT = 'attachment';
    const TYPE_PAGE = 'page';
    const TYPE_SKITS = 'skits';
    const TYPE_BIG_WENT = 'big_went';
    const TYPE
        = [
            self::TYPE_POST => '文章',
                // self::TYPE_ATTACHMENT => '附件',
            self::TYPE_PAGE => '单页',
            // self::TYPE_SKITS      => '短剧',
            // self::TYPE_BIG_WENT   => '大事件',
        ];
    const STATUS_PUBLISH = 'publish';
    const STATUS_PRIVATE = 'private';
    const STATUS_WAITING = 'waiting';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_PASSWORD = 'password';
    const STATUS_SECRET = 'secret';
    const STATUS_DRAFT = 'draft';
    const STATUS_REMOVED = 'removed';
    const IS_HOME_TICP = [
        0 => '首页不显示',
        1 => '首页显示'
    ];
    const STATUS
        = [
            self::STATUS_PUBLISH => '公开',
            self::STATUS_PRIVATE => '私密',
            self::STATUS_WAITING => '待审核',
            self::STATUS_HIDDEN => '隐藏',
            self::STATUS_PASSWORD => '密码保护',
            self::STATUS_SECRET => '秘闻',
            self::STATUS_DRAFT => '草稿',
            self::STATUS_REMOVED => '已下架',
        ];

    const APP_HIDE_NO = 0;
    const APP_HIDE_YES = 1;
    const APP_HIDE
        = [
            self::APP_HIDE_NO => 'APP显示',
            self::APP_HIDE_YES => 'APP隐藏',
        ];

    const WEB_SHOW_NO = 0;
    const WEB_SHOW_YES = 1;
    const WEB_SHOW
        = [
            self::APP_HIDE_NO => 'web显示',
            self::APP_HIDE_YES => 'web隐藏',
        ];

    const CONTENTS_RANK_VIEW = 'contents:rank:view:v2:%s';
    const FAKE_VIEW_MULTIPLE = 9.1;

    const GP_HOME_CONTENT_LIST = "gp:content:home-list";
    const CN_HOME_CONTENT_LIST = "WEB端首页列表缓存";
    const GP_HOME_CONTENT_LIST_COUNT = "gp:content:home-count";
    const CN_HOME_CONTENT_LIST_COUNT = "WEB端首页列表分页缓存";

    public function categories()
    {
        return $this->belongsToMany(CategoriesModel::class, 'category_relationships', 'cid', 'category_id');
    }
    public static function incrementFavoriteNum($id, $num = 1)
    {
        return self::where('cid', $id)->increment('favorite_num', $num);
    }

    public static function incrementLikeNum($id, $num = 1)
    {
        return self::where('cid', $id)->increment('like_num', $num);
    }

    public static function decrementFavoriteNum($id, $num = 1)
    {
        return self::where('cid', $id)->decrement('favorite_num', $num);
    }

    public static function decrementLikeNum($id, $num = 1)
    {
        return self::where('cid', $id)->decrement('like_num', $num);
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);
        $this->fillable = array_merge($this->getFillable(), array_keys($this->getMergeFields()));

        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) && $this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function fields(): HasMany
    {
        //return $this->hasMany(FieldsModel::class, 'cid', 'cid');
        return $this->hasMany(FieldsModel::class, 'cid', 'cid')
            ->whereNotIn('name', [
                    'disableDarkMask',
                    'enableFlowChat',
                    'enableMathJax',
                    'enableMermaid',
                    'TOC',
                ]);
    }

    public function author(): HasOne
    {
        return $this
            ->hasOne(UsersModel::class, 'uid', 'authorId')
            ->withDefault(function () {
                return UsersModel::makeOnce(['uid' => 0, 'screenName' => "铁名"]);
            });
    }

    public function authorValue($field = 'screenName'): ?string
    {
        if (empty($this->author)) {
            return null;
        }

        return $this->author->{$field} ?? null;
    }

    public static function queryPost()
    {
        return ContentsModel::query()
            ->where('status', ContentsModel::STATUS_PUBLISH)
            ->where('type', ContentsModel::TYPE_POST)
            ->where('created', '<', time());
    }

    /**
     * 获取发布中的文章数量
     * @return int
     */
    public static function getPublishedCount(): int
    {
        return self::query()
            ->where('status', self::STATUS_PUBLISH)
            ->where('type', self::TYPE_POST)
            ->count();
    }

    /**
     * 获取最近发布的文章列表
     * @param int $limit 返回数量,默认10条
     * @return \Illuminate\Support\Collection
     */
    public static function getRecentPublished(int $limit = 10)
    {
        return self::query()
            ->where('status', self::STATUS_PUBLISH)
            ->where('type', self::TYPE_POST)
            ->orderByDesc('created')
            ->limit($limit)
            ->get(['cid', 'title', 'created'])
            ->map(function ($item) {
                return [
                    'cid' => $item->cid,
                    'title' => $item->title,
                    'date' => date('m-d', $item->getRawOriginal('created')),
                    'url' => rtrim(options('siteUrl'), '/') . url('detail', ['id' => $item->cid]),
                ];
            });
    }

    public static function queryWebPost()
    {
        return ContentsModel::queryPost()
            ->where('app_hide', ContentsModel::APP_HIDE_NO)
            ->where('web_show', ContentsModel::WEB_SHOW_YES)
            ->where('is_slice', 1);
    }

    public static function queryWebListPost()
    {
        $prefix = Yaf_Registry::get('database')->prefix;
        $fullTable = $prefix . 'contents';
        return ContentsModel::queryPost()
            ->selectRaw("$fullTable.cid,title,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,fake_view,view,created,modified");
    }


    public static function queryPrev($cid)
    {
        return self::queryPost()
            ->where('cid', '>', $cid - 1000)
            ->where('cid', '<', $cid)
            ->orderByDesc('cid');
    }

    public static function queryNext($cid)
    {

        return self::queryPost()
            ->where('cid', '>', $cid);
    }

    public static function queryRecommend()
    {
        $ids = cached('content:recommend:ids')
            ->chinese("WEB端404推荐ID池缓存")
            ->fetchPhp(function () {
                return ContentsModel::queryWebPost()->orderByDesc('home_top')->limit(1000)->pluck('cid')->toArray();
            }, 7200);


        return cached('content:recommend:list')
            ->chinese("WEB端404推荐列表缓存")
            ->fetchPhp(function () use ($ids) {
                shuffle($ids);
                return ContentsModel::queryPost()->with(['fields'])  // 预加载 fields 关系，这样才能获取 banner 字段;
                    ->whereIn('cid', array_slice($ids, rand(0, count($ids) - 50), 8))
                    ->get();
            }, 300);
    }

    public static function queryWebListBase()
    {
        return self::query()
            ->with([
                    'author' => function ($query) {
                        /** @var HasOne $query */
                        return $query
                            ->select(
                                'uid',
                                'name',
                                'mail',
                                'url',
                                'screenName',
                                'created',
                                'activated',
                                'logged',
                                'group',
                                'authCode'
                            );
                    }
                ])
            ->with([
                    'relationships' => function (HasMany $query) {
                        $sub = MetasModel::where('type', MetasModel::TYPE_CATEGORY);
                        $query->joinSub($sub, 'tm', 'tm.mid', '=', 'relationships.mid');
                    }
                ])
            ->where('type', self::TYPE_POST)
            ->where('status', '=', self::STATUS_PUBLISH)
            ->where('web_show', self::WEB_SHOW_YES)
            ->where('is_slice', 1)
            ->where('created', '<', time())
            ->select(
                'cid',
                'title',
                'slug',
                'authorId',
                'modified',
                'type',
                'status',
                'commentsNum',
                'order',
                'template',
                'password',
                'allowComment',
                'is_home',
                'allowPing',
                'allowFeed',
                'web_show',
                'app_hide',
                'like_num',
                'fake_view',
                'parent',
                'favorite_num',
                'view',
                'app_view',
                'web_view',
                'home_top',
                'sort_by',
                'bkdg',
                'sort4',
                'dj_51',
                'adtongyong'
            );
    }


    public function relationships(): HasMany
    {
        return $this->hasMany(CategoryRelationshipsModel::class, 'cid', 'cid');
    }

    public function categoryRelationships()
    {
        return $this->hasMany(CategoryRelationshipsModel::class, 'cid', 'cid');
    }

    public function tagRelationships()
    {
        return $this->hasMany(TagRelationshipsModel::class, 'cid', 'cid');
    }

    public function fieldValue($name, $default = null)
    {
        static $fields = [];
        $cid = $this->cid ?? null;
        if (empty($cid)) {
            return $default;
        }
        if (!isset($fields[$cid])) {
            $fields[$cid] = $this->fields->keyBy('name');
        }
        $data = $fields[$cid];
        /** @var FieldsModel $field */
        $field = $data[$name] ?? null;
        if (!isset($field)) {
            return $default;
        }
        if ($field->type == FieldsModel::TYPE_STR) {
            return $field->str_value;
        } elseif ($field->type == FieldsModel::TYPE_INT) {
            return $field->int_value;
        } elseif ($field->type == FieldsModel::TYPE_FLOAT) {
            return $field->float_value;
        } else {
            return $default;
        }
    }

    public function loadFirstImage()
    {
        $content = $this->text;
        if (preg_match('/<img.*?data-src\=\"((http|https)\:\/\/[^>\"]+?\.(jpg|jpeg|bmp|webp|png))\"[^>]*>/i', $content, $matches)) {
            return $matches[1];
        }
        if (preg_match('/<img.*?src\=\"((http|https)\:\/\/[^>\"]+?\.(jpg|jpeg|bmp|webp|png))\"[^>]*>/i', $content, $matches)) {
            return $matches[1];
        }
        return false;
    }


    public function loadTagWithCategory(): ContentsModel
    {

        if (!$this->relationLoaded('categoryRelationships') || !$this->relationLoaded('tagRelationships')) {
            $this->load([
                'categoryRelationships.category',
                'tagRelationships.tag',
            ]);
        }


        $categories = collect($this->categoryRelationships)
            ->map(function ($relationship) {
                return $relationship->category;
            })
            ->filter();


        $tags = collect($this->tagRelationships)
            ->map(function ($relationship) {
                return $relationship->tag;
            })
            ->filter();


        $this->setRelation('categories', $categories);
        $this->setRelation('tags', $tags);


        $this->unsetRelation('categoryRelationships');
        $this->unsetRelation('tagRelationships');

        return $this;
    }

    public function loadMarkdown()
    {
        $html = \tools\LibMarkdown::loadMarkdown($this->text, $this->title);
        $this->setAttribute('content', $html);
        $this->makeHidden('text');
    }

    public function loadWebMarkdown()
    {
        $html = \tools\LibMarkdown::loadWebMarkdown($this->text, false, $this->title);
        $this->setAttribute('content', $html);
        $this->makeHidden('text');
    }

    public function getViewAttribute()
    {
        return $this->attributes['fake_view'] ?? ceil($this->attributes['view'] * self::FAKE_VIEW_MULTIPLE);
    }

    public static function incrByView($cid)
    {
        $key = "contents:view:key:" . $cid;
        $val = redis()->incrBy($key, 1);
        $val = intval($val);

        //        if ($val >= 1){
        //浏览数
        $contents = self::find($cid);

        if (!empty($contents)) {
            $fake_view = ceil($val * self::FAKE_VIEW_MULTIPLE);
            $contents->increment('view', $val, ['fake_view' => \DB::raw('fake_view + ' . $fake_view)]);
            if ($contents->status == self::STATUS_PUBLISH) {
                //加入排行榜
                self::addCacheData($cid, $val, $contents->getRawOriginal('created'));
            }
        }
        //清除redis
        redis()->del($key);

    }

    /**
     * @throws Exception
     */
    public static function addCacheData($cid, $increase, $created)
    {
        $increase = floatval($increase);
        $day = date('Ymd');
        $week = date('W');
        $month = date('Ym');
        $key_day = sprintf(self::CONTENTS_RANK_VIEW, $day);
        $key_week = sprintf(self::CONTENTS_RANK_VIEW, $week);
        $key_month = sprintf(self::CONTENTS_RANK_VIEW, $month);
        //日榜
        if (date('Ymd', $created) == $day) {
            redis()->zIncrBy($key_day, $increase, $cid);
            self::keyTtl('day', $key_day);
        }
        //周榜
        if (date('W', $created) == $week) {
            redis()->zIncrBy($key_week, $increase, $cid);
            self::keyTtl('week', $key_week);
        }
        //月榜
        if (date('Ym', $created) == $month) {
            redis()->zIncrBy($key_month, $increase, $cid);
            self::keyTtl('month', $key_month);
        }
    }

    /**
     * @throws RedisException
     */
    public static function keyTtl($type, $key)
    {
        switch ($type) {
            case 'day':
                if (redis()->ttl($key) == -1) {
                    redis()->expire($key, 25 * 3600);
                }
                break;
            case 'week':
                if (redis()->ttl($key) == -1) {
                    redis()->expire($key, 8 * 24 * 3600);
                }
                break;
            case 'month':
                if (redis()->ttl($key) == -1) {
                    redis()->expire($key, 32 * 24 * 3600);
                }
                break;
            default:
                break;
        }
    }

    public static function hotTags()
    {
        $day = date('Ymd');
        return cached('hot:view:tags' . $day)
            ->fetchJson(function () use ($day) {
                $key_day = sprintf(self::CONTENTS_RANK_VIEW, $day);
                $cids = redis()->zRevRange($key_day, 0, 9, ['withscores' => true]);
                $cids = array_keys($cids);
                $tags = [];
                if ($cids) {
                    $list = ContentsModel::query()
                        ->with([
                                'relationships' => function ($query) {
                                    $query->with('meta');
                                },
                            ])
                        ->selectRaw("cid")
                        ->whereIn('cid', $cids)
                        ->get()
                        ->each(function (ContentsModel $model) {
                            $model->loadTagWithCategory();
                        });
                    collect($list)->map(function ($item) use (&$tags) {
                        collect($item->tags)->map(function ($val) use (&$tags) {
                            $tags[] = [
                                'mid' => $val->mid,
                                'name' => $val->name,
                            ];
                        });
                    });
                    $tags = array_unique($tags, SORT_REGULAR);
                    if (count($tags) > 8) {
                        $tags = collect($tags)->random(8)->toArray();
                    }
                }
                return $tags;
            });
    }

    /**
     * 更新文章后更新缓存
     */

    public function updateCache($cid)
    {

        return cached($cid)->clearCached();

    }

    public function url(): ParseUrl
    {
        if ($this->type == self::TYPE_PAGE) {
            return new ParseUrl(url('slug', [$this->slug]));
        }
        return new ParseUrl(url('detail', ['id' => $this->cid]));
    }

    public function biaoqingTitle(): string
    {
        return \Mirages\BiaoqingParser::parse($this->title);
    }


    public function date($format)
    {
        $dt = new DateTime($this->created);
        if ($format == 'c') {
            $format = DateTime::ATOM;
        }

        return $dt->format($format);
    }

    //    public function setSulg()
//    {//设置slug,通过随机数7位且不与文章id重复生成,例如 1221333.html
//        $slug = $this->slug;
//        if (empty($slug)) {
//            $slug = $this->random_str(7);
//            $exists = self::where('slug', $slug)->first();
//            if ($exists) {
//                $slug = $this->random_str(7);
//            }
//            $this->slug = $slug;
//            // $this->save();
//        }
//
//        return $this->slug;
//    }
    static function getSlug()
    {
        return self::random_str(7);
    }

    static function random_str($length = 10)
    {
        $characters
            = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function getCategoryNames(): array
    {
        if (!$this->relationLoaded('categoryRelationships')) {
            $this->load('categoryRelationships.category');
        }

        return collect($this->categoryRelationships)
            ->pluck('category.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function getTDK(): array
    {
        $title = $this->fieldValue('seo_title', $this->title);
        $keywords = $this->fieldValue('seo_keywords', collect($this->tags)->pluck('name')->join(','));
        $description = strip_tags($this->fieldValue('seo_description', $this->content));
        if (empty($keywords)) {
            $keywords = options()->keywords;
        }
        if (empty($description)) {
            $description = options()->description;
        }
        return [$title, $description, $keywords];
    }

    public function getCategoryNamesString(string $delimiter = '、', bool $withLinks = false): string
    {
        if (!$this->relationLoaded('categoryRelationships')) {
            $this->load('categoryRelationships.category');
        }

        $names = collect($this->categoryRelationships)
            ->map(function ($rel) use ($withLinks) {
                $category = $rel->category ?? null;

                if (!$category || empty($category->name)) {
                    return null;
                }

                if ($withLinks && !empty($category->slug)) {
                    $url = url('category', [$category->slug]);
                    return '<a href="' . e($url) . '">' . e($category->name) . '</a>';
                }

                return e($category->name);
            })
            ->filter()
            ->toArray();

        return implode($delimiter, $names);
    }




    public function categoryStr()
    {
        throw new \RuntimeException(__METHOD__ . '方法没有实现');
        return category($this);
    }

    public function getColumns()
    {
        static $columnsCache = [];
        $conn = $this->getConnectionName();     // 可能为 null = 默认连接
        $table = $this->getTable();
        $cacheKey = ($conn ?: 'default') . ':' . $table;

        if (!isset($columnsCache[$cacheKey])) {
            $schema = $this->getConnection()->getSchemaBuilder();
            $columnsCache[$cacheKey] = $schema->getColumnListing($table);
        }

        return $columnsCache[$cacheKey];
    }
    /**
     * @param $feild
     * @param $comment
     * @param $default
     * @return false|\Illuminate\Database\Schema\Builder|null
     */
    public function upSortFeild($feild, $comment = '', $default = '')
    {
        $feild = str_replace(["'", '"'], '', $feild);
        $comment = str_replace(["'", '"'], '', $comment);

        $cols = $this->getColumns();
        if (in_array($feild, $cols)) {
            return true;
        }

        return DBManager::Schema()->table($this->table, function (Blueprint $table) use ($feild, $default, $comment) {
            $table->integer($feild)->default($default)->comment($comment);
            $table->index($feild);
        });
    }

    /**
     * @param $feild
     * @return \Illuminate\Database\Schema\Builder|null
     */
    public function downSortFeild($feild)
    {
        if ($feild == 'home_top')
            return false;

        $cols = $this->getColumns();
        if (!in_array($feild, $cols)) {
            return true;
        }

        $cate = CategoriesModel::where('sort_column', $feild)->first();
        if ($cate) {
            test_assert(false, '该字段不能直接删除,请前往分类详情页面修改排序字段');
            return false;
        }

        return DBManager::Schema()->table($this->table, function (Blueprint $table) use ($feild) {
            try {
                $table->dropIndexIfExists([$feild]);
            } catch (\Exception $e) {
                trigger_log("Error-删除排序字段错误：索引 - " . $e->getMessage());
            }
            try {
                $table->dropColumn($feild);
            } catch (\Exception $e) {
                trigger_log("Error-删除排序字段错误：字段 - " . $e->getMessage());
            }
        });
    }

    /**
     * @return array
     */
    public function getMergeFields()
    {
        $return = [];
        
        $enabledSorts = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)
            ->get(['slug', 'name'])
            ->pluck('name', 'slug')
            ->toArray();

      
        if (empty($enabledSorts)) {
            
            error_log('getMergeFields: 没有找到开启的自定义排序字段 (status=1)', 3, APP_PATH . '/storage/logs/log.log');
            return $return;
        }
        
        $cols = $this->getColumns();
                
        foreach ($enabledSorts as $slug => $name) {
            if (in_array($slug, $cols, true)) {
                $return[$slug] = $name;
            } 
        }
        

        return $return;
    }

    /**
     * 获取后台列表分页数据 (优雅的扁平化参数)
     * @param array $params 扁平化参数: cid, status, is_home, category_id, hot_search, keyword, author, date_from, date_to
     * @param int $limit
     * @param int $offset
     * @param string $type
     * @return array [$list, $total]
     */
    public static function getPageList(array $params, int $limit, int $offset, string $type = self::TYPE_POST): array
    {
        $query = self::query()->where('type', '=', $type);

        // 唯一 cid 精确搜索（优先级最高）
        if (!empty($params['cid'])) {
            $cid = (int) $params['cid'];
            if ($cid > 0) {
                $query->where('cid', $cid);
            }
        }

        // 状态筛选
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // 首页显示
        if (isset($params['is_home']) && $params['is_home'] !== '') {
            $query->where('is_home', (int) $params['is_home']);
        }

        // 分类筛选
        if (!empty($params['category_id'])) {
            $categoryId = $params['category_id'];
            if ($categoryId === 'no_category') {
                $query->whereDoesntHave('categories');
            } else {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('id', $categoryId);
                });
            }
        }

        // 热搜筛选：查询 fields 表中的 hotSearch 字段
        if (isset($params['hot_search']) && $params['hot_search'] !== '') {
            $hotSearch = (int) $params['hot_search'];
            $query->whereHas('fields', function ($q) use ($hotSearch) {
                $q->where('name', 'hotSearch')->where('str_value', (string) $hotSearch);
            });
        }

        // 老后台 custormsort_id 语义：筛选指定自定义排序字段且值大于 0
        if (!empty($params['custormsort_id'])) {
            $customSortField = (string) $params['custormsort_id'];
            $enabledSortFields = array_keys((new self())->getMergeFields());
            if (in_array($customSortField, $enabledSortFields, true)) {
                $query->where($customSortField, '>', 0);
            }
        }

        // 关键词搜索 (标题)
        if (!empty($params['keyword'])) {
            $query->where('title', 'like', '%' . $params['keyword'] . '%');
        }

        // 作者搜索
        if (!empty($params['author'])) {
            $query->whereHas('author', function ($q) use ($params) {
                $q->where('screenName', 'like', '%' . $params['author'] . '%');
            });
        }

        // 日期范围
        if (!empty($params['date_from'])) {
            $query->where('created', '>=', strtotime($params['date_from']));
        }
        if (!empty($params['date_to'])) {
            $query->where('created', '<=', strtotime($params['date_to'] . ' 23:59:59'));
        }

        // 排序：公开文章默认按发布时间倒序，其余默认按 cid 倒序
        $hasCustomOrder = !empty($params['order_by']);
        $orderBy = $hasCustomOrder
            ? $params['order_by']
            : (($params['status'] ?? '') === self::STATUS_PUBLISH ? 'created' : 'cid');
        $orderDir = $params['order_dir'] ?? 'desc';
        // 确保排序方向只能是 asc 或 desc
        if (!in_array(strtolower($orderDir), ['asc', 'desc'])) {
            $orderDir = 'desc';
        } else {
            $orderDir = strtolower($orderDir);
        }
        $query->orderBy($orderBy, $orderDir);
        if ($orderBy !== 'cid') {
            $query->orderByDesc('cid');
        }

        $total = $query->count();
        $list = $query->limit($limit)
            ->offset($offset)
            ->with(['author', 'categories', 'fields'])
            ->get()
            ->map(function ($item) {
                return $item->processListItem();
            });

        return [$list, $total];
    }

    /**
     * 处理列表项数据
     */
    public function processListItem()
    {
        $this->loadTagWithCategory();

        // 分类
        $category_names = [];
        if ($this->categories && $this->categories->count() > 0) {
            $category_names = array_column($this->categories->toArray(), 'name');
        }
        $this->category_str = !empty($category_names) ? implode(',', $category_names) : '无分类';
        $this->category_ids = $this->categories ? $this->categories->pluck('id')->toArray() : [];

        // 作者
        $this->author_name = $this->author ? $this->author->screenName : '未知作者';

        // 字段
        $this->hotSearch = 0;
        $this->banner = '';
        foreach ($this->fields as $field) {
            if ($field->name == 'banner') {
                $this->banner = url_image(parse_url($field->str_value, PHP_URL_PATH));
            }
            if ($field->name == 'hotSearch') {
                $this->hotSearch = $field->str_value;
            }
        }

        // 格式化时间 - 使用getRawOriginal避免触发Eloquent日期转换(避免Carbon依赖)
        $createdTs = $this->getRawOriginal('created');
        $modifiedTs = $this->getRawOriginal('modified');
        $this->created_str = $createdTs ? date('Y-m-d H:i:s', $createdTs) : '';
        $this->modified_str = $modifiedTs ? date('Y-m-d H:i:s', $modifiedTs) : '';

        // 状态和类型文字
        $this->status_str = self::STATUS[$this->status] ?? '未知状态';
        $this->type_str = self::TYPE[$this->type] ?? '未知类型';
        $this->home_str = self::IS_HOME_TICP[$this->is_home] ?? '未知状态';

        // 预览URL
        $this->preview_url = rtrim(options('siteUrl'), '/') . (string) $this->url();

        // 自定义排序字段
        $sortFields = $this->getMergeFields();
        $sortFieldsData = [];
        foreach ($sortFields as $slug => $name) {
            $value = $this->getRawOriginal($slug) ?? null;
            $sortFieldsData[$slug] = [
                'name' => $name,
                'value' => $value,
            ];
        }
        $this->sort_fields = $sortFieldsData;

        return $this;
    }

    /**
     * 获取单条详情
     */
    public static function getOneDetail(int $id)
    {
        $post = self::where('cid', $id)->with(['fields', 'author'])->first();
        if (!$post) {
            return null;
        }

        $post->loadTagWithCategory();

        // 处理自定义字段
        $post->hotSearch = 0;
        $post->ads_field = 0;
        foreach ($post->fields as $field) {
            if ($field->name == 'hotSearch') {
                $post->hotSearch = $field->str_value;
            }
            if ($field->name == 'ads_field') {
                $post->ads_field = $field->str_value;
            }
        }

        // 处理内容
        $post->content_processed = preg_replace('/\s*<!--markdown-->\s*/', '', $post->text);

        return $post;
    }

    /**
     * 批量删除
     */
    public static function deleteByCids(array $cids)
    {
        return self::whereIn('cid', $cids)->delete();
    }

    /**
     * 批量更新状态
     */
    public static function batchUpdateStatus(array $cids, string $status)
    {
        return self::whereIn('cid', $cids)->update(['status' => $status, 'modified' => time()]);
    }

    /**
     * 批量更新首页显示
     */
    public static function batchUpdateHome(array $cids, int $isHome)
    {
        return self::whereIn('cid', $cids)->update(['is_home' => $isHome]);
    }

    /**
     * 更新置顶权重
     */
    public static function updateHomeTop(int $cid, int $homeTop)
    {
        return self::where('cid', $cid)->update(['home_top' => $homeTop]);
    }

    /**
     * 切换 APP 隐藏
     */
    public static function toggleAppHide(int $cid)
    {
        $content = self::findOrFail($cid);
        $content->app_hide = ($content->app_hide == self::APP_HIDE_NO) ? self::APP_HIDE_YES : self::APP_HIDE_NO;
        return $content->save();
    }

    /**
     * 切换 Web 显示
     */
    public static function toggleWebShow(int $cid)
    {
        $content = self::findOrFail($cid);
        $content->web_show = ($content->web_show == self::WEB_SHOW_NO) ? self::WEB_SHOW_YES : self::WEB_SHOW_NO;
        return $content->save();
    }

    /**
     * 设置内容类型
     */
    public static function setArticleType(int $cid, string $type, int $sid)
    {
        $content = self::findOrFail($cid);
        if ($type == self::TYPE_SKITS) {
            FieldsModel::updateOrCreate(
                ['cid' => $cid, 'name' => 'skits'],
                ['type' => 'int', 'int_value' => $sid, 'str_value' => '0', 'float_value' => 0]
            );
        } elseif ($type == self::TYPE_BIG_WENT) {
            FieldsModel::updateOrCreate(
                ['cid' => $cid, 'name' => 'bigEvent'],
                ['type' => 'int', 'int_value' => $sid, 'str_value' => '0', 'float_value' => 0]
            );
        }
        $content->type = $type;
        if (in_array($type, [self::TYPE_SKITS, self::TYPE_BIG_WENT])) {
            $content->web_show = self::WEB_SHOW_NO;
            $content->app_hide = self::APP_HIDE_NO;
        }
        return $content->save();
    }

    /**
     * 特殊编辑
     */
    public static function updateSpecial(int $cid, array $data)
    {
        $content = self::findOrFail($cid);
        if (isset($data['created'])) {
            $content->created = is_numeric($data['created']) ? $data['created'] : strtotime($data['created']);
        }
        if (isset($data['title'])) {
            $content->title = $data['title'];
        }
        if (!$content->save()) {
            return false;
        }

        $service = new \service\ContentsService();
        if (isset($data['category_ids'])) {
            $service->handleCategories($content, $data['category_ids']);
        }
        if (isset($data['tags'])) {
            $service->handleTags($content, $data['tags']);
        }

        if (isset($data['banner'])) {
            FieldsModel::updateOrCreate(
                ['cid' => $cid, 'name' => 'banner'],
                ['type' => 'str', 'str_value' => $data['banner'], 'int_value' => 0, 'float_value' => 0]
            );
        }
        if (isset($data['hotSearch'])) {
            FieldsModel::updateOrCreate(
                ['cid' => $cid, 'name' => 'hotSearch'],
                ['type' => 'str', 'str_value' => (string) $data['hotSearch'], 'int_value' => (int) $data['hotSearch'], 'float_value' => 0]
            );
        }
        return true;
    }
}
