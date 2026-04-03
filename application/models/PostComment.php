<?php

/**
 * class PostCommentModel
 *
 *
 * @property int $id
 * @property int $post_id 帖子ID
 * @property int $pid 父级 默认0
 * @property int $aff 用户AFF
 * @property int $comment 留言内容
 * @property int $status 0:待审核 1:审核通过 2.未通过
 * @property int $is_read 被回复者是否已读 0未读 1已读
 * @property int $like_num 点赞数
 * @property int $video_num 视频数量
 * @property int $photo_num 图片数量
 * @property string $ipstr IP
 * @property string $cityname 城市名
 * @property int $complain_num 被举报次数
 * @property string $refuse_reason 拒绝通过原因
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_finished 资源是否处理完 0未处理 1已处理
 * @property string $author 评论者昵称
 * @property int $reply_aff 回复对应评论人的aff
 * @property string $ads_url 广告地址
 * @property int $redirect_type 跳转类型  0外部 1内部
 * @property int $sort 排序
 * @property int $admin_id
 * @property int $reply_ct 二级回复数量
 * @property int $sec_parent app回复ID
 * @property int $fix_reply 是否更新二级评论数量
 * @property int $is_top 置顶
 *
 * @property mixed $comments
 * @property mixed $is_like
 *
 * @property ?PostModel $post
 * @property ?MemberModel $user
 * @property ?ManagerModel $manager
 * @property array<PostMediaModel>|\Illuminate\Database\Eloquent\Collection $medias
 *
 * @mixin \Eloquent
 */
