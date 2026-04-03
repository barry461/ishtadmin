<?php

/**
 * class CommentsModel
 *
 * @property int $coid
 * @property int $cid
 * @property int $created
 * @property string $author
 * @property int $authorId
 * @property int $ownerId
 * @property string $mail
 * @property string $url
 * @property string $ip
 * @property int $app_aff
 * @property int $reply_aff
 * @property int $reply_author
 * @property string $agent
 * @property string $text utf7mb4
 * @property string $type
 * @property string $status
 * @property string $thumb
 * @property int $parent
 * @property int $admin_id
 * @property int $reply_ct
 * @property int $like_num
 * @property int $is_top
 * @property int $sec_parent 二级评论ID
 * @property int $fix_reply
 *
 * @property MemberModel $member app的用户
 * @property MemberModel $reply_member app的用户
 * @property ContentsModel $contents
 * @property ManagerModel $manager
 *
 * @mixin \Eloquent
 */
class CommentsModel extends BaseModel
{

    protected $table = "comments";

    protected $primaryKey = 'coid';

    protected $fillable
        = [
            'cid',
            'created',
            'author',
            'authorId',
            'ownerId',
            'mail',
            'app_aff',
            'reply_author',
            'reply_aff',
            'url',
            'ip',
            'agent',
            'text',
            'type',
            'status',
            'parent',
            'thumb',
            'admin_id',
            'reply_ct',
            'like_num',
            'is_top',
            'sec_parent',
            'fix_reply',
        ];

    protected $guarded = 'coid';

    public $timestamps = false;

    const STATUS_APPROVED = 'approved';
    const STATUS_WAITING = 'waiting';
    const STATUS_SPAM = 'spam';
    const STATUS_FILTER = 'filter';
    const STATUS
        = [
            self::STATUS_APPROVED => 'approved',
            self::STATUS_WAITING => 'waiting',
            self::STATUS_SPAM => 'spam',
            self::STATUS_FILTER => 'filter',
        ];

    const STATUS_TIPS = [
        self::STATUS_APPROVED => '通过',
        self::STATUS_WAITING => '待审核',
        self::STATUS_SPAM => '拒绝',
        self::STATUS_FILTER => '过滤',
    ];

    const TOP_NO = 0;
    const TOP_OK = 1;
    const TOP_TIPS = [
        self::TOP_NO => '否',
        self::TOP_OK => '是',
    ];

    const TYPE_COMMENT = 'comment';
    const TYPE
        = [
            self::TYPE_COMMENT => 'comment',
        ];

    protected $dateFormat = 'U';
    const UPDATED_AT = false;
    const CREATED_AT = 'created';
    // 移除 casts 中的日期转换，改用访问器处理避免 Carbon 依赖
    protected $casts = [];

