<?php

/**
 * class PostModel
 *
 *
 * @property string $aff 用户AFF
 * @property int $category 帖子类型 1图片 2视频 3图文
 * @property string $cityname 定位城市
 * @property int $coins 解锁金币
 * @property int $comment_num 评论数量
 * @property string $content 内容
 * @property string $content_word 帖子文字内容
 * @property string $created_at 创建时间
 * @property int $favorite_num 收藏数
 * @property int $id
 * @property string $ipstr 用户ip
 * @property int $is_best 置精 0否 1是
 * @property int $is_deleted 用户删除标识 0否 1是
 * @property int $is_finished 资源是否处理完成 0否1是
 * @property int $like_num 点赞数量
 * @property int $photo_num 图片数
 * @property string $refresh_at 刷新时间
 * @property string $refuse_reason 拒绝通过的原因
 * @property int $reward_amount 打赏金币
 * @property int $reward_num 打赏次数
 * @property int $set_top 置顶 官方使用
 * @property int $sort 排序 越大越前
 * @property int $status 0:待审核 1:审核中 2.审核通过 3.未通过 4.被举报
 * @property string $title 标题
 * @property int $topic_id 话题ID
 * @property string $updated_at 更新时间
 * @property int $video_num 视频数
 * @property int $view_num 浏览数量
 * @property int $admin_id 审核管理员ID
 * @property int $is_subscribe 是否订阅贴
 * @property int $hot_sort 热门排序
 *
 * @property-read int $is_fans
 * @property ?MemberModel $user
 * @property ?ManagerModel $manager
 * @property ?PostTopicModel $topic
 * @property ?PostClubsModel $clubs
 * @property array<PostMediaModel>|\Illuminate\Database\Eloquent\Collection $medias
 *
 * @mixin \Eloquent
 */