class PostCommentModel extends BaseModel
{
    /**
     * @var array|mixed
     */
    protected $table = 'post_comment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'post_id',
        'pid',
        'aff',
        'comment',
        'status',
        'is_read',
        'like_num',
        'video_num',
        'photo_num',
        'ipstr',
        'cityname',
        'complain_num',
        'refuse_reason',
        'created_at',
        'updated_at',
        'is_finished',
        'author',
        'reply_aff',
        'ads_url',
        'redirect_type',
        'sort',
        'admin_id',
        'reply_ct',
        'sec_parent',
        'fix_reply',
        'is_top',
    ];
    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_UNPASS = 2;
    const STATUS_TIPS = [
        self::STATUS_WAIT => '待审核',
        self::STATUS_PASS => '审核通过',
        self::STATUS_UNPASS => '未通过'
    ];
    const FINISH_NO = 0;
    const FINISH_OK = 1;
    const FINISH_TIPS = [
        self::FINISH_NO => '未完成',
        self::FINISH_OK => '完成'
    ];
    const REDIRECT_TYPE_OUT = 0;
    const REDIRECT_TYPE_IN = 1;
    const REDIRECT_TYPE_TIPS = [
        self::REDIRECT_TYPE_OUT => '外部跳转',
        self::REDIRECT_TYPE_IN => '内部跳转'
    ];

    const TOP_NO = 0;
    const TOP_OK = 1;
    const TOP_TIPS = [
        self::TOP_NO => '否',
        self::TOP_OK => '是'
    ];

    const SELECT_LIST_RAW = 'id,post_id,pid,aff,comment,like_num,created_at,ads_url,redirect_type,sort,reply_ct,reply_aff,sec_parent,is_top,sec_parent,author';

    const POST_COMMENT_DETAIL_KEY = 'post:comment:detail:%s';
    const POST_COMMENT_LIST_FIRST_KEY = 'post:comment:list:first:%s:%s:%s';
    const POST_COMMENT_LIST_SECOND_KEY = 'post:comment:list:second:%s:%s:%s:%s';
    const POST_COMMENT_LIST_DETAIL_KEY = 'post:comment:list:detail:%s:%s:%s:%s';
    // 特殊GROUP
    const CREATE_POST_COMMENT_CLEAR_GROUP = 'create_post_commemnt_clear_%s';
    const CREATE_COMMENT_COMMENT_CLEAR_GROUP = 'create_comment_commemnt_clear_%s';
    const POST_COMMENT_DETAIL_GROUP_KEY = 'post_comment_detail';
    const POST_COMMENT_LIST_FIRST_GROUP_KEY = 'post_comment_list_first';
    const POST_COMMENT_LIST_SECOND_GROUP_KEY = 'post_comment_list_second';
    const POST_COMMENT_LIST_DETAIL_GROUP_KEY = 'post_comment_list_detail';
    const POST_COMMENT_LIMIT_KEY = 'post:comment:limit:%s';
    const POST_COMMENT_LIMIT_SECOND = 60;
    const POST_COMMENT_LIMIT_NUM = 10;

    // 评论列表
    const CK_POST_COM_LIST = 'ck:post:com:list:%s:%s:%s';
    const CK_POST_COM_FIRST_IDS_LIST = 'ck:post:com:first:ids:list:%s';
    const CK_POST_FIRST_SEC_LIST = 'ck:post:first:sec:list:%s';
    const GP_POST_COM_LIST = 'gp:post:com:list';
    const CN_POST_COM_LIST = '社区评论一级列表';

    // 评论列表
    const CK_POST_COM_REPLY_LIST = 'ck:post:com:reply:list:%s:%s:%s:%s';
    const GP_POST_COM_REPLY_LIST = 'gp:post:com:reply:list';
    const CN_POST_COM_REPLY_LIST = '社区评评论二级列表';

    protected $hidden = ['admin_id'];
    protected $appends = [
        'comments',
        'is_like'
    ];

    public function post(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PostModel::class, 'id', 'post_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function reply_member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'reply_aff');
    }

    public function medias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostMediaModel::class,'pid','id');
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ManagerModel::class, 'uid', 'admin_id');
    }

    public function getCommentsAttribute($key)
    {
        return $this->attributes['comments'] ?? [];
    }

    //是否点赞
    public function getIsLikeAttribute()
    {
        static $ary = null;
        if (APP_MODULE == 'staff') {
            return 1;
        }
        if (isset($this->attributes['is_like'])) {
            return $this->attributes['is_like'];
        }

        $aff = self::$watchUser ? self::$watchUser->aff : 0;
        if (empty($aff)) {
            return 0;
        }
        $id = $this->attributes['id'] ?? 0;

        $rk = sprintf(PostCommentsLikeModel::POST_COMMENTS_LIKE, $aff);
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (empty($ary) || !is_array($ary) || !in_array($id, $ary)) {
            return 0;
        }

        return 1;
    }

    public static function incrementLikeNum($id, $num = 1)
    {
        return self::where('id', $id)->increment('like_num', $num);
    }

    public static function decrementLikeNum($id, $num = 1)
    {
        return self::where('id', $id)->decrement('like_num', $num);
    }