    /**
     * 获取创建时间 - 使用原生 PHP 避免 Carbon 依赖
     */
    public function getCreatedAttribute($value)
    {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int) $value);
        }
        return $value;
    }

    const SELECT_LIST_RAW = 'coid,cid,thumb,reply_author,author,authorId,ownerId,`text`,type,status,parent,created,reply_ct,like_num,is_top,sec_parent,app_aff,reply_aff';

    // 评论列表
    const CK_CON_COM_LIST = 'ck:con:com:list:%s:%s:%s';
    const CK_CON_COM_FIRST_IDS_LIST = 'ck:con:com:first:ids:list:%s';
    const CK_CON_FIRST_SEC_LIST = 'ck:con:first:sec:list:%s';
    const GP_CON_COM_LIST = 'gp:con:com:list';
    const CN_CON_COM_LIST = '文章评论一级列表';

    /** 前台 Home 评论列表缓存组（Comments/commentAction 使用，审核/删除后必须清理） */
    const GP_LIST_COMMENT_LIST = 'list-comment-list';

    // 评论列表
    const CK_CON_COM_REPLY_LIST = 'ck:con:com:reply:list:%s:%s:%s:%s';
    const GP_CON_COM_REPLY_LIST = 'gp:con:com:reply:list';
    const CN_CON_COM_REPLY_LIST = '文章评论二级列表';

    protected $appends = ['is_like'];

    public function member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'app_aff')->withDefault([
            'uid' => 0,
            'aff' => 0,
            'nickname' => '',
            'is_set_password' => 0,
            'new_user' => 0,
            'tag_list' => '',
            'vip_str' => '',
            'is_follow' => 0,
            'vip_bg' => '',
            'thumb_bg' => '',
            'is_official' => 0,
            'thumb' => '',
        ]);
    }

    public function reply_member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'reply_aff')->withDefault([
            'uid' => 0,
            'aff' => 0,
            'nickname' => '',
            'is_set_password' => 0,
            'new_user' => 0,
            'tag_list' => '',
            'vip_str' => '',
            'is_follow' => 0,
            'vip_bg' => '',
            'thumb_bg' => '',
            'is_official' => 0,
            'thumb' => '',
        ]);
    }

    public function contents(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentsModel::class, 'cid', 'cid');
    }

    public function reply(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent', 'coid');
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ManagerModel::class, 'uid', 'admin_id');
    }

    public function getThumbAttribute(): string
    {
        $thumb = $this->attributes['thumb'] ?? '';
        if (empty($thumb)) {
            // 优先使用外观中配置的评论头像，未配置则用默认头像
            $thumb = options('comment_avatar') ?: DEFAULT_THUMB;
        }
        // 若已是完整 URL（如用户上传的评论头像地址），直接返回，避免被 url_image 替换成 CDN 域名
        if (strpos($thumb, '://') !== false) {
            return $thumb;
        }
        return url_image($thumb);
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
        $coid = $this->attributes['coid'] ?? 0;

        $rk = sprintf(CommentsLikeModel::CONTENTS_COMMENTS_LIKE, $aff);
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (empty($ary) || !is_array($ary) || !in_array($coid, $ary)) {
            return 0;
        }

        return 1;
    }

    public static function incrementLikeNum($coid, $num = 1)
    {
        return self::where('coid', $coid)->increment('like_num', $num);
    }

    public static function decrementLikeNum($coid, $num = 1)
    {
        return self::where('coid', $coid)->decrement('like_num', $num);
    }

    /**
     * 获取评论总数
     * @return int
     */
    public static function getTotalCount(): int
    {
        return self::count();
    }

    /**
     * 获取最近的评论列表
     * @param int $limit 返回数量,默认10条
     * @return \Illuminate\Support\Collection
     */
    public static function getRecentComments(int $limit = 10)
    {
        return self::query()
            ->orderByDesc('created')
            ->limit($limit)
            ->get(['coid', 'author', 'text', 'created', "cid"])
            ->map(function ($item) {
                return [
                    'coid' => $item->coid,
                    'date' => date('m-d', $item->getRawOriginal('created')),
                    'author' => $item->author,
                    'content' => mb_substr(strip_tags($item->text), 0, 50),
                    'url' => trim(options('siteUrl'), '/') . '/archives/' . $item->cid . '/',
                ];
            });
    }

    public static function list_first($cid, $page, $limit)
    {
        $key = sprintf(self::CK_CON_COM_LIST, $cid, $page, $limit);
        return cached($key)
            ->group(self::GP_CON_COM_LIST)
            ->chinese(self::CN_CON_COM_LIST)
            ->fetchPhp(function () use ($cid, $page, $limit) {
                return self::where('cid', $cid)
                    ->with('member')
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->where('parent', 0)
                    ->where('created', '<=', time())
                    ->orderByDesc('is_top')
                    ->orderByDesc('reply_ct')
                    ->orderByDesc('created')
                    ->forPage($page, $limit)
                    ->get()
                    ->each(function (CommentsModel $item) {
                        if ($item->app_aff == 0) {
                            $item->member->nickname = mb_substr($item->author, 0, 6);
                            $item->member->thumb = $item->thumb;
                            if ($item->is_top == CommentsModel::TOP_OK) {
                                $item->member->is_official = 1;
                            }
                        }
                        //格式化a标签
                        if ($item->is_top == CommentsModel::TOP_OK) {
                            $item->text = self::preg_match_a($item->text);
                        }
                    });
            }, rand(1800, 3600));
    }

    public static function fir_sec_comment($cid, $coid)
    {
        $key = sprintf(self::CK_CON_FIRST_SEC_LIST, $coid);
        return cached($key)
            ->group(self::GP_CON_COM_LIST)
            ->chinese(self::CN_CON_COM_LIST)
            ->fetchPhp(function () use ($cid, $coid) {
                return self::with(['member', 'reply_member'])
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('cid', $cid)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->where('parent', $coid)
                    ->orderByDesc('reply_ct')
                    ->limit(4)
                    ->get()->each(function (CommentsModel $item) {
                        if ($item->app_aff == 0) {
                            $item->member->nickname = mb_substr($item->author, 0, 6);
                            $item->member->thumb = $item->thumb;
                        }
                        if (!$item->sec_parent) {
                            $item->setRelation('reply_member', null);
                        } else {
                            if ($item->reply_aff == 0) {
                                $item->reply_member->nickname = $item->reply_author;
                                $item->reply_member->thumb = $item->thumb;
                            }
                        }
                    });
            }, rand(1800, 3600));
    }


    public static function list_first_ids($cid)
    {
        $key = sprintf(self::CK_CON_COM_FIRST_IDS_LIST, $cid);
        return cached($key)
            ->group(self::GP_CON_COM_LIST)
            ->chinese(self::CN_CON_COM_LIST)
            ->fetchJson(function () use ($cid) {
                return self::where('cid', $cid)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->where('parent', 0)
                    ->where('created', '<=', time())
                    ->orderByDesc('is_top')
                    ->orderByDesc('reply_ct')
                    ->orderByDesc('created')
                    ->limit(30)
                    ->get()
                    ->pluck('coid')
                    ->toArray();
            }, 1800);
    }

    public static function list_comments($cid, $coids, $page, $limit)
    {
        $key = sprintf(self::CK_CON_COM_LIST, $cid, $page, $limit);
        return cached($key)
            ->group(self::GP_CON_COM_LIST)
            ->chinese(self::CN_CON_COM_LIST)
            ->fetchPhp(function () use ($cid, $coids, $page, $limit) {
                return CommentsModel::where('cid', $cid)
                    ->with('member')
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->whereNotIn('coid', $coids)
                    ->where('parent', 0)
                    ->where('created', '<=', time())
                    ->forPage($page - 1, $limit)
                    ->orderBy('created')
                    ->get()
                    ->each(function (CommentsModel $item) {
                        if ($item->app_aff == 0) {
                            $item->member->nickname = mb_substr($item->author, 0, 6);
                            $item->member->thumb = $item->thumb;
                            if ($item->is_top == CommentsModel::TOP_OK) {
                                $item->member->is_official = 1;
                            }
                        }
                        $item->reply_member = null;
                    });
            }, 1800);
    }

    public static function list_replys($cid, $coid, $page, $limit)
    {
        $key = sprintf(self::CK_CON_COM_REPLY_LIST, $cid, $coid, $page, $limit);
        return cached($key)
            ->group(self::GP_CON_COM_REPLY_LIST)
            ->chinese(self::CN_CON_COM_REPLY_LIST)
            ->fetchPhp(function () use ($cid, $coid, $page, $limit) {
                return CommentsModel::where('cid', $cid)
                    ->with('member')
                    ->selectRaw(self::SELECT_LIST_RAW)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->where('parent', $coid)
                    ->orderBy('created')
                    ->forPage($page, $limit)
                    ->get()
                    ->each(function (CommentsModel $item) {
                        if ($item->app_aff == 0) {
                            $item->member->nickname = mb_substr($item->author, 0, 6);
                            $item->member->thumb = $item->thumb;
                        }
                        //回复
                        $tmp = null;
                        if ($item->sec_parent) {
                            $reply_comment = self::where('coid', $item->sec_parent)
                                ->selectRaw(self::SELECT_LIST_RAW)
                                ->where('status', CommentsModel::STATUS_APPROVED)
                                ->first();
                            $tmp = [
                                'coid' => $reply_comment->coid,
                                'aff' => $reply_comment->app_aff,
                                'text' => $reply_comment->text,
                                'nickname' => $reply_comment->author,
                            ];
                        }
                        $item->setAttribute('reply', $tmp);
                        $item->setAttribute('is_owner', $item->authorId == $item->ownerId ? 1 : 0);
                    });
            }, 1800);
    }

    public static function preg_match_a($text)
    {
        $reg1 = "/<a .*?>.*?<\/a>/";
        //这个存放的就是正则匹配出来的所有《a》标签数组
        preg_match_all($reg1, $text, $arr);
        collect($arr[0])->map(function ($item) use (&$text) {
            $reg2 = "/href=\"([^\"]+)/";
            preg_match_all($reg2, $item, $href);
            //拿出《a》标签的内容
            $reg3 = "/>(.*)<\/a>/";
            preg_match_all($reg3, $item, $content);
            $new_str = $content[1][0] . $href[1][0];
            if (trim($content[1][0]) == trim($href[1][0])) {
                $new_str = $href[1][0];
            }
            $text = str_replace($item, $new_str, $text);
        });
        return $text;
    }

    public static function delByIp($ip)
    {
        CommentsModel::useWritePdo()
            ->where('ip', 'like', $ip . '%')
            ->chunkById(50, function ($items) {
                collect($items)->each(function (CommentsModel $item) {
                    $item->delete();
                });
            });
    }

    /**
     * @description 获取开始和结束IP
     * @param $ip
     * @param string $subnetMask
     * @return array
     */
    public static function getIPRange($ip, string $subnetMask = '255.255.255.0')
    {
        $ipLong = ip2long($ip);
        $maskLong = ip2long($subnetMask);
        // 网络地址（起始地址）
        $network = $ipLong & $maskLong;
        // 广播地址（结束地址）
        $broadcast = $network | (~$maskLong);

        return [
            'start' => long2ip($network),
            'end' => long2ip($broadcast)
        ];
    }

    public static function generateIPRange($startIP, $endIP)
    {
        $start = ip2long($startIP);
        $end = ip2long($endIP);

        if ($start === false || $end === false) {
            throw new InvalidArgumentException("Invalid IP address provided.");
        }

        if ($start > $end) {
            throw new InvalidArgumentException("Start IP must be less than or equal to End IP.");
        }

        for ($current = $start; $current <= $end; $current++) {
            yield long2ip($current);
        }
    }

    /**
     * 获取后台评论列表 (优雅的扁平化参数)
     * @param array $params 扁平化参数: status, cid, keyword, author, ip, date_from, date_to
     * @param int $limit
     * @param int $offset
     * @return array [$list, $total]
     */
    public static function getPageList(array $params, int $limit, int $offset)
    {
        $query = self::query()->with(['contents', 'member', 'manager']);

        // 状态筛选
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // 文章ID筛选
        if (!empty($params['cid'])) {
            $query->where('cid', $params['cid']);
        }

        // 关键词搜索 (评论内容)
        if (!empty($params['keyword'])) {
            $query->where('text', 'like', '%' . $params['keyword'] . '%');
        }

        // 作者搜索
        if (!empty($params['author'])) {
            $query->where('author', 'like', '%' . $params['author'] . '%');
        }

        // IP筛选
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', $params['ip'] . '%');
        }

        // 日期范围
        if (!empty($params['date_from'])) {
            $query->where('created', '>=', strtotime($params['date_from']));
        }
        if (!empty($params['date_to'])) {
            $query->where('created', '<=', strtotime($params['date_to'] . ' 23:59:59'));
        }

        // 排序
        $orderBy = $params['order_by'] ?? 'created';
        $orderDir = $params['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        $total = $query->count();
        $list = $query->limit($limit)->offset($offset)->get()->map(function ($item) {
            // 格式化时间，避免 Carbon 依赖问题
            $createdTs = $item->getRawOriginal('created');
            $item->created_str = $createdTs ? date('Y-m-d H:i:s', $createdTs) : '';

            // 后台列表头像：无用户头像时使用外观配置的评论头像，保证序列化后 thumb 为完整 URL
            $item->thumb = $item->thumb;

            // 添加关联数据
            $item->content_title = $item->contents ? $item->contents->title : '';
            $item->content_preview_url = $item->contents ? rtrim(options('siteUrl'), '/') . $item->contents->url() : '';
            $item->member_nickname = $item->member ? $item->member->nickname : '';
            $item->admin_name = $item->manager ? $item->manager->username : '';
            $item->status_str = self::STATUS_TIPS[$item->status] ?? '未知';
            $item->is_top_str = self::TOP_TIPS[$item->is_top] ?? '否';

            return $item;
        });

        return [$list, $total];
    }

    /**
     * 批量审核通过
     * @param array $ids 评论ID数组
     * @param int $adminId 管理员ID
     * @return bool
     */
    public static function batchApprove(array $ids, int $adminId)
    {
        return self::useWritePdo()
            ->with('contents')
            ->whereIn('coid', $ids)
            ->get()
            ->each(function (CommentsModel $item) use ($adminId) {
                // 只有待审核或过滤状态可以通过
                if (!in_array($item->status, [self::STATUS_WAITING, self::STATUS_FILTER])) {
                    return;
                }
                $item->update([
                    'status' => self::STATUS_APPROVED,
                    'admin_id' => $adminId
                ]);
                // 增加未读回复数
                if ($item->app_aff) {
                    MemberModel::where('aff', $item->app_aff)->increment('unread_reply');
                }
                // 增加文章评论数
                $item->contents->increment('commentsNum');
                // 清理缓存，包含前台 Home 评论列表（list-comment-list）
                cached('')->clearGroup(self::GP_CON_COM_LIST);
                cached('')->clearGroup(self::GP_CON_COM_REPLY_LIST);
                cached('')->clearGroup(self::GP_LIST_COMMENT_LIST);
            }) !== false;
    }

    /**
     * 批量删除
     * @param array $ids 评论ID数组
     * @return bool
     */
    public static function batchDelete(array $ids)
    {
        return self::useWritePdo()
            ->with('contents')
            ->whereIn('coid', $ids)
            ->get()
            ->each(function (CommentsModel $item) {
                // 如果是已通过的评论,需要减少文章评论数
                if ($item->status == self::STATUS_APPROVED) {
                    $item->contents->decrement('commentsNum');
                }
                // 清理缓存，包含前台 Home 评论列表
                cached('')->clearGroup(self::GP_CON_COM_LIST);
                cached('')->clearGroup(self::GP_CON_COM_REPLY_LIST);
                cached('')->clearGroup(self::GP_LIST_COMMENT_LIST);
                $item->delete();
            }) !== false;
    }

    /**
     * 批量更新状态
     * @param array $ids 评论ID数组
     * @param string $status 目标状态
     * @param int $adminId 管理员ID
     * @return bool
     */
    public static function batchUpdateStatus(array $ids, string $status, int $adminId)
    {
        return self::useWritePdo()
            ->with('contents')
            ->whereIn('coid', $ids)
            ->get()
            ->each(function (CommentsModel $item) use ($status, $adminId) {
                // 如果从已通过变为其他状态,减少评论数
                if ($item->status == self::STATUS_APPROVED && $status != self::STATUS_APPROVED) {
                    $item->contents->decrement('commentsNum');
                }
                // 如果从其他状态变为已通过,增加评论数
                if ($item->status != self::STATUS_APPROVED && $status == self::STATUS_APPROVED) {
                    $item->contents->increment('commentsNum');
                }

                $item->status = $status;
                $item->admin_id = $adminId;
                $item->save();

                // 清理缓存，包含前台 Home 评论列表
                cached('')->clearGroup(self::GP_CON_COM_LIST);
                cached('')->clearGroup(self::GP_CON_COM_REPLY_LIST);
                cached('')->clearGroup(self::GP_LIST_COMMENT_LIST);
            }) !== false;
    }

    /**
     * 删除相同IP的评论
     * @param string $ip IP地址
     * @return bool
     */
    public static function deleteSameIp(string $ip)
    {
        self::useWritePdo()
            ->where('ip', 'like', $ip . '%')
            ->chunkById(50, function ($items) {
                collect($items)->each(function (CommentsModel $item) {
                    if ($item->status == self::STATUS_APPROVED && $item->contents) {
                        $item->contents->decrement('commentsNum');
                    }
                    // 清理缓存，包含前台 Home 评论列表
                    cached('')->clearGroup(self::GP_CON_COM_LIST);
                    cached('')->clearGroup(self::GP_CON_COM_REPLY_LIST);
                    cached('')->clearGroup(self::GP_LIST_COMMENT_LIST);
                    $item->delete();
                });
            });
        return true;
    }

    /**
     * 封禁单个IP
     * @param string $ip IP地址
     * @param string $reason 封禁原因
     * @param int $adminId 管理员ID
     * @return bool
     */
    public static function banIp(string $ip, string $reason, int $adminId)
    {
        // 这里需要配合 IP 黑名单表,暂时只标记评论为垃圾
        return self::batchUpdateStatus(
            self::where('ip', $ip)->pluck('coid')->toArray(),
            self::STATUS_SPAM,
            $adminId
        );
    }

    /**
     * 封禁IP段
     * @param string $startIp 起始IP
     * @param string $endIp 结束IP
     * @param string $reason 封禁原因
     * @param int $adminId 管理员ID
     * @return bool
     */
    public static function banIpRange(string $startIp, string $endIp, string $reason, int $adminId)
    {
        $start = ip2long($startIp);
        $end = ip2long($endIp);

        if ($start === false || $end === false || $start > $end) {
            return false;
        }

        // 获取该IP段内的所有评论ID
        $ids = self::whereBetween(\DB::raw('INET_ATON(ip)'), [$start, $end])
            ->pluck('coid')
            ->toArray();

        if (empty($ids)) {
            return true;
        }

        return self::batchUpdateStatus($ids, self::STATUS_SPAM, $adminId);
    }

    /**
     * 创建评论
     * @param array $data 评论数据
     * @return CommentsModel|null
     */
    public static function createComment(array $data)
    {
        $comment = self::create($data);
        if ($comment && $data['status'] == self::STATUS_APPROVED) {
            ContentsModel::find($data['cid'])->increment('commentsNum');
        }
        return $comment;
    }

    /**
     * 切换置顶状态
     * @param int $coid 评论ID
     * @return bool
     */
    public static function toggleTop(int $coid)
    {
        $comment = self::find($coid);
        if (!$comment) {
            throw new \Exception('评论不存在');
        }

        if ($comment->parent != 0) {
            throw new \Exception('只有一级评论才能置顶');
        }

        // 如果要置顶,检查是否为官方账号
        if ($comment->is_top == self::TOP_NO) {
            $official = OfficialAccountModel::where('aff', $comment->app_aff)->first();
            if (!$official) {
                throw new \Exception('非官方账号不能置顶');
            }
        }

        $comment->is_top = $comment->is_top == self::TOP_OK ? self::TOP_NO : self::TOP_OK;
        return $comment->save();
    }

}