class PostModel extends BaseModel
{
    protected $table = 'post';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aff',
        'category',
        'cityname',
        'coins',
        'comment_num',
        'content',
        'content_word',
        'created_at',
        'favorite_num',
        'ipstr',
        'is_best',
        'is_deleted',
        'is_finished',
        'like_num',
        'photo_num',
        'refresh_at',
        'refuse_reason',
        'reward_amount',
        'reward_num',
        'set_top',
        'sort',
        'status',
        'title',
        'topic_id',
        'updated_at',
        'video_num',
        'view_num',
        'admin_id',
        'is_subscribe',
        'hot_sort',
    ];
    const POST_TOPIC_LIST_KEY = 'post:topic:post:list:%s:%s:%s:%s';
    const POST_NEW_LIST_KEY = 'post:new:list:%s:%s';
    const POST_RECOMMEND_LIST_KEY = 'post:recommend:list:%s:%s:%s';
    const POST_SEARCH_LIST_KEY = 'post:search:list:%s:%s:%s';
    const POST_SEARCH_COUNT_KEY = 'post:search:count:%s';
    const POST_PEER_AFF_LIST_KEY = 'post:peer:aff:%s:%s:%s';
    // 对方的aff:offset:limit
    const POST_DETAIL_KEY = 'post:detail:%s';
    // 第一页单独GROUP
    const POST_FIRST_PAGE_GROUP_KEY = 'post_first_page';
    // GROUP
    const POST_TOPIC_LIST_GROUP_KEY = 'post_topic_post_list';
    const POST_NEW_LIST_GROUP_KEY = 'post_new_list';
    const POST_RECOMMEND_LIST_GROUP_KEY = 'post_recommend_list';
    const POST_SEARCH_LIST_GROUP_KEY = 'post_search_list';
    const POST_PEER_AFF_LIST_GROUP_KEY = 'post_peer_aff';
    const POST_DETAIL_GROUP_KEY = 'post_detail';


    // 推荐
    const CK_POST_RECOMMEND_LIST = 'ck:post:recommend:list:%s:%s:%s';
    const GP_POST_RECOMMEND_LIST = 'gp:post:recommend:list';
    const CN_POST_RECOMMEND_LIST = '推荐帖子列表';

    // 分类
    const CK_POST_CATEGORY_LIST = 'ck:post:category:list:%s:%s:%s:%s';
    const GP_POST_CATEGORY_LIST = 'gp:post:category:list';
    const CN_POST_CATEGORY_LIST = '分类帖子列表';

    // 他人中心帖子列表
    const CK_POST_PEER_LIST = 'ck:post:peer:list:%s:%s:%s:%s';
    const GP_POST_PEER_LIST = 'gp:post:peer:list';
    const CN_POST_PEER_LIST = '个人中心帖子列表';

    const TYPE_IMG = 1;
    const TYPE_VIDEO = 2;
    const TYPE_TXT = 3;
    const TYPE_TIPS = [
        self::TYPE_IMG => '图片',
        self::TYPE_VIDEO => '视频',
        self::TYPE_TXT => '文字'
    ];
    const TYPE_TIPS_PAR = [
        'pic' => self::TYPE_IMG,
        'video' => self::TYPE_VIDEO,
        'txt' => self::TYPE_TXT
    ];
    const BEST_NO = 0;
    const BEST_OK = 1;
    const BEST_TIPS = [
        self::BEST_NO => '未置精',
        self::BEST_OK => '置精'
    ];
    const DELETED_NO = 0;
    const DELETED_OK = 1;
    const DELETED_TIPS = [
        self::DELETED_NO => '未删除',
        self::DELETED_OK => '已删除'
    ];
    const FINISH_NO = 0;
    const FINISH_OK = 1;
    const FINISH_TIPS = [
        self::FINISH_NO => '未完成',
        self::FINISH_OK => '已完成'
    ];
    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_UNPASS = 2;
    const STATUS_TIPS = [
        self::STATUS_WAIT => '待审核',
        self::STATUS_PASS => '通过',
        self::STATUS_UNPASS => '未通过'
    ];
    const STATUS_TIPS_PARA = [
        'wait' => self::STATUS_WAIT,
        'pass' => self::STATUS_PASS,
        'unpass' => self::STATUS_UNPASS
    ];

    const SUBSCRIBE_NO = 0;
    const SUBSCRIBE_YES = 1;
    const SUBSCRIBE_TIPS = [
        self::SUBSCRIBE_NO => '否',
        self::SUBSCRIBE_YES => '是',
    ];

    //特殊符号
    const SPECIAL_SYMBOLS_LIST = [
        '~' => 'x15x-cg-x16x',
    ];

    const POST_VIEW_MULTIPLE = 5.1;
    const POST_LIKE_MULTIPLE = 21;

    protected $appends = ['is_fans'];

    public function user()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff')->withDefault([
            'uid' => 0,
            'aff' => 0,
            'vip_level' => 0,
            'nickname' => '该账号已经注销',
            'post_club_month' => 0,
            'post_club_quarter' => 0,
            'post_club_year' => 0,
        ]);
    }

    public function member()
    {
        return $this->user();
    }

    public function topic()
    {
        return $this->hasOne(PostTopicModel::class, 'id', 'topic_id');
    }

    public function clubs()
    {
        return $this->hasOne(PostClubsModel::class, 'aff', 'aff');
    }

    public function medias()
    {
        return $this->hasMany(PostMediaModel::class, 'pid', 'id');
    }

    public function manager()
    {
        return $this->hasOne(ManagerModel::class, 'uid', 'admin_id');
    }

    public function getViewNumAttribute()
    {
        return ceil($this->attributes['view_num'] * self::POST_VIEW_MULTIPLE);
    }

    public function getLikeNumAttribute()
    {
        return ceil($this->attributes['like_num'] * self::POST_LIKE_MULTIPLE);
    }

    public static function getPostById($aff, $id): ?PostModel
    {
        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $favorites = \UserFavoritesLogModel::favoritePostIds($aff);
        $cacheKey = sprintf(self::POST_DETAIL_KEY, $id);
        /** @var ?PostModel $post */
        $post = cached($cacheKey)
            ->group(self::POST_DETAIL_GROUP_KEY)
            ->fetchPhp(function () use ($id) {
                return \PostModel::with([
                    'user',
                    'topic' => function ($q) {
                        $q->where('status', PostTopicModel::STATUS_NORMAL);
                    },
                    'medias' => function ($q) {
                        $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST);
                    }
                ])->where('id', $id)
                    ->where('status', self::STATUS_PASS)
                    ->where('is_deleted', self::DELETED_NO)
                    ->where('is_finished', \PostModel::FINISH_OK)
                    ->first();
        });
        if (empty($post)) {
            return null;
        }
        $post->is_like = in_array($post->id, $likes) ? 1 : 0;
        $post->is_favorite = in_array($post->id, $favorites) ? 1 : 0;
        return $post;
    }

    public static function getPostContentById($id)
    {
        return self::where('id', $id)->pluck('content')->first();
    }

    // 话题帖子
    public static function listTopicPosts($cate, $topicId, $aff, $offset, $limit)
    {
//        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $cacheKey = sprintf(\PostModel::POST_TOPIC_LIST_KEY, $cate, $topicId, $offset, $limit);
        $group = $offset == 0 ? self::POST_FIRST_PAGE_GROUP_KEY : self::POST_TOPIC_LIST_GROUP_KEY;
        $posts = cached($cacheKey)
            ->group($group)
            ->fetchPhp(function () use ($cate, $topicId, $offset, $limit) {
                return \PostModel::with([
                    'clubs' => function ($q) {
                        $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
                    },
                    'user',
                    'topic' => function ($q) {
                        $q->where('status', PostTopicModel::STATUS_NORMAL);
                    },
                    'medias' => function ($q) {
                        $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                    }
                ])->where('status', \PostModel::STATUS_PASS)
                    ->where('is_deleted', \PostModel::DELETED_NO)
                    ->where('topic_id', $topicId)->where('is_finished', \PostModel::FINISH_OK)
                    ->when(!in_array($cate, ['new','hot']), function ($q) use ($cate) {
                        $q->where('category', $cate);
                    })
                    ->when($cate == 'new', function ($q) {
                        $q->orderByDesc('set_top');
                    })
                    ->when($cate == 'hot', function ($q) {
                        $q->orderByDesc('hot_sort');
                    })
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
        });

        foreach ($posts as $post) {
//            $post->is_like = in_array($post->id, $likes) ? 1 : 0;
            $post->is_like = 0;
        }

        return $posts;
    }

    // 推荐帖子
    public static function listRecommendTopicPosts($cateSec, $aff, $offset, $limit)
    {
//        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $cacheKey = sprintf(self::POST_RECOMMEND_LIST_KEY, $cateSec, $offset, $limit);
        $group = $offset == 0 ? self::POST_FIRST_PAGE_GROUP_KEY : self::POST_RECOMMEND_LIST_GROUP_KEY;
        $posts = cached($cacheKey)->group($group)->fetchPhp(function () use ($cateSec, $offset, $limit) {
            return \PostModel::with([
                'user',
                'clubs' => function ($q) {
                    $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
                },
                'topic' => function ($q) {
                    $q->where('status', PostTopicModel::STATUS_NORMAL);
                },
                'medias' => function ($q) {
                    $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                }
            ])->when($cateSec != 'new', function ($q) use ($cateSec) {
                $q->where('category', $cateSec);
            })->where('status', self::STATUS_PASS)
                ->where('is_deleted', self::DELETED_NO)
                ->where('is_finished', \PostModel::FINISH_OK)
                ->orderByDesc('set_top')
                ->when($cateSec != 'new', function ($q) {
                    $q->orderByDesc('sort');
                })->orderByDesc('id')
                ->offset($offset)
                ->limit($limit)
                ->get();
        });
        foreach ($posts as $post) {
//            $post->is_like = in_array($post->id, $likes) ? 1 : 0;
            $post->is_like = 0;
        }

        return $posts;
    }

    // 订阅的帖子 无需缓存
    public static function listClubPosts($aff, $offset, $limit)
    {
        $clubUserAffs = PostClubMembersModel::listClubUserAffs($aff);
        $posts = self::with([
            'user',
            'topic' => function ($q) {
                $q->where('status', PostTopicModel::STATUS_NORMAL);
            },
            'medias' => function ($q) {
                $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
            }
        ])->where('status', self::STATUS_PASS)
            ->where('is_deleted', self::DELETED_NO)
            ->where(function ($q) use ($clubUserAffs) {
                if ($clubUserAffs) {
                    return $q->whereIn('aff', $clubUserAffs);
                }

                return $q->whereIn('aff', []);
            })->where('is_finished', \PostModel::FINISH_OK)
            ->orderByDesc('set_top')
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get();
        foreach ($posts as $post) {
            $post->is_like = 0;
        }

        return $posts;
    }

    // 搜索
    public static function listPostBySearch($word, $aff, $offset, $limit)
    {
//        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $cacheKey = sprintf(\PostModel::POST_SEARCH_LIST_KEY, md5($word), $offset, $limit);
        $group = $offset == 0 ? self::POST_FIRST_PAGE_GROUP_KEY : self::POST_SEARCH_LIST_GROUP_KEY;
        $posts = cached($cacheKey)->group($group)->fetchPhp(function () use ($word, $limit, $offset) {
            return \PostModel::with([
                'user',
                'clubs' => function ($q) {
                    $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
                },
                'topic' => function ($q) {
                    $q->where('status', PostTopicModel::STATUS_NORMAL);
                },
                'medias' => function ($q) {
                    $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                }
            ])->where('status', \PostModel::STATUS_PASS)
                ->where('is_deleted', \PostModel::DELETED_NO)
                ->where('is_finished', \PostModel::FINISH_OK)
                ->where('title', 'like', '%' . $word . '%')
                ->orderByDesc('set_top')
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->offset($offset)
                ->limit($limit)
                ->get();
        });
        foreach ($posts as $post) {
//            $post->is_like = in_array($post->id, $likes) ? 1 : 0;
            $post->is_like = 0;
        }

        if ($posts->count()) {
            jobs([SearchWordModel::class, 'createSearchRecord'], [$word, $aff]);
        }
        redis()->zIncrBy(\SearchWordModel::SEARCH_TOPLIST_POST_KEY, 1, $word);

        return $posts;
    }

    public static function getSearchCount($word)
    {
        $cacheKey = sprintf(\PostModel::POST_SEARCH_COUNT_KEY, md5($word));
        return cached($cacheKey)->fetchPhp(function () use ($word) {
            return \PostModel::query()->where('status', \PostModel::STATUS_PASS)
                ->where('is_deleted', \PostModel::DELETED_NO)
                ->where('is_finished', \PostModel::FINISH_OK)
                ->where('title', 'like', '%' . $word . '%')
                ->count();
        });
    }

    // 我的发布帖子 无需缓存
    public static function listMyPosts($aff, $cate, $page, $limit)
    {
        $query = self::query()
            ->with([
                'topic' => function ($q) {
                    $q->where('status', PostTopicModel::STATUS_NORMAL);
                },
                'medias' => function ($q) {
                    $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST);
                },
            ])
            ->where('aff', $aff)
            ->when($cate === 'wait_release', function ($q) {
                $q->where('status', PostModel::STATUS_PASS)
                    ->where('is_finished', PostModel::FINISH_NO);
            })
            ->when($cate !== 'wait_release', function ($q) use ($cate) {
                $q->where('status', $cate);
                if ($cate == PostModel::STATUS_PASS) {
                    $q->where('is_finished', PostModel::FINISH_OK);
                }
            })
            ->where('is_deleted', self::DELETED_NO)
            ->orderByDesc('id')
            ->forPage($page, $limit);

        $posts = $query->get();

        return $posts;
    }

    // 我的发布帖子 无需缓存
    public static function listMyPostsNew($aff, $cate, $is_subscribe, $page, $limit)
    {
        $query = self::query()
            ->with([
                'topic' => function ($q) {
                    $q->where('status', PostTopicModel::STATUS_NORMAL);
                },
                'medias' => function ($q) {
                    $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST);
                },
            ])
            ->where('aff', $aff)
            ->where('is_subscribe', $is_subscribe)
            ->when($cate === 'wait_release', function ($q) {
                $q->where('status', PostModel::STATUS_PASS)
                    ->where('is_finished', PostModel::FINISH_NO);
            })
            ->when($cate !== 'wait_release', function ($q) use ($cate) {
                $q->where('status', $cate);
                if ($cate == PostModel::STATUS_PASS) {
                    $q->where('is_finished', PostModel::FINISH_OK);
                }
            })
            ->where('is_deleted', self::DELETED_NO)
            ->orderByDesc('id')
            ->forPage($page, $limit);

        $posts = $query->get();

        return $posts;
    }

    // 我的发布帖子 无需缓存
    public static function listMyPostsCount($aff, $cate, $is_subscribe)
    {
        return self::query()
            ->where('aff', $aff)
            ->where('is_subscribe', $is_subscribe)
            ->when($cate === 'wait_release', function ($q) {
                $q->where('status', PostModel::STATUS_PASS)
                    ->where('is_finished', PostModel::FINISH_NO);
            })
            ->when($cate !== 'wait_release', function ($q) use ($cate) {
                $q->where('status', $cate);
                if ($cate == PostModel::STATUS_PASS) {
                    $q->where('is_finished', PostModel::FINISH_OK);
                }
            })
            ->where('is_deleted', self::DELETED_NO)
            ->count();
    }

    public static function listPeerPosts($aff, $peerAff, $offset, $limit)
    {
        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $cacheKey = sprintf(\PostModel::POST_PEER_AFF_LIST_KEY, $peerAff, $offset, $limit);
        $group = $offset == 0 ? self::POST_FIRST_PAGE_GROUP_KEY : self::POST_PEER_AFF_LIST_GROUP_KEY;
        $posts = cached($cacheKey)
            ->group($group)
            ->fetchPhp(function () use ($peerAff, $offset, $limit) {
                return \PostModel::with([
                    'topic' => function ($q) {
                        $q->where('status', PostTopicModel::STATUS_NORMAL);
                    },
                    'medias' => function ($q) {
                        $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                    }
                ])->where('aff', $peerAff)
                    ->where('status', \PostModel::STATUS_PASS)
                    ->where('is_deleted', \PostModel::DELETED_NO)
                    ->where('is_finished', \PostModel::FINISH_OK)
                    ->orderByDesc('id')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
        });
        foreach ($posts as $post) {
            $post->is_like = in_array($post->id, $likes) ? 1 : 0;
        }
        return $posts;
    }

    public static function listFavoritedPosts($aff, $ids)
    {
        $likes = \UserCommunityLikeModel::listLikePostIds($aff);
        $posts = self::with([
            'user',
            'clubs' => function ($q) {
                $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
            },
            'topic' => function ($q) {
                $q->where('status', PostTopicModel::STATUS_NORMAL);
            },
            'medias' => function ($q) {
                $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
            }
        ])->whereIn('id', $ids)
            ->where('status', \PostModel::STATUS_PASS)
            ->where('is_deleted', self::DELETED_NO)
            ->where('is_finished', self::FINISH_OK)
            ->orderByDesc('id')
            ->get();
        foreach ($posts as $post) {
            $post->is_like = in_array($post->id, $likes) ? 1 : 0;
        }
        return $posts;
    }


    public function getIsFansAttribute(): int
    {
        static $ary = null;
        if (APP_MODULE == 'staff') {
            return 1;
        }
        if (isset($this->attributes['is_fans'])) {
            return $this->attributes['is_fans'];
        }
        $post_aff = $this->attributes['aff'] ?? 0;
        $aff = self::$watchUser ? self::$watchUser->aff : 0;
        if (empty($post_aff) || empty($aff)) {
            return 0;
        }
        $rk = PostClubMembersModel::generateRk($aff);
        if ($ary === null) {
            $ary = redis()->hGetAll($rk);
        }
        if (empty($ary) || !is_array($ary) || !isset($ary[$post_aff])) {
            return 0;
        }
        $time = $ary[$post_aff] ?? 0;
        if ($time < time()) {
            redis()->hDel($rk, $post_aff);
            return 0;
        }
        return 1;
    }

    public static function incrByViewNum($id, $num){
        $post = self::find($id);
        if ($post){
            $post->increment('view_num', $num, ['hot_sort' => \DB::raw('hot_sort + ' . $num)]);
        }
    }

    public static function replaceSym($content)
    {
        foreach (self::SPECIAL_SYMBOLS_LIST as $k => $v) {
            if (str_contains($content, $k)) {
                $content = str_replace($k, $v, $content);
            }
        }
        return $content;
    }

    public static function symReplace($content)
    {
        foreach (self::SPECIAL_SYMBOLS_LIST as $k => $v) {
            if (str_contains($content, $v)) {
                $content = str_replace($v, $k, $content);
            }
        }
        return $content;
    }

    public static function clearDetailCache($postId)
    {
        $key = sprintf(self::POST_DETAIL_KEY, $postId);
        cached($key)->clearCached();
    }

    public static function clearAllCache()
    {
        cached('')->clearGroup(self::POST_FIRST_PAGE_GROUP_KEY, self::POST_NEW_LIST_GROUP_KEY, self::POST_TOPIC_LIST_GROUP_KEY, self::POST_RECOMMEND_LIST_GROUP_KEY, self::POST_SEARCH_LIST_GROUP_KEY, self::POST_PEER_AFF_LIST_GROUP_KEY, self::POST_DETAIL_GROUP_KEY);
    }

    public static function clearFirstPageCache()
    {
        cached('')->clearGroup(self::POST_FIRST_PAGE_GROUP_KEY);
    }

    // 推荐帖子
    public static function listRecommend($sort, $topic_ids, $page, $limit)
    {
        $cacheKey = sprintf(self::CK_POST_RECOMMEND_LIST, $sort, $page, $limit);
        $posts = cached($cacheKey)
            ->group(self::GP_POST_RECOMMEND_LIST)
            ->chinese(self::CN_POST_RECOMMEND_LIST)
            ->fetchPhp(function () use ($sort, $topic_ids, $page, $limit) {
                return self::with([
                        'user',
                        'clubs' => function ($q) {
                            $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
                        },
                        'topic' => function ($q) {
                            $q->where('status', PostTopicModel::STATUS_NORMAL);
                        },
                        'medias' => function ($q) {
                            $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                        }
                    ])
                    ->where('status', self::STATUS_PASS)
                    ->where('is_deleted', self::DELETED_NO)
                    ->where('is_finished', \PostModel::FINISH_OK)
                    ->whereIn('topic_id', $topic_ids)
                    ->when($sort == 'pic', function ($q){
                        $q->where('category', self::TYPE_IMG);
                    })
                    ->when($sort == 'video', function ($q){
                        $q->where('category', self::TYPE_VIDEO);
                    })
                    ->when($sort == 'txt', function ($q){
                        $q->where('category', self::TYPE_TXT);
                    })
                    ->when($sort == 'hot', function ($q){
                        $q->orderByDesc('hot_sort');
                    })
                    ->when($sort == 'new', function ($q) {
                        $q->orderByDesc('set_top');
                    })
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->forPage($page ,$limit)
                    ->get();
        });
        foreach ($posts as $post) {
            $post->is_like = 0;
        }

        return $posts;
    }

    // 推荐帖子
    public static function listCategory($category_id, $sort, $topic_ids, $page, $limit)
    {
        $cacheKey = sprintf(self::CK_POST_CATEGORY_LIST, $category_id, $sort, $page, $limit);
        $posts = cached($cacheKey)
            ->group(self::GP_POST_CATEGORY_LIST)
            ->chinese(self::CN_POST_CATEGORY_LIST)
            ->fetchPhp(function () use ($sort, $topic_ids, $page, $limit) {
                return self::with([
                    'user',
                    'clubs' => function ($q) {
                        $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
                    },
                    'topic' => function ($q) {
                        $q->where('status', PostTopicModel::STATUS_NORMAL);
                    },
                    'medias' => function ($q) {
                        $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                    }
                ])
                    ->where('status', self::STATUS_PASS)
                    ->where('is_deleted', self::DELETED_NO)
                    ->where('is_finished', \PostModel::FINISH_OK)
                    ->whereIn('topic_id', $topic_ids)
                    ->when($sort == 'pic', function ($q){
                        $q->where('category', self::TYPE_IMG);
                    })
                    ->when($sort == 'video', function ($q){
                        $q->where('category', self::TYPE_VIDEO);
                    })
                    ->when($sort == 'txt', function ($q){
                        $q->where('category', self::TYPE_TXT);
                    })
                    ->when($sort == 'hot', function ($q){
                        $q->orderByDesc('hot_sort');
                    })
                    ->when($sort == 'new', function ($q) {
                        $q->orderByDesc('set_top');
                    })
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->forPage($page ,$limit)
                    ->get();
            });
        foreach ($posts as $post) {
            $post->is_like = 0;
        }

        return $posts;
    }

    // 推荐帖子
    public static function listFollow($affs, $page, $limit)
    {
        $posts = self::with([
            'user',
            'clubs' => function ($q) {
                $q->select('aff', 'year as post_club_year', 'month as post_club_month', 'quarter as post_club_quarter');
            },
            'topic' => function ($q) {
                $q->where('status', PostTopicModel::STATUS_NORMAL);
            },
            'medias' => function ($q) {
                $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
            }
        ])
            ->where('status', self::STATUS_PASS)
            ->where('is_deleted', self::DELETED_NO)
            ->where('is_finished', \PostModel::FINISH_OK)
            ->whereIn('aff', $affs)
            ->orderByDesc('id')
            ->forPage($page ,$limit)
            ->get();
        foreach ($posts as $post) {
            $post->is_like = 0;
        }

        return $posts;
    }

    public static function listPeerPostsNew($peerAff, $sort, $page, $limit)
    {
        $cacheKey = sprintf(\PostModel::CK_POST_PEER_LIST, $peerAff, $sort, $page, $limit);
        return cached($cacheKey)
            ->group(self::GP_POST_PEER_LIST)
            ->fetchPhp(function () use ($peerAff, $sort, $page, $limit) {
                return \PostModel::with([
                    'topic' => function ($q) {
                        $q->where('status', PostTopicModel::STATUS_NORMAL);
                    },
                    'medias' => function ($q) {
                        $q->where('relate_type', PostMediaModel::TYPE_RELATE_POST)->where('status', PostMediaModel::STATUS_OK);
                    }
                ])->where('aff', $peerAff)
                    ->where('status', \PostModel::STATUS_PASS)
                    ->where('is_deleted', \PostModel::DELETED_NO)
                    ->where('is_finished', \PostModel::FINISH_OK)
                    ->when($sort == 'free', function ($q){
                        return $q->where('is_subscribe', self::SUBSCRIBE_NO);
                    })
                    ->when($sort == 'hot', function ($q){
                        return $q->orderByDesc('hot_sort');
                    })
                    ->orderByDesc('id')
                    ->forPage($page, $limit)
                    ->get();
            });
    }
}