//    public function getUserAttribute(): MemberModel
//    {
//        static $members = [];
//        if (!isset($members[$this->aff])) {
//            $member = MemberModel::firstAff($this->aff);
//            if (empty($member)) {
//                $member = MemberModel::firstAff(1);
//            }
//            //把评论表的名字替换进去
//            if ($this->getAttribute('author')){
//                $member->nickname = $this->getAttribute('author');
//            }
//            $members[$this->aff] = $member;
//        }
//        return $members[$this->aff];
//    }

    public static function getCommentById($aff, $commentId)
    {
        $likes = UserCommunityLikeModel::listLikeCommentIds($aff);
        $cacheKey = sprintf(self::POST_COMMENT_DETAIL_KEY, $commentId);
        $comment = cached($cacheKey)
            ->group(self::POST_COMMENT_DETAIL_GROUP_KEY)
            ->fetchPhp(function () use ($commentId) {
                return self::where('id', $commentId)
                    ->where('status', self::STATUS_PASS)
                    ->where('is_finished', self::FINISH_OK)
                    ->first();
        });
        if ($comment) {
            $comment->is_like = in_array($comment->id, $likes) ? 1 : 0;
        }
        return $comment;
    }

    protected static function listCommentsByFirst($postId, $offset, $limit)
    {
        $cacheKey = sprintf(self::POST_COMMENT_LIST_FIRST_KEY, $postId, $offset, $limit);
        $group = $offset == 0 ? sprintf(self::CREATE_POST_COMMENT_CLEAR_GROUP, $postId) : self::POST_COMMENT_LIST_FIRST_GROUP_KEY;
        return cached($cacheKey)
            ->chinese('社区-一级评论')
            ->group($group)
            ->fetchPhp(function () use ($postId, $offset, $limit) {
                return self::with([
                    'user',
                    'medias' => function($query){
                        $query->where('relate_type', PostMediaModel::TYPE_RELATE_COMMENT)
                            ->where('status', PostMediaModel::STATUS_OK);
                    }
                ])
                    ->where('post_id', $postId)
                    ->where('status', self::STATUS_PASS)
                    ->where('is_finished', self::FINISH_OK)
                    ->where('pid', 0)
                    ->offset($offset)
                    ->limit($limit)
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            });
    }

    protected static function listCommentsBySecond($pid, $postId, $offset, $limit)
    {
        $cacheKey = sprintf(self::POST_COMMENT_LIST_SECOND_KEY, $pid, $postId, $offset, $limit);
        $group = $offset == 0 ? sprintf(self::CREATE_COMMENT_COMMENT_CLEAR_GROUP, $pid) : self::POST_COMMENT_LIST_SECOND_GROUP_KEY;
        return cached($cacheKey)
            ->chinese('社区-二级评论-1')
            ->group($group)
            ->fetchPhp(function () use ($pid, $postId, $offset, $limit) {
                return self::with('user')
                    ->where('post_id', $postId)
                    ->where('status', self::STATUS_PASS)
                    ->where('is_finished', self::FINISH_OK)
                    ->where('pid', $pid)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
            });
    }

    public static function listCommentsByPostId(\MemberModel $member, $postId, $authorAff, $offset, $limit,$version)
    {
        $aff = $member->aff;
        //$likes = UserCommunityLikeModel::listLikeCommentIds($aff);
        $comments = self::listCommentsByFirst($postId, $offset, $limit);
        foreach ($comments as $v) {
            //$v->is_like = in_array($v->id, $likes) ? 1 : 0;
            $v->is_like = 0;
            $v->is_landlord = (int)$v->aff === (int)$authorAff ? 1 : 0;
            $v->comments = self::listCommentsBySecond($v->id, $postId, 0, 3);
            foreach ($v->comments as $v1) {
//                $v1->is_like = in_array($v1->id, $likes) ? 1 : 0;
                $v1->is_like = 0;
                $v1->is_landlord = (int)$v1->aff === (int)$authorAff ? 1 : 0;
            }
            foreach ($v->medias as $v2){
                $v2->ads_url = $v->ads_url ?: '';
                $v2->redirect_type = $v->redirect_type;
                $v2->type = $v->ads_url ? 3 : $v2->type;
            }
            if (in_array($member->oauth_type,[MemberModel::TYPE_MACOS,MemberModel::TYPE_WINDOWS])){
                if (version_compare($version,'1.3.0','<') && !$v->comment){
                    $v->comment = '1.3.0 以下 【图片/视频评论】请升级最新版本查看';
                }
            }else{
                if (version_compare($version,'2.0.0','<') && !$v->comment){
                    $v->comment = '2.0.0 以下 【图片/视频评论】请升级最新版本查看';
                }
            }
        }
        return $comments;
    }

    public static function listCommentsByDetail($pid, $postId, $offset, $limit)
    {
        $cacheKey = sprintf(self::POST_COMMENT_LIST_DETAIL_KEY, $pid, $postId, $offset, $limit);
        $group = $offset == 0 ? sprintf(self::CREATE_COMMENT_COMMENT_CLEAR_GROUP, $pid) : self::POST_COMMENT_LIST_DETAIL_GROUP_KEY;
        return cached($cacheKey)
            ->chinese('社区-二级评论-2')
            ->group($group)
            ->fetchPhp(function () use ($pid, $postId, $offset, $limit) {
                return self::with('user')
                    ->where('post_id', $postId)
                    ->where('status', self::STATUS_PASS)
                    ->where('is_finished', self::FINISH_OK)
                    ->where('pid', $pid)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
        });
    }

    public static function listCommentsByCommentId($aff, $pid, $postId, $authorAff, $offset, $limit)
    {
        $likes = UserCommunityLikeModel::listLikeCommentIds($aff);
        $comments = self::listCommentsByDetail($pid, $postId, $offset, $limit);
        foreach ($comments as $v) {
            $v->is_like = in_array($v->id, $likes) ? 1 : 0;
            $v->is_landlord = (int)$v->aff === (int)$authorAff ? 1 : 0;
        }
        return $comments;
    }

    public static function listMyComments($aff, $page, $limit){
        return \PostCommentModel::query()
            ->selectRaw('aff,post_id,id as coid,post_id as cid, 0 as reply_author,0 as reply_aff,0 as thumb,0 as author,0 as app_aff,0 as authorId,0 as ownerId,comment as `text`,0 as type,0 as status,0 as parent,0 as created')
            ->with([
                'post' => function ($q) {
                    return $q->selectRaw('id,id as cid,title,created_at as created,0 as slug,0 as modified,0 as authorId,0 as commentsNum,0 as allowComment,0 as allowPing,0 as allowFeed');
                },
                'member' => function ($q) {
                    return $q->selectRaw('aff,nickname,thumb');
                }
            ])
            ->where('aff', $aff)
            ->orWhere('reply_aff',$aff)
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get()
            ->map(function ($item){
                if (!$item->text){
                    $item->text = '[图文评论]';
                }
                $item->author = $item->member->nickname;
                $item->thumb = $item->member->thumb;
                $item->contents = $item->post;
                if (!$item->contents){
                    $item->contents = new StdClass();
                    $item->contents->title = '此帖子已删除';
                    $item->contents->created = 0;
                }
                $item->contents->fields = [];
                $item->reply = [];
                $item->makeHidden(['user','post','member','comments','medias','is_like']);

                return $item;
            });
    }

    public static function clearCache($model)
    {
        //一级评论清理
        cached('')->clearGroup(sprintf(self::CREATE_POST_COMMENT_CLEAR_GROUP, $model->post_id), sprintf(self::CREATE_COMMENT_COMMENT_CLEAR_GROUP, $model->pid));
    }

    // 清理帖子评论首页
    public static function clearCacheWhenCreatePostComment($postId)
    {
        // 发布帖子评论时
        // 清理帖子详情
        PostModel::clearDetailCache($postId);
        // 清理帖子第一页评论
        $group = sprintf(self::CREATE_POST_COMMENT_CLEAR_GROUP, $postId);
        cached('')->clearGroup($group);
        // 清理第一层级
    }

    public static function clearCacheWhenCreateComment($commentId)
    {
        // 发布评论时
        // 清理二级评论列表
        // 清理二级评论详情列表
        $group = sprintf(self::CREATE_COMMENT_COMMENT_CLEAR_GROUP, $commentId);
        cached('')->clearGroup($group);
    }

    public static function list_first($postId,  $authorAff, $page, $limit){
        $key = sprintf(self::CK_POST_COM_LIST, $postId, $page, $limit);
        return cached($key)
            ->group(self::GP_POST_COM_LIST)
            ->chinese(self::CN_POST_COM_LIST)
            ->fetchPhp(function () use ($postId, $authorAff, $page, $limit){
                return self::where('post_id', $postId)
                    ->with(['member',
                            'medias' => function($query){
                                $query->where('relate_type', PostMediaModel::TYPE_RELATE_COMMENT)
                                    ->where('status', PostMediaModel::STATUS_OK);
                            }])
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', self::STATUS_PASS)
                    ->where('pid', 0)
                    ->orderByDesc('is_top')
                    ->orderByDesc('reply_ct')
                    ->forPage($page, $limit)
                    ->get()
                    ->each(function (PostCommentModel $item) use ($authorAff) {
                        if ($item->is_top == self::TOP_OK){
                            //格式化a标签
                            $item->comment = CommentsModel::preg_match_a($item->comment);
                        }
                        $item->is_landlord = $item->aff === (int)$authorAff ? 1 : 0;
                    });
            },rand(1800, 3600));
    }

    public static function fir_sec_list($postId, $commentId, $authorAff){
        $key = sprintf(self::CK_POST_FIRST_SEC_LIST, $commentId);
        return cached($key)
            ->group(self::GP_POST_COM_LIST)
            ->chinese(self::CN_POST_COM_LIST)
            ->fetchPhp(function () use ($postId, $commentId, $authorAff){
                return self::with(['member','reply_member'])
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('post_id', $postId)
                    ->where('pid', $commentId)
                    ->where('status', PostCommentModel::STATUS_PASS)
                    ->orderByDesc('reply_ct')
                    ->limit(4)
                    ->get()->each(function (PostCommentModel $sec_item) use ($authorAff){
                        $sec_item->is_landlord = $sec_item->aff === (int)$authorAff ? 1 : 0;
                        if (!$sec_item->sec_parent){
                            $sec_item->setRelation('reply_member', null);
                        }
                    });
            }, rand(1800, 3600));
    }


    public static function list_first_ids($postId){
        $key = sprintf(self::CK_POST_COM_FIRST_IDS_LIST, $postId);
        return cached($key)
            ->group(self::GP_POST_COM_LIST)
            ->chinese(self::CN_POST_COM_LIST)
            ->fetchJson(function () use ($postId){
                return self::where('post_id', $postId)
                    ->where('status', self::STATUS_PASS)
                    ->where('pid', 0)
                    ->orderByDesc('is_top')
                    ->orderByDesc('reply_ct')
                    ->limit(30)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            },1800);
    }

    public static function list_comments($postId, $ids, $authorAff, $page, $limit){
        $key = sprintf(self::CK_POST_COM_LIST, $postId, $page, $limit);
        return cached($key)
            ->group(self::GP_POST_COM_LIST)
            ->chinese(self::CN_POST_COM_LIST)
            ->fetchPhp(function () use ($postId, $ids, $authorAff, $page, $limit){
                return self::where('post_id', $postId)
                    ->with(['member',
                            'medias' => function($query){
                                $query->where('relate_type', PostMediaModel::TYPE_RELATE_COMMENT)
                                    ->where('status', PostMediaModel::STATUS_OK);
                            }])
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', self::STATUS_PASS)
                    ->whereNotIn('id', $ids)
                    ->where('pid', 0)
                    ->forPage($page - 1, $limit)
                    ->get()
                    ->each(function (PostCommentModel $item) use($authorAff) {
                        $item->setRelation('reply_member', null);
                        $item->is_landlord = $item->aff === (int)$authorAff ? 1 : 0;
                    });
            },1800);
    }

    public static function list_replys($postId, $id, $authorAff, $page, $limit){
        $key = sprintf(self::CK_POST_COM_REPLY_LIST, $postId, $id, $page, $limit);
        return cached($key)
            ->group(self::GP_POST_COM_REPLY_LIST)
            ->chinese(self::CN_POST_COM_REPLY_LIST)
            ->fetchPhp(function () use ($postId, $id, $authorAff, $page, $limit){
                return self::where('post_id', $postId)
                    ->with('member')
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', self::STATUS_PASS)
                    ->where('pid', $id)
                    ->orderByDesc('reply_ct')
                    ->forPage($page, $limit)
                    ->get()
                    ->each(function (PostCommentModel $item) use ($authorAff) {
                        $item->is_landlord = $item->aff === (int)$authorAff ? 1 : 0;
                        //回复
                        $tmp = null;
                        if ($item->sec_parent){
                            $replay = self::where('id', $item->sec_parent)
                                ->selectRaw(self::SELECT_LIST_RAW)
                                ->where('status', self::STATUS_PASS)
                                ->first();
                            $tmp = [
                                'id' => $replay->id,
                                'aff' => $replay->aff,
                                'comment' => $replay->comment,
                                'nickname' => $replay->author,
                                'is_landlord' => (int)$replay->aff === (int)$authorAff ? 1 : 0
                            ];
                        }
                        $item->setAttribute('reply', $tmp);
                    });
            },1800);
    }
}