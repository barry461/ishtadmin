<?php

/**
 * @property int $id
 * @property int $pid 话题类型ID
 * @property string $thumb 封面
 * @property string $bg_thumb 背景
 * @property string $name 名称
 * @property int $follow_num 关注人数
 * @property int $view_num 浏览人数
 * @property int $post_num  帖子数量
 * @property int $status 是否显示 0不显示 1显示
 * @property int $sort 排序 越大越前
 * @property int $is_hot 是否热门
 * @property string $intro 简介
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @mixin \Eloquent
 */
class PostTopicModel extends BaseModel
{
    protected $table = 'post_topic';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'pid',
        'thumb',
        'bg_thumb',
        'name',
        'follow_num',
        'view_num',
        'post_num',
        'status',
        'sort',
        'is_hot',
        'intro',
        'created_at',
        'updated_at',
    ];
    protected $appends = ['is_follow'];

    const POST_TOPIC_LIST_KEY = 'post:topic:list:%s:%s:%s';
    const POST_TOPIC_DETAIL_KEY = 'post:topic:detail:%s';
    const POST_TOPIC_NEW_TAB_LIST_KEY = 'post:topic:new:tab:list';
    const POST_TOPIC_ALL_KEY = 'post:topic:all';

    const POST_TOPIC_FOLLOW_TAB_LIST_KEY = 'post:topic:follow:tab:list';
    const POST_TOPIC_ALL_GROUP_KEY = 'post_topic_list';

    // 推荐
    const CK_TOPIC_RECOMMEND_TAB_LIST = 'post:topic:recommend:tab:list';
    const GP_TOPIC_RECOMMEND_TAB_LIST = 'post:topic:recommend:tab:list';
    const CN_TOPIC_RECOMMEND_TAB_LIST = '推荐话题列表';

    //分类话题列表
    const CK_CATEGORY_TOPIC_LIST = 'ck:category:topic:list:%d';
    const GP_CATEGORY_TOPIC_LIST = 'gp:category:topic:list';
    const CN_CATEGORY_TOPIC_LIST = '分类话题列表';

    const STATUS_HIDE = 0;
    const STATUS_NORMAL = 1;
    const STATUS_TIPS = [
        self::STATUS_HIDE => '屏蔽',
        self::STATUS_NORMAL => '正常'
    ];

    const HOT_NO = 0;
    const HOT_OK = 1;
    const HOT_TIPS = [
        self::HOT_NO => '否',
        self::HOT_OK => '是'
    ];

    public function setThumbAttribute($value)
    {
        parent::resetSetPathAttribute('thumb', $value);
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function setBgThumbAttribute($value)
    {
        parent::resetSetPathAttribute('bg_thumb', $value);
    }

    public function getBgThumbAttribute(): string
    {
        return url_image($this->attributes['bg_thumb'] ?? '');
    }

    public function getIsFollowAttribute(): int
    {
        return $this->attributes['is_follow'] ?? 0;
    }

    public static function clearCache()
    {
        cached('')->clearGroup(self::POST_TOPIC_ALL_GROUP_KEY);
    }

    // 获取分页话题数据
    public static function listAllTopics()
    {
        $cacheKey = sprintf(self::POST_TOPIC_ALL_KEY);
        $topics = cached($cacheKey)
            ->group(self::POST_TOPIC_ALL_GROUP_KEY)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_NORMAL)
                    ->offset(0)
                    ->limit(15)
                    ->orderByDesc('sort')
                    ->orderByDesc('sort')
                    ->get();
            });
        foreach ($topics as $topic) {
            $topic->makeHidden('is_follow');
        }
        return $topics;
    }

    // 获取分页话题数据
    public static function listTopics($cateId, $offset, $limit)
    {
        $cacheKey = sprintf(self::POST_TOPIC_LIST_KEY, $cateId, $offset, $limit);
        $topics = cached($cacheKey)
            ->group(self::POST_TOPIC_ALL_GROUP_KEY)
            ->fetchPhp(function () use ($cateId, $offset, $limit) {
                return self::where('status', self::STATUS_NORMAL)
                    ->where('pid', $cateId)
                    ->orderByDesc('sort')
                    ->offset($offset)
                    ->limit($limit)
                    ->orderByDesc('sort')
                    ->get();
            });

        return $topics;
    }

    // 获取话题详情
    public static function getTopicById($topicId)
    {
        $cacheKey = sprintf(self::POST_TOPIC_DETAIL_KEY, $topicId);
        return cached($cacheKey)
            ->group(self::POST_TOPIC_ALL_GROUP_KEY)
            ->fetchPhp(function () use ($topicId) {
                return self::where('id', $topicId)
                    ->where('status', self::STATUS_NORMAL)
                    ->first();
            });
    }

    public static function listNewTabTopics()
    {
        return cached(self::POST_TOPIC_NEW_TAB_LIST_KEY)
            ->group(self::POST_TOPIC_ALL_GROUP_KEY)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_NORMAL)
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            });
    }

    public static function listByRecommend()
    {
        return cached(self::CK_TOPIC_RECOMMEND_TAB_LIST)
            ->group(self::GP_TOPIC_RECOMMEND_TAB_LIST)
            ->chinese(self::CN_TOPIC_RECOMMEND_TAB_LIST)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_NORMAL)
                    ->where('is_hot', self::HOT_OK)
                    ->orderByDesc('sort')
                    ->get();
            });
    }

    public static function listFollowTabTopics()
    {
        return cached(self::POST_TOPIC_FOLLOW_TAB_LIST_KEY)
            ->group(self::POST_TOPIC_ALL_GROUP_KEY)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_NORMAL)
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            });
    }

    public static function incrByViewNum($id, $num){
        $topic = self::find($id);
        if ($topic){
            $topic->increment('view_num', $num);
        }
    }

    // 获取分页话题数据
    public static function listTopicsByCategory($cateId)
    {
        $cacheKey = sprintf(self::CK_CATEGORY_TOPIC_LIST, $cateId);
        return cached($cacheKey)
            ->group(self::GP_CATEGORY_TOPIC_LIST)
            ->chinese(self::CN_CATEGORY_TOPIC_LIST)
            ->fetchPhp(function () use ($cateId) {
                return self::where('status', self::STATUS_NORMAL)
                    ->where('pid', $cateId)
                    ->orderByDesc('is_hot')
                    ->orderByDesc('sort')
                    ->get();
            });
    }
}
