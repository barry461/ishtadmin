<?php

use helper\Validator;
use service\ChannelService;
use service\CommonService;
use Tbold\Serv\biz\BizAppVisit;

class ContentsController extends BaseController
{

    public function list_categoryAction(): bool
    {
        try {
            $version = $this->data['version'];
            $list = cached('list_category' . $version)
                ->fetchPhp(function () use ($version){
                    $list = MetasModel::query()
                        ->where('type', MetasModel::TYPE_CATEGORY)
                        ->orderBy('order')
                        ->get()->each(function (MetasModel $item){
                            $item->show_first = 0;
                        });
                    if (version_compare($version, '2.4.0', '>')){
                        /** @var MetasModel $two */
                        $two = clone $list->last();
                        $two->mid = 0;
                        $two->name = '首页';
                        $two->slug = 'index';
                        $two->description = '首页';
                        $two->show_first = 1;
                        $list->prepend($two);

                        /** @var MetasModel $first */
                        $first = clone $list->last();
                        $first->mid = -1;
                        $first->name = '秘闻';
                        $first->slug = 'index';
                        $first->description = '秘闻';
                        $first->type = 'secret';
                        $first->show_first = 0;
                        $list->prepend($first);
                    }else{
                        if (version_compare($version, '2.3.0', '>')){
                            /** @var MetasModel $two */
                            $two = clone $list->last();
                            $two->mid = -1;
                            $two->name = '秘闻';
                            $two->slug = 'index';
                            $two->description = '秘闻';
                            $two->type = 'secret';
                            $two->show_first = 0;
                            $list->prepend($two);
                        }
                        /** @var MetasModel $first */
                        $first = clone $list->last();
                        $first->mid = 0;
                        $first->name = '首页';
                        $first->slug = 'index';
                        $first->description = '首页';
                        $first->show_first = 1;
                        $list->prepend($first);
                    }

                    return $list;
                });

            //上报渠道V2数据
            ChannelService::reportVisit($this->member, USER_IP,BizAppVisit::ID_VISIT_HOME);

            return $this->listJson($list, 'mid');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_contentsAction(): bool
    {
        try {
            FieldsModel::setWatchUser($this->member);
            $version = $this->data['version'];
            $table = \Yaf\Registry::get('database')->prefix;
            $date = $this->data['date'] ?? null;
            $fullTable = $table.'contents';
            $mid = $this->data['mid'] ?? null;
            $key = 'content:tags-list-'.$mid.'_'.$this->page.'_'.$date.'_'.$version;
            $list = cached($key)
                ->chinese('大类列表')
                ->fetchPhp(function () use ($mid, $fullTable, $date, $version) {
                    //查询分类的自定义排序
                    $cti_order = '';
                    if ($mid > 0){
                        $meta = MetasModel::find($mid);
                        if (!empty($meta) && $meta->type == MetasModel::TYPE_CATEGORY && $meta->sort_column && $meta->sort_type){
                            $cti_order = sprintf('%s %s', $meta->sort_column, $meta->sort_type);
                        }
                    }
                    return ContentsModel::query()
                        ->with([
                            'relationships' => function ($query) {
                                $query->with('meta');
                            },
                        ])
                        ->when(empty($date), function ($query) {
                            $query->where('created', '<=', time());
                        })
                        ->when($date, function ($query, $value) {
                            $start = strtotime("$value 00:00:00");
                            $end = strtotime("$value 23:59:59");
                            $end = min($end, time());
                            $query->whereBetween('created', [$start, $end]);
                        })
                        ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                        ->with('fields', 'author')
                        ->when(version_compare($version, '2.3.0', '>'), function ($q){
                            $q->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS]);
                        })
                        ->when(version_compare($version, '2.3.0', '<='), function ($q){
                            $q->where('type', ContentsModel::TYPE_POST);
                        })
                        ->when($mid == -1, function ($query){
                            $query->where('status', '=', ContentsModel::STATUS_SECRET);
                        }, function ($query){
                            $query->where('status', ContentsModel::STATUS_PUBLISH);
                        })
                        ->where('is_slice', 1)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->when($mid, function ($query, $mid) {
                            if ($mid > 0){
                                $query
                                    ->where('relationships.mid', $mid)
                                    ->join('relationships', 'relationships.cid', 'contents.cid');
                            }
                        }, function ($query) {
                            $query->where('is_home', '=', 1)
                                ->orderBy('home_top', 'desc');
                        })
                        ->when($cti_order, function ($q, $cti_order){
                            $q->orderByRaw($cti_order);
                        })
                        ->orderByDesc('created')
                        ->forPage($this->page, $this->limit)
                        ->get()
                        ->each(function (ContentsModel $model) {
                            $model->loadTagWithCategory();
                        });
                });
            $banner = [];
            if ($this->page == 1) {
                //$banner = AdsModel::listPos(AdsModel::POS_CONTENT_LIST);
                $banner =  CommonService::getAds($this->member,AdsModel::POS_CONTENT_LIST);
            }

            return $this->listJson($list, 'cid', [
                'banner' => $banner,
                'count'  => $list->count(),
                'page'   => $this->page,
                'limit'  => $this->limit,
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 通过标签id，获取标签的资源 */
    public function list_contents_tagAction(): bool
    {
        try {
            $version = $this->version;
            $mid = $this->data['mid'] ?? null;
            if (empty($mid)) {
                return $this->errorJson('参数错误');
            }
            $key = 'content:tags-list-'.$mid.'-'.$this->page.'-'.$version;
            $list = cached($key)
                ->chinese('标签的内容列表')
                ->fetchPhp(function () use ($mid, $version) {
                    $fullTable = \Yaf\Registry::get('database')->prefix
                        .'contents';
                    $meta = MetasModel::getMetaByMid($mid);
                    test_assert($meta, '数据异常');
                    if ($meta->count <= 100){
                        $cids = RelationshipsModel::getCidArrByMid($mid);
                        if (empty($cids)){
                            return [];
                        }
                        return ContentsModel::where('created', '<=', time())
                            ->selectRaw("cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                            ->with('fields', 'author')
                            ->whereIn('cid', $cids)
                            ->where('status', ContentsModel::STATUS_PUBLISH)
                            ->when(version_compare($version, '2.3.0', '>'), function ($q){
                                $q->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS]);
                            })
                            ->when(version_compare($version, '2.3.0', '<='), function ($q){
                                $q->where('type', ContentsModel::TYPE_POST);
                            })
                            ->where('app_hide', ContentsModel::APP_HIDE_NO)
                            ->where('is_slice', 1)
                            ->orderByDesc('created')
                            ->forPage($this->page, $this->limit)
                            ->get()
                            ->each(function (ContentsModel $model) {
                                $model->loadTagWithCategory();
                            });
                    }else{
                        return ContentsModel::where('created', '<=', time())
                            ->with([
                                'relationships' => function ($query) {
                                    $query->with('meta');
                                },
                            ])
                            ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                            ->with('fields', 'author')
                            ->where('relationships.mid', $mid)
                            ->join('relationships', 'relationships.cid', 'contents.cid')
                            ->where('status', ContentsModel::STATUS_PUBLISH)
                            ->when(version_compare($version, '2.3.0', '>'), function ($q){
                                $q->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS]);
                            })
                            ->when(version_compare($version, '2.3.0', '<='), function ($q){
                                $q->where('type', ContentsModel::TYPE_POST);
                            })
                            ->where('app_hide', ContentsModel::APP_HIDE_NO)
                            ->where('is_slice', 1)
                            ->orderByDesc('created')
                            ->forPage($this->page, $this->limit)
                            ->get()
                            ->each(function (ContentsModel $model) {
                                $model->loadTagWithCategory();
                            });
                    }
                });

            $banner = [];
            if ($this->page == 1) {
                //$banner = AdsModel::listPos(AdsModel::POS_CONTENT_LIST);
                $banner = CommonService::getAds($this->member,AdsModel::POS_CONTENT_LIST);
            }

            return $this->listJson($list, 'cid', ['banner' => $banner]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function detail_contentAction(): bool
    {
        $cid = $this->data['cid'] ?? null;
        if (empty($cid)) {
            return $this->errorJson('参数错误');
        }
        try {
            /** @var ContentsModel $cur */
            $cur = cached("content-$cid")
                ->chinese('文章详情')
                ->fetchPhp(function () use ($cid){
                $cur = ContentsModel::query()
                    ->selectRaw('cid,title,slug,created,modified,`text`,authorId,commentsNum,allowComment,allowPing,allowFeed,favorite_num,view,like_num,fake_view')
                    ->where('is_slice', 1)
                    ->whereIn('status', [ContentsModel::STATUS_PUBLISH, ContentsModel::STATUS_SECRET])
                    ->where('app_hide', ContentsModel::APP_HIDE_NO)
                    ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_PAGE])
                    ->where('created', '<=', time())
                    ->where('cid', $cid)
                    ->first();
                if (empty($cur)) {
                    throw new RuntimeException('文章不存在');
                }
                $cur->load(['fields', 'author']);
                $cur->loadTagWithCategory();
                return $cur;
            });
            $cur->loadMarkdown();
            //收藏
            $favorite_id = redis()->sIsMember(ContentsFavoriteModel::generateID($this->member->aff) , $cid);
            $cur->setAttribute('is_favorite', $favorite_id ? 1 : 0);

            //点赞
            $like_id = redis()->sIsMember(ContentsLikeModel::generateID($this->member->aff) , $cid);
            $cur->setAttribute('is_like', $like_id ? 1 : 0);

            $query = ContentsModel::query()->where('is_slice', 1)
                ->selectRaw('cid,title,slug,created,modified,authorId,commentsNum,allowComment,allowPing,allowFeed,type,view,fake_view')
                ->where('status', ContentsModel::STATUS_PUBLISH)
                ->where('app_hide', ContentsModel::APP_HIDE_NO)
                ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_SKITS, ContentsModel::TYPE_BIG_WENT])
                ->where('created', '<=', time());
            //版本控制
            if (version_compare($this->version, '2.5.0', '>')){
                /** @var ContentsModel $next */
                $next = cached("content-${cid}-next-new")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        $model = $query->clone()
                            ->with([
                                'relationships' => function ($query) {
                                    $query->with('meta');
                                },
                            ])
                            ->with('fields', 'author')
                            ->where('cid', '>', $cid)
                            ->first();
                        if (!empty($model)){
                            $model->loadTagWithCategory();
                        }
                        return $model;
                    });
                $prev = null;
            }else{
                /** @var ContentsModel $next */
                $next = cached("content-${cid}-next")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        return $query->clone()->where('cid', '>', $cid)->first();
                    });
                $prev = cached("content-${cid}-prev")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        return $query->clone()->where('cid', '<', $cid)->orderByDesc('cid')->first();
                    });
            }
            $wxqun = null;
            if (!$cur->fieldValue('hide_wxqun')) {
                //$wxqun = AdsModel::onePos(AdsModel::POSITION_QUN); // 隐藏微信群
                $wxqun = CommonService::getAds($this->member,AdsModel::POSITION_QUN,true); // 隐藏微信群
                if ($wxqun && version_compare($this->version, '2.5.0', '>')){
                    $wxqun->img_url = url_image('/upload_01/ads/20240713/2024071317473027633.png');
                }
            }
            $redbag = null;
            if (!$cur->fieldValue('hide_redbag')) {
                //$redbag = AdsModel::onePos(AdsModel::POSITION_REDBAG);  // 隐藏红包
                $redbag = CommonService::getAds($this->member,AdsModel::POSITION_REDBAG,true);  // 隐藏红包
                if ($redbag && version_compare($this->version, '2.5.0', '>')){
                    if ($redbag->type == 3){
                        $redbag->img_url = url_image('/upload_01/ads/20240713/2024071317475126591.png');
                    }else{
                        //$redbag->img_url = '';
                    }
                }
            }
            $banner = [];
            if (!$cur->fieldValue('hide_banner')) {
                //$banner = AdsModel::listPos(AdsModel::POSITION_DETAIL);
                $banner = CommonService::getAds($this->member,AdsModel::POSITION_DETAIL);
            }
            //顶部banner
            $top_banner = CommonService::getAds($this->member,AdsModel::POS_SKITS_TOP_BANNER);

            $shareText = setting('detail_share_text', '');
            $shareText = str_replace("{title}", $cur->title, $shareText);
            $shareText = str_replace("{id}", $cur->cid, $shareText);

            $data = [
                'prev'       => $prev,
                'cur'        => $cur,
                'next'       => $next,
                'banner'     => $banner,
                'top_banner' => $top_banner,
                'qun_ad'     => $wxqun,
                'redbag_ad'  => $redbag,
                'share_text' => $shareText,
                'hot_search' => HotSearchModel::getOne(),//热搜
                'notice'     => CommonService::getNotice($this->member, NoticeModel::POS_DETAIL),
            ];
            //浏览数记录
            jobs([ContentsModel::class, 'incrByView'], [$cid]);

            return $this->showJson($data);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_commentsAction(): bool
    {
        try {
            $cid = $this->data['cid'] ?? null;
            if (empty($cid)) {
                return $this->errorJson('参数错误');
            }
            $list = cached("list-comment:$cid-{$this->page}")
                ->group("list-comment:$cid")
                ->fetchPhp(function () use ($cid){
                    return CommentsModel::where('cid', $cid)
                        ->selectRaw('coid,cid,thumb,reply_author,author,authorId,ownerId,`text`,type,status,parent,created')
                        ->where('status', CommentsModel::STATUS_APPROVED)
                        ->where('parent', 0)
                        ->forPage($this->page, $this->limit)
                        ->get()
                        ->each(function (CommentsModel $item) {
                            $items = CommentsModel::where('cid', $item->cid)
                                ->selectRaw('coid,cid,thumb,reply_author,author,authorId,ownerId,`text`,type,status,parent,created')
                                ->where('status', CommentsModel::STATUS_APPROVED)
                                ->where('parent', $item->coid)
                                ->limit(3)
                                ->get();
                            $item->setRelation('items', $items);
                            $item->setAttribute('is_owner', $item->authorId == $item->ownerId ? 1 : 0);
                        });
                },1800);

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_reply_commentsAction(): bool
    {
        try {
            $cid = $this->data['cid'] ?? null;
            $coid = $this->data['coid'] ?? null;
            if (empty($cid) || empty($coid)) {
                return $this->errorJson('参数错误');
            }
            $list = CommentsModel::where('cid', $cid)
                ->selectRaw('coid,cid,reply_author,thumb,author,authorId,ownerId,`text`,type,status,parent,created')
                ->where('status', CommentsModel::STATUS_APPROVED)
                ->where('parent', $coid)
                ->forPage($this->page, $this->limit)
                ->get();

            $list->each(function (CommentsModel $item) {
                $item->setAttribute('is_owner',
                    $item->authorId == $item->ownerId ? 1 : 0);
            });

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_my_commentsAction(): bool
    {
        try {
            $aff = $this->member->aff;
            $page = $this->page;
            $limit = $this->limit;
            $list = cached("content-{$aff}-{$page}-{$limit}")
                ->chinese('我的评论回复')
                ->fetchPhp(function () use ($aff, $page, $limit){
                    return CommentsModel::query()
                        ->where('type', CommentsModel::TYPE_COMMENT)
                        ->selectRaw('coid,cid,reply_author,reply_aff,thumb,author,app_aff,authorId,ownerId,`text`,type,status,parent,created')
                        ->with([
                            'contents' => function ($query) {
                                return $query->selectRaw('cid,title,slug,created,modified,authorId,commentsNum,allowComment,allowPing,allowFeed')
                                    ->with([
                                        'fields' => function ($query) {
                                            return $query->whereIn('name',
                                                ['banner', 'disableBanner']);
                                        },
                                    ]);
                            },
                            'reply'    => function ($query) {
                                return $query
                                    ->where('type', CommentsModel::TYPE_COMMENT)
                                    ->selectRaw('coid,cid,thumb,reply_author,author,app_aff,authorId,ownerId,`text`,type,status,parent,created');
                            },
                        ])
                        ->where('app_aff', $aff)
                        ->orWhere('reply_aff', $aff)
                        ->orderByDesc('coid')
                        ->forPage($page, $limit)
                        ->get();
                });

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function create_commentAction(): bool
    {
        $cid = $this->data['cid'] ?? '';
        $coid = $this->data['coid'] ?? null;
        $text = trim($this->data['text'] ?? '');
        if (empty($cid) || empty($text)) {
            return $this->errorJson('操作失败');
        }
        //封禁IP
        $exist = redis()->sIsMember(BAN_IPS_KEY, USER_IP);
        if ($exist){
            return $this->errorJson('评论失败，你已被封禁！');
        }
        try {
            $parent = null;
            $model = ContentsModel::find($cid);
            test_assert($model, '您评论的文章不存在');
            $parent_coid = 0;
            $sec_parent_coid = 0;
            if (is_numeric($coid) && $coid > 0) {
                $parent = CommentsModel::find($coid);
                test_assert($parent, '您回复的评论不存在');
                if ($parent->parent){
                    //二级评论
                    $parent_coid = $parent->parent;
                    $sec_parent_coid = $parent->coid;
                }else{
                    //一级评论ID作为父ID
                    $parent_coid = $parent->coid;
                }
            }
            if (empty($model->allowComment)) {
                throw new RuntimeException('该文章不允许评论');
            }

            // 敏感词检测 - 自动过滤
            $sensitiveHandle = SensitiveWordsModel::sensitiveHandle();
            $hasSensitiveWord = $sensitiveHandle->islegal($text);
            $commentStatus = CommentsModel::STATUS_WAITING;
            
            if ($hasSensitiveWord) {
                // 包含敏感词，自动标记为过滤状态
                $commentStatus = CommentsModel::STATUS_FILTER;
            }
            
            $data = [
                'cid'          => $model->cid,
                'created'      => time(),
                'author'       => $this->member->nickname,
                'reply_author' => $parent ? $parent->author : '',
                'reply_aff'    => $parent ? $parent->app_aff : 0,
                'thumb'        => $this->member->thumb,
                'app_aff'      => $this->member->aff,
                'authorId'     => 0,
                'ownerId'      => $model->authorId,
                'mail'         => '',
                'url'          => '',
                'ip'           => client_ip(),
                'agent'        => 'app',
                'text'         => htmlentities($text),
                'type'         => CommentsModel::TYPE_COMMENT,
                'status'       => $commentStatus,
                'parent'       => $parent_coid,
                'sec_parent'   => $sec_parent_coid //二级评论ID
            ];
            
            // 广告检测
            $check2 = tools\FilterService::validate($text);
            if (!$check2){
                $cityname = ($this->position['province'].$this->position['city']) ?: '火星';
                RubCommentModel::addData($cid, $this->member->aff, $text, RubCommentModel::TYPE_CONTENTS, USER_IP, $cityname, $this->member->nickname, $data);
                test_assert(false, '存在广告嫌疑');
            }

            $this->verifyMemberSayRole();
            $cacheKey2 = sprintf(PostCommentModel::POST_COMMENT_LIMIT_KEY, $this->member->aff);
            $mem = \tools\RedisService::get($cacheKey2);
            if (intval($mem) >= PostCommentModel::POST_COMMENT_LIMIT_NUM) {
                PostBanModel::setBan($this->member->aff);
                throw new RuntimeException('评论失败，你已被封禁！等待自动解禁，恶意刷评论打广告会被永久封禁。');
            }

            $comment = CommentsModel::create($data);
            if ($comment && $parent && $parent->app_aff && $parent->member) {
                $appMember = $parent->member;
                //MessageModel::addMessage($appMember, $comment);
            }

            $mem = !$mem ? 1 : $mem + 1;
            \tools\RedisService::set($cacheKey2, $mem, PostCommentModel::POST_COMMENT_LIMIT_SECOND);

            return $this->successMsg('评论成功，请耐心等待审核');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 往期福利 */
    public function archivesAction(): bool
    {
        try {
            $last_ix = $this->last_ix ? intval($this->last_ix) : '(null)';
            $date = $this->data['date'] ?? null;
            $list = cached('contents:archives-'.$last_ix.'_' . $date)
                ->chinese('往期福利')
                ->fetchPhp(function () use ($date) {
                    return ContentsModel::query()
                        ->when(empty($date), function ($query) {
                            $query->where('created', '<=', time());
                        })
                        ->when($date, function ($query, $value) {
                            $start = strtotime("$value 00:00:00");
                            $end = strtotime("$value 23:59:59");
                            $end = min($end, time());
                            $query->whereBetween('created', [$start, $end]);
                        })
                        ->with([
                            'fields' => function ($query) {
                                return $query->whereIn('name', ['banner', 'disableBanner','redirect']);
                            },
                        ])
                        ->from('contents', 'contents')
                        ->selectRaw("cid,title,slug,created")
                        ->where('type', ContentsModel::TYPE_POST)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->forPageBeforeId(50,  $this->last_ix ? intval($this->last_ix) : null,  'created')
                        ->get()
                        ->each(function (ContentsModel $item) {
                            $item->setAttribute('date', $item->created->toDateString());
                        });
                });
            $last_ix = $list->last() ? strtotime($list->last()->created).'' : '';
            $list = $list->groupBy('date');
            $result = collect([]);
            foreach ($list as $group => $items) {
                $result->push([
                    'group_name' => $group,
                    'items'      => $items,
                ]);
            }

            return $this->showJson(['list' => $result, 'last_ix' => $last_ix]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 热门 */
    public function popularAction(): bool
    {
        try {
            $prefix = \Yaf\Registry::get('database')->prefix;
            $table = "{$prefix}contents";
            $list = cached("list-popular-". $this->page)
                ->fetchPhp(function () use ($table,$prefix){
                    return ContentsModel::where('created', '<=', time())
                        ->with([
                            'relationships' => function ($query) {
                                $query->with('meta');
                            },
                        ])
                        ->with([
                            'fields' => function ($query) {
                                return $query->whereIn('name',  ['banner', 'disableBanner', 'hotSearch']);
                            },
                            'author',
                        ])
                        ->selectRaw("$table.cid,$table.title,$table.created,$table.authorId,$table.type,{$prefix}fields.str_value")
                        ->join('fields', 'fields.cid', 'contents.cid')
                        ->where('fields.name', 'hotSearch')
                        ->where('fields.str_value', '1')
                        ->where('contents.status', ContentsModel::STATUS_PUBLISH)
                        ->where('contents.type', ContentsModel::TYPE_POST)
                        ->where('contents.app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('contents.is_slice', 1)
                        ->orderByDesc('contents.created')
                        ->forPage($this->page, $this->limit)
                        ->get();
                });

            $list->each(function (ContentsModel $item) {
                $item->loadTagWithCategory();
            });

            return $this->showJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 标签 */
    public function list_tagsAction(): bool
    {
        try {
            $popular = [];
            if ($this->page == 1){
                $popular = setting('popular:tags', '');
                $tags = explode(',', $popular);
                $list = MetasModel::query()
                    ->where('type', MetasModel::TYPE_TAG)
                    ->whereIn('name', $tags)
                    ->get();
                $popular = array_keep_idx($list , $tags , 'name');
            }
            $list = cached('list:tags:v4-' . $this->page)
                ->group('list:tags')
                ->fetchPhp(function () {
                    return MetasModel::query()
                        ->where('type', MetasModel::TYPE_TAG)
                        ->where('count', '>', 0)
                        ->orderByDesc('mid')
                        ->forPage($this->page , 60)
                        ->get();
                });
            if ($this->page == 1) {
                $list = $list->slice(10)->values();
            }

            return $this->showJson([
                'popular' => $popular,
                'all'     => $list,
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 搜索 */
    public function searchOldAction(): bool
    {
        try {
            $kwy = trim($this->data['kwy'] ?? '');
            if (empty($kwy)) {
                return $this->errorJson('参数错误');
            }
            $this->verifyMemberSayRole();
            $this->verifyFrequency(10, 10);
//            $key = "search:kk:" . $kwy.'-' . $this->page;
//            $data = redis()->get($key);
//            if ($data){
//                $list = json_decode($data , true);
//                return $this->listJson($list);
//            }
            $query = ContentsModel::where('created', '<=', time())
                ->with([
                    'relationships' => function ($query) {
                        $query->with('meta');
                    },
                ])
                ->selectRaw('cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view')
                ->with('fields', 'author')
                ->where(function ($query) use ($kwy) {
                    $query->where('title', 'like', "%$kwy%")
                        ->orWhere('text', 'like', "%$kwy%");
                })
                ->where('app_hide', ContentsModel::APP_HIDE_NO)
                ->where('status', ContentsModel::STATUS_PUBLISH)
                ->where('type', ContentsModel::TYPE_POST)
                ->where('is_slice', 1)
                ->orderByDesc('created')
                ->forPage($this->page, $this->limit);
            $list = $query
                ->get()
                ->each(function (ContentsModel $model) {
                    $model->loadTagWithCategory();
                });
//            if ($list->count()) {
//                //后台运行
//                jobs([SearchWordModel::class, 'createSearchRecord'], [$kwy,$this->member->aff,SearchWordModel::TYPE_CONTENTS]);
//            }
//            redis()->zIncrBy('search:toplist', 1, $kwy);
//            redis()->set($key , json_encode($list) , 3600);

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 搜索 */
    public function searchBkAction(): bool
    {
        try {
            $kwy = trim($this->data['kwy'] ?? '');
            if (empty($kwy)) {
                return $this->errorJson('参数错误');
            }
            $this->verifyMemberSayRole();
            $this->verifyFrequency(10, 10);
            $key = "search:mtc:new:v1:" . $kwy.'-' . $this->page;
            $data = redis()->get($key);
            if ($data){
                $list = json_decode($data , true);
                return $this->listJson($list);
            }

            //判断搜索关键词是否是全英文
            if (strlen($kwy) == mb_strlen($kwy)){
                $list = ContentDataEnModel::on('manticore')
                    ->selectRaw('id as cid,title,created,type,status,commentsNum,is_home,home_top,is_slice,authorId,authorName,category,tags,view')
                    ->where('app_hide',intval(ContentsModel::APP_HIDE_NO))
                    ->where('created','<=',time())
                    ->whereRaw("match('{$kwy}')")
                    ->orderByDesc('created')
                    ->forPage($this->page, $this->limit)
                    ->get()
                    ->each(function (ContentDataEnModel $model){
                        $model->order = 0;
                        $author = new stdClass();
                        $author->uid = 0;
                        $author->screenName = $model->authorname;
                        $model->author = $author;
                        $category = explode(',',$model->category);
                        $catArr = [];
                        foreach ($category as $key => $val){
                            $catObj = new stdClass();
                            $catObj->mid = 0;
                            $catObj->name = $val;
                            $catObj->slug = '';
                            $catObj->type = 'category';
                            $catObj->description = '';
                            $catObj->count = 0;
                            $catObj->order = $key + 1;
                            $catObj->parent = 0;
                            $catObj->sort_type = '';
                            $catObj->sort_column = '';
                            $catArr[] = $catObj;
                        }
                        $model->category = $catArr;
                        $tags = explode(',',$model->tags);
                        $tagsArr = [];
                        foreach ($tags as $v){
                            $tagObj = new stdClass();
                            $tagObj->mid = 0;
                            $tagObj->name = $v;
                            $tagObj->slug = $v;
                            $tagObj->type = 'tag';
                            $tagObj->description = '';
                            $tagObj->count = 0;
                            $tagObj->order = 0;
                            $tagObj->parent = 0;
                            $tagObj->sort_type = '';
                            $catObj->sort_column = '';
                            $tagsArr[] = $tagObj;
                        }
                        $model->tags = $tagsArr;
                        $model->commentsNum = $model->commentsnum;
                        $model->authorId = $model->authorid;
                        $model->view = ceil($model->view * ContentsModel::FAKE_VIEW_MULTIPLE);
                        unset($model->commentsnum);
                        unset($model->authorid);

                        //查询field
                        $field_list  = ContentFieldsDataModel::on('manticore')
                            ->selectRaw('cid,name,type,str_value,int_value,float_value')
                            ->where('cid',intval($model->cid))
                            ->get()->each(function (ContentFieldsDataModel $item){
                                if ($item->name == 'banner' && $item->type == ContentFieldsDataModel::TYPE_STR){
                                    $item->str_value = url_image($item->str_value);
                                }elseif ($item->name == 'redirect' && $item->type == ContentFieldsDataModel::TYPE_STR){
                                    $value = str_replace([" " , "\r", "\n"] , '' , $item->str_value);
                                    preg_match("#\/\d+\.html#", $value, $matches1);
                                    if ($matches1 && $matches1[0]){
                                        preg_match("#\d+#", $matches1[0], $matches2);
                                        if ($matches2){
                                            $cid = $matches2[0];
                                            if ($cid > 0){
                                                $redirect = FieldsModel::getRedirectStr($cid);
                                                if ($redirect){
                                                    $item->str_value = $redirect;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        $model->fields = $field_list->toArray();
                    });
            }else{
                $list = ContentDataModel::on('manticore')
                    ->selectRaw('id as cid,title,created,type,status,commentsNum,is_home,home_top,is_slice,authorId,authorName,category,tags,view')
                    ->where('app_hide',intval(ContentsModel::APP_HIDE_NO))
                    ->where('created','<=',time())
                    ->whereRaw("match('{$kwy}')")
                    ->orderByDesc('created')
                    ->forPage($this->page, $this->limit)
                    ->get()
                    ->each(function (ContentDataModel $model){
                        $model->order = 0;
                        $author = new stdClass();
                        $author->uid = 0;
                        $author->screenName = $model->authorname;
                        $model->author = $author;
                        $category = explode(',',$model->category);
                        $catArr = [];
                        foreach ($category as $key => $val){
                            $catObj = new stdClass();
                            $catObj->mid = 0;
                            $catObj->name = $val;
                            $catObj->slug = '';
                            $catObj->type = 'category';
                            $catObj->description = '';
                            $catObj->count = 0;
                            $catObj->order = $key + 1;
                            $catObj->parent = 0;
                            $catObj->sort_type = '';
                            $catObj->sort_column = '';
                            $catArr[] = $catObj;
                        }
                        $model->category = $catArr;
                        $tags = explode(',',$model->tags);
                        $tagsArr = [];
                        foreach ($tags as $v){
                            $tagObj = new stdClass();
                            $tagObj->mid = 0;
                            $tagObj->name = $v;
                            $tagObj->slug = $v;
                            $tagObj->type = 'tag';
                            $tagObj->description = '';
                            $tagObj->count = 0;
                            $tagObj->order = 0;
                            $tagObj->parent = 0;
                            $tagObj->sort_type = '';
                            $catObj->sort_column = '';
                            $tagsArr[] = $tagObj;
                        }
                        $model->tags = $tagsArr;
                        $model->commentsNum = $model->commentsnum;
                        $model->authorId = $model->authorid;
                        $model->view = ceil($model->view * ContentsModel::FAKE_VIEW_MULTIPLE);
                        unset($model->commentsnum);
                        unset($model->authorid);

                        //查询field
                        $field_list  = ContentFieldsDataModel::on('manticore')
                            ->selectRaw('cid,name,type,str_value,int_value,float_value')
                            ->where('cid',intval($model->cid))
                            ->get()->each(function (ContentFieldsDataModel $item){
                                if ($item->name == 'banner' && $item->type == ContentFieldsDataModel::TYPE_STR){
                                    $item->str_value = url_image($item->str_value);
                                }elseif ($item->name == 'redirect' && $item->type == ContentFieldsDataModel::TYPE_STR){
                                    $value = str_replace([" " , "\r", "\n"] , '' , $item->str_value);
                                    preg_match("#\/\d+\.html#", $value, $matches1);
                                    if ($matches1 && $matches1[0]){
                                        preg_match("#\d+#", $matches1[0], $matches2);
                                        if ($matches2){
                                            $cid = $matches2[0];
                                            if ($cid > 0){
                                                $redirect = FieldsModel::getRedirectStr($cid);
                                                if ($redirect){
                                                    $item->str_value = $redirect;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        $model->fields = $field_list->toArray();
                    });
            }
            if ($list->count()) {
                //后台运行
                jobs([SearchWordModel::class, 'createSearchRecord'], [$kwy,$this->member->aff,SearchWordModel::TYPE_CONTENTS]);
            }
            redis()->zIncrBy('search:toplist', 1, $kwy);
            redis()->set($key , json_encode($list) , 3600);

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 搜索 */
    public function searchAction(): bool
    {
        try {
            $kwy = trim($this->data['kwy'] ?? '');
            if (empty($kwy)) {
                return $this->errorJson('参数错误');
            }
            $this->verifyMemberSayRole();
            $this->verifyFrequency(10, 10);
            $kwy = preg_replace('/[^\x{4e00}-\x{9fa5}\w]/u', '', $kwy);
            $kwy = '*' . str_replace(' ', '*', $kwy) . '*';
            FieldsModel::setWatchUser($this->member);
            $list = cached(sprintf('search:%s:%s:%s', substr(md5($kwy), 0, 8), $this->page, $this->limit))
                ->group('gp:search:kwy')
                ->chinese('搜索列表')
                ->fetchPhp(function () use ($kwy){
                    $query = ContentsModel::where('created', '<=', time())
                        ->with([
                            'relationships' => function ($query) {
                                $query->with('meta');
                            },
                        ])
                        ->selectRaw('cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view')
                        ->with('fields', 'author')
                        ->whereRaw("match(title,text) against(? in boolean mode)", [$kwy])
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS])
                        ->where('is_slice', 1)
                        ->orderByDesc('created')
                        ->forPage($this->page, $this->limit);
                    return $query
                        ->get()
                        ->each(function (ContentsModel $model) {
                            $model->loadTagWithCategory();
                        });
                });
            if ($list->count()) {
                //后台运行
                jobs([SearchWordModel::class, 'createSearchRecord'], [$kwy,$this->member->aff,SearchWordModel::TYPE_CONTENTS]);
            }
            redis()->zIncrBy('search:toplist', 1, $kwy);

            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function search_ttAction(): bool
    {
        try {
            $kwy = trim($this->data['kwy'] ?? '');
            if (empty($kwy)) {
                return $this->errorJson('参数错误');
            }
            $this->verifyMemberSayRole();
            $this->verifyFrequency(10, 10);
            $list = ContentDataModel::on('manticore')
                ->selectRaw('id as cid,title,created,type,status,commentsNum,is_home,home_top,is_slice,authorId,authorName,category,tags')
                ->where('app_hide',intval(ContentsModel::APP_HIDE_NO))
                ->where('created','<=',time())
                ->whereRaw("match('{$kwy}')")
                ->orderByDesc('created')
                ->forPage($this->page, $this->limit)
                ->get()
                ->each(function (ContentDataModel $model){
                    $model->order = 0;
                    $author = new stdClass();
                    $author->uid = 0;
                    $author->screenName = $model->authorname;
                    $model->author = $author;
                    $category = explode(',',$model->category);
                    $catArr = [];
                    foreach ($category as $key => $val){
                        $catObj = new stdClass();
                        $catObj->mid = 0;
                        $catObj->name = $val;
                        $catObj->slug = '';
                        $catObj->type = 'category';
                        $catObj->description = '';
                        $catObj->count = 0;
                        $catObj->order = $key + 1;
                        $catObj->parent = 0;
                        $catObj->sort_type = '';
                        $catObj->sort_column = '';
                        $catArr[] = $catObj;
                    }
                    $model->category = $catArr;
                    $tags = explode(',',$model->tags);
                    $tagsArr = [];
                    foreach ($tags as $v){
                        $tagObj = new stdClass();
                        $tagObj->mid = 0;
                        $tagObj->name = $v;
                        $tagObj->slug = $v;
                        $tagObj->type = 'tag';
                        $tagObj->description = '';
                        $tagObj->count = 0;
                        $tagObj->order = 0;
                        $tagObj->parent = 0;
                        $tagObj->sort_type = '';
                        $catObj->sort_column = '';
                        $tagsArr[] = $tagObj;
                    }
                    $model->tags = $tagsArr;
                    $model->commentsNum = $model->commentsnum;
                    $model->authorId = $model->authorid;
                    unset($model->commentsnum);
                    unset($model->authorid);

                    //查询field
                    $field_list  = ContentFieldsDataModel::on('manticore')
                        ->selectRaw('cid,name,type,str_value,int_value,float_value')
                        ->where('cid',intval($model->cid))
                        ->get()->each(function (ContentFieldsDataModel $item){
                            if ($item->name == 'banner' && $item->type == ContentFieldsDataModel::TYPE_STR){
                                $item->str_value = url_image($item->str_value);
                            }
                        });
                    $model->fields = $field_list->toArray();
                });
            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function search_optionsAction(): bool
    {
        try {
            $this->verifyFrequency(10, 10);
            $toplist = redis()->zRevRange('search:toplist', 0, 9, true);
            $toplist = collect($toplist)->map(function ($value, $key) {
                return ['name' => $key, 'value' => $value];
            })->values();

            return $this->showJson([
                'toplist' => $toplist,
                //'banner'  => AdsModel::listPos(AdsModel::POS_SEARCH_BANNER),
                'banner'  => CommonService::getAds($this->member,AdsModel::POS_SEARCH_BANNER),
                'hot_tags'  => ContentsModel::hotTags(),
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function trigger_favoriteAction(): bool
    {
        $cid = $this->data['cid'] ?? 0;
        if (empty($cid)) {
            return $this->errorJson('参数错误');
        }
        try {
            transaction(function () use ($cid){
                /** @var ContentsFavoriteModel $favorite */
                $favorite = ContentsFavoriteModel::query()
                    ->where('cid', $cid)
                    ->where('aff', $this->member->aff)->first();
                if (empty($favorite)) {
                    $favorite = ContentsFavoriteModel::create([
                        'aff'        => $this->member->aff,
                        'cid'        => $cid,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    ]);
                    test_assert($favorite , '添加收藏数据失败');
                    jobs([ContentsModel::class, 'incrementFavoriteNum'], [$cid]);
                    //test_assert($favorite->contents()->increment('favorite_num') , '影响收藏统计失败');
                    redis()->sAdd(ContentsFavoriteModel::generateID($this->member->aff) , $cid);
                } else {
                    //test_assert($favorite->contents()->decrement('favorite_num') , '影响收藏统计失败');
                    test_assert($favorite->delete() , '清理收藏数据失败');
                    jobs([ContentsModel::class, 'decrementFavoriteNum'], [$cid]);
                    redis()->sRem(ContentsFavoriteModel::generateID($this->member->aff) , $cid);
                }
            });
            cached('')->clearGroup('contents:favorite:'.$this->member->aff);
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**  */
    public function list_my_favoriteAction(): bool
    {
        try {
            $list = cached('contents:favorite:'.$this->member->aff.'-'.$this->page)
                ->group('contents:favorite:'.$this->member->aff)
                ->fetchPhp(function (){
                    $list = ContentsFavoriteModel::query()
                        ->with([
                            'contents' => function ($query) {
                                $query
                                    ->selectRaw('cid,title,slug,type,created,modified,authorId,commentsNum,allowComment,allowPing,allowFeed,view,fake_view')
                                    ->with(['fields', 'author'])
                                    ->with([
                                        'relationships' => function ($query) {
                                            $query->with('meta');
                                        },
                                    ]);
                            },
                        ])
                        ->where(['aff' => $this->member->aff,])
                        ->forPage($this->page, $this->limit)
                        ->orderByDesc('id')
                        ->get()
                        ->map(function (ContentsFavoriteModel $item) {
                            if (!$item->contents) {
                                return null;
                            }
                            $item->contents->setAttribute('favorite_id', $item->id);
                            return $item->contents;
                        })->filter()->values();
                    $list->each(function (ContentsModel $item) {
                        $item->loadTagWithCategory();
                    });
                    return $list;
                });

            return $this->listJson($list, 'favorite_id');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //弹幕
    public function barrageAction(){
        try {
            $validator = \helper\Validator::make($this->data, [
                'cid' => 'required|numeric|min:1', //文章ID
                'type' => 'required|numeric|enum:1,2',//类型 1文章 2帖子
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cid = $this->data['cid'];
            $type = $this->data['type'];

            if ($type == 1){
                $list = cached("comment-barrage:$cid")
                    ->group("comment-barrage:$cid")
                    ->chinese("文章弹幕")
                    ->fetchJson(function () use ($cid){
                        return CommentsModel::where('cid', $cid)
                            ->selectRaw('`text`')
                            ->where('status', CommentsModel::STATUS_APPROVED)
                            ->orderByDesc('coid')
                            ->limit(500)
                            ->get()
                            ->pluck('text')
                            ->toArray();
                    });
            }else{
                $list = cached("post-barrage:$cid")
                    ->group("post-barrage:$cid")
                    ->chinese("社区弹幕")
                    ->fetchJson(function () use ($cid){
                        return PostCommentModel::selectRaw('comment')
                            ->where('post_id', $cid)
                            ->where('status', PostCommentModel::STATUS_PASS)
                            ->orderByDesc('id')
                            ->limit(500)
                            ->get()
                            ->pluck('comment')
                            ->toArray();
                    });
            }

            return $this->listJson(array_filter($list));
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //短剧详情
    public function skitsAction(){
        try {
            $validator = \helper\Validator::make($this->data, [
                'cid' => 'required|numeric|min:1', //文章ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cid = $this->data['cid'];

            /** @var ContentsModel $cur */
            $cur = cached("content-$cid")
                ->chinese('文章详情')
                ->fetchPhp(function () use ($cid){
                    $cur = ContentsModel::query()
                        ->selectRaw('cid,title,slug,created,modified,`text`,authorId,commentsNum,allowComment,allowPing,allowFeed,favorite_num,view,like_num,view,fake_view')
                        ->where('is_slice', 1)
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('type', ContentsModel::TYPE_SKITS)
                        ->where('created', '<=', time())
                        ->where('cid', $cid)
                        ->first();
                    if (empty($cur)) {
                        throw new RuntimeException('文章不存在');
                    }
                    $cur->load(['fields', 'author']);
                    $cur->loadTagWithCategory();
                    return $cur;
                });
            //$cur->loadMarkdown();
            //寻找sid
            $sid = 0;
            foreach ($cur->fields as $val){
                if ($val['name'] == 'skits'){
                    $sid = $val['int_value'];
                }
            }
            test_assert($sid, '短剧未配置');
            //收藏
            $favorite_id = redis()->sIsMember(ContentsFavoriteModel::generateID($this->member->aff) , $cid);
            $cur->setAttribute('is_favorite', $favorite_id ? 1 : 0);

            //点赞
            $like_id = redis()->sIsMember(ContentsLikeModel::generateID($this->member->aff) , $cid);
            $cur->setAttribute('is_like', $like_id ? 1 : 0);

            $query = ContentsModel::query()->where('is_slice', 1)
                ->selectRaw('cid,title,slug,created,modified,authorId,commentsNum,allowComment,allowPing,allowFeed,type,view,fake_view')
                ->where('status', ContentsModel::STATUS_PUBLISH)
                ->where('app_hide', ContentsModel::APP_HIDE_NO)
                ->where('type', ContentsModel::TYPE_POST)
                ->where('created', '<=', time());
            //版本控制
            if (version_compare($this->version, '2.5.0', '>')){
                /** @var ContentsModel $next */
                $next = cached("content-${cid}-next-new")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        $model = $query->clone()
                            ->with([
                                'relationships' => function ($query) {
                                    $query->with('meta');
                                },
                            ])
                            ->with('fields', 'author')
                            ->where('cid', '>', $cid)
                            ->first();
                        if (!empty($model)){
                            $model->loadTagWithCategory();
                        }
                        return $model;
                    });
                $prev = null;
            }else{
                /** @var ContentsModel $next */
                $next = cached("content-${cid}-next")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        return $query->clone()->where('cid', '>', $cid)->first();
                    });
                $prev = cached("content-${cid}-prev")
                    ->chinese('文章详情')
                    ->fetchPhp( function () use ($cid , $query){
                        return $query->clone()->where('cid', '<', $cid)->orderByDesc('cid')->first();
                    });
            }

            $wxqun = null;
            if (!$cur->fieldValue('hide_wxqun')) {
                $wxqun = CommonService::getAds($this->member,AdsModel::POSITION_QUN,true); // 隐藏微信群
                if ($wxqun && version_compare($this->version, '2.5.0', '>')){
                    $wxqun->img_url = url_image('/upload_01/ads/20240713/2024071317473027633.png');
                }
            }
            $redbag = null;
            if (!$cur->fieldValue('hide_redbag')) {
                $redbag = CommonService::getAds($this->member,AdsModel::POSITION_EMAIL_SUB,true);  // 隐藏红包
                if ($redbag && version_compare($this->version, '2.5.0', '>')){
                    $redbag->img_url = url_image('/upload_01/ads/20240713/2024071317475126591.png');
                }
            }

            $banner = [];
            if (!$cur->fieldValue('hide_banner')) {
                $banner = CommonService::getAds($this->member,AdsModel::POSITION_DETAIL);
            }
            //邮箱订阅
            $email_ad = null;

            $shareText = setting('detail_share_text', '');
            $shareText = str_replace("{title}", $cur->title, $shareText);
            $shareText = str_replace("{id}", $cur->cid, $shareText);

            //top banner
            $top_banner = CommonService::getAds($this->member,AdsModel::POS_SKITS_TOP_BANNER);
            $mid_ad = CommonService::getAds($this->member,AdsModel::POS_SKITS_MID_AD);

            //短剧
            SkitsModel::setWatchUser($this->member);
            $skits = SkitsModel::findById($sid);
            test_assert($skits, '短剧合集不存在');
            EpisodeModel::setWatchUser($this->member);
            $list = EpisodeModel::list($sid, $this->version);
            $list = collect($list)->map(function ($item){
                if ($item->is_pay == 0){
                    $item->play_url = '';
                }
                return $item;
            });
            $skits->list = $list;
            //邮箱是否订阅
            $is_subscribe = EmailSubscribeModel::hasSubscribe($this->member->aff);

            $data = [
                'prev'         => $prev,
                'cur'          => $cur,
                'next'         => $next,
                'banner'       => $banner,
                'qun_ad'       => $wxqun,
                'redbag_ad'    => $redbag,
                'share_text'   => $shareText,
                'notice'       => CommonService::getNotice($this->member, NoticeModel::POS_DETAIL),
                'top_banner'   => $top_banner,
                'mid_ad'       => $mid_ad,
                'email_ad'     => $email_ad,
                'skits'        => $skits,
                'is_subscribe' => $is_subscribe,
                'hot_search' => HotSearchModel::getOne(),//热搜
            ];

            //浏览数记录
            jobs([ContentsModel::class, 'incrByView'], [$cid]);

            return $this->showJson($data);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //短剧详情
    public function bigEventAction(){
        try {
            $validator = \helper\Validator::make($this->data, [
                'cid' => 'required|numeric|min:1', //文章ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cid = $this->data['cid'];

            /** @var ContentsModel $cur */
            $cur = cached("content-$cid")
                ->chinese('文章详情')
                ->fetchPhp(function () use ($cid){
                    $cur = ContentsModel::query()
                        ->selectRaw('cid,title,slug,created,modified,`text`,authorId,commentsNum,allowComment,allowPing,allowFeed,favorite_num,view,like_num,view,fake_view')
                        ->where('is_slice', 1)
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('type', ContentsModel::TYPE_BIG_WENT)
                        ->where('created', '<=', time())
                        ->where('cid', $cid)
                        ->first();
                    if (empty($cur)) {
                        throw new RuntimeException('文章不存在');
                    }
                    $cur->load(['fields', 'author']);
                    $cur->loadTagWithCategory();
                    return $cur;
                });
            test_assert($cur, '文章不存在');
            //$cur->loadMarkdown();
            $cur->setAttribute('is_favorite', 0);
            //寻找bid
            $bid = 0;
            foreach ($cur->fields as $val){
                if ($val['name'] == 'bigEvent'){
                    $bid = $val['int_value'];
                }
            }
            test_assert($bid, '大事件未配置');

            //大事件
            $big_event = BigEventModel::findById($bid);
            test_assert($big_event, '大事件不存在');
            $list = BigRelationModel::list_ids($bid);
            $big_event->list = $list;

            $data = [
                'prev'       => null,
                'cur'        => $cur,
                'next'       => null,
                'banner'     => [],
                'qun_ad'     => null,
                'redbag_ad'  => null,
                'share_text' => '',
                'notice'     => null,
                'big_event'  => $big_event,
            ];

            //浏览数记录
            jobs([ContentsModel::class, 'incrByView'], [$cid]);

            return $this->showJson($data);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function unlockSkitsAction(){
        try {
            $validator = Validator::make($this->data, [
                'skits_id' => 'required|numeric|min:1',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $skitsId = (int)$this->data['skits_id'];
            $service = new \service\SkitsService();
            $service->unlockSkits($this->member, $skitsId);
            return $this->successMsg('解锁成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function unlockEpisodeAction(){
        try {
            $validator = Validator::make($this->data, [
                'episode_id' => 'required|numeric|min:1',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $episodeId = (int)$this->data['episode_id'];
            $service = new \service\SkitsService();
            $res = $service->buyEpisode($this->member, $episodeId);
            return $this->showJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function trigger_likeAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'cid' => 'required|min:1',
            ]);
            $cid = $this->data['cid'];
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            transaction(function () use ($cid){
                /** @var ContentsLikeModel $like */
                $like = ContentsLikeModel::query()
                    ->where('cid', $cid)
                    ->where('aff', $this->member->aff)->first();
                if (empty($like)) {
                    $like = ContentsLikeModel::create([
                        'aff'        => $this->member->aff,
                        'cid'        => $cid,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    ]);
                    test_assert($like , '添加点赞数据失败');
                    jobs([ContentsModel::class, 'incrementLikeNum'], [$cid]);
                    redis()->sAdd(ContentsLikeModel::generateID($this->member->aff) , $cid);
                } else {
                    test_assert($like->delete() , '清理点赞数据失败');
                    jobs([ContentsModel::class, 'decrementLikeNum'], [$cid]);
                    redis()->sRem(ContentsLikeModel::generateID($this->member->aff) , $cid);
                }
            });
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_comments_v1Action(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'cid' => 'required|numeric|min:1',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cid = $this->data['cid'];
            $service = new \service\contentsService();
            $list = $service->list_comment($this->member, $cid, $this->page);
            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_reply_comments_v1Action(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'cid' => 'required|numeric|min:1',
                'coid' => 'required|numeric|min:1',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cid = $this->data['cid'];
            $coid = $this->data['coid'];
            $service = new \service\contentsService();
            $list = $service->list_replys($this->member, $cid, $coid, $this->page, $this->limit);
            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //点赞/取消点赞
    public function like_commentAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'coid' => 'required|numeric|min:1', //评论ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $coid = (int)$this->data['coid'];
            $this->verifyMemberSayRole();
            $service = new \service\contentsService();
            $service->like_comment($this->member, $coid);
            return $this->successMsg("成功");
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /** 往期福利 */
    public function archives_v1Action(): bool
    {
        try {
            $last_ix = $this->last_ix ? intval($this->last_ix) : '(null)';
            $date = $this->data['date'] ?? null;
            $list = cached('contents:archives:new-'.$last_ix.'_' . $date)
                ->chinese('往期福利')
                ->fetchPhp(function () use ($date) {
                    $table = \Yaf\Registry::get('database')->prefix;
                    $fullTable = $table.'contents';
                    return ContentsModel::query()
                        ->when(empty($date), function ($query) {
                            $query->where('created', '<=', time());
                        })
                        ->when($date, function ($query, $value) {
                            $start = strtotime("$value 00:00:00");
                            $end = strtotime("$value 23:59:59");
                            $end = min($end, time());
                            $query->whereBetween('created', [$start, $end]);
                        })
                        ->with('fields','author')
                        ->from('contents', 'contents')
                        ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                        ->where('type', ContentsModel::TYPE_POST)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->forPageBeforeId(50,  $this->last_ix ? intval($this->last_ix) : null,  'created')
                        ->get()
                        ->each(function (ContentsModel $item) {
                            $item->setAttribute('date', $item->created->toDateString());
                            $item->loadTagWithCategory();
                        });
                });
            $last_ix = $list->last() ? strtotime($list->last()->created).'' : '';
            $list = $list->groupBy('date');
            $result = collect([]);
            foreach ($list as $group => $items) {
                $result->push([
                    'group_name' => $group,
                    'items'      => $items,
                ]);
            }

            return $this->showJson(['list' => $result, 'last_ix' => $last_ix]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

}