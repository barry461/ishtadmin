<?php


/**
 * class UsersModel
 *
 * @property int $uid
 * @property string $name
 * @property string $password
 * @property string $mail
 * @property string $url
 * @property string $screenName
 * @property int $created
 * @property int $activated
 * @property int $logged
 * @property string $group
 * @property string $authCode
 * @property string $seo_title
 * @property string $seo_description
 * @property string $seo_keywords
 *
 * @mixin \Eloquent
 */
class UsersModel extends BaseModel
{

    protected $table = "users";

    protected $primaryKey = 'uid';

    protected $fillable = [
        'name',
        'password',
        'mail',
        'url',
        'screenName',
        'created',
        'activated',
        'logged',
        'group',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'authCode',
    ];

    protected $guarded = 'uid';

    public $timestamps = false;

    protected $hidden = ['password'];

    protected $visible = [
        'uid',
        'name',
        'created',
        'logged',
        'screenName',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'url',
        'mail',
    ];

    public static function findByUsername($username)
    {
        return self::where('name', $username)->first();
    }

    public function url(): ?ParseUrl
    {
        return new ParseUrl(url('authors', [$this->uid]));
    }

    /**
     * 用户发布的内容关系
     */
    public function contents()
    {
        return $this->hasMany(ContentsModel::class, 'authorId', 'uid');
    }

    public function getTDK(): array
    {
        $title = $this->seo_title ?? $this->screenName;
        $desc = $this->seo_description ?? null;
        $keywords = $this->seo_keywords ?? null;


        return [$title, $desc, $keywords];
    }

    /**
     * 获取作者分页列表
     */
    public static function getAuthorsList(array $params, int $limit, int $offset): array
    {
        $query = self::query();

        // 关键词搜索
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('screenName', 'like', '%' . $keyword . '%')
                    ->orWhere('mail', 'like', '%' . $keyword . '%');
            });
        }

        // 分组筛选
        if (!empty($params['group'])) {
            $query->where('group', $params['group']);
        }

        $total = $query->count();
        $list = $query->orderByDesc('uid')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return [$list, $total];
    }

    /**
     * 获取作者详情
     */
    public static function getAuthorDetail(int $uid)
    {
        return self::find($uid);
    }

    /**
     * 保存作者(创建/更新)
     */
    public static function saveAuthor(array $data): UsersModel
    {
        if (!empty($data['uid'])) {
            $author = self::find($data['uid']);
            test_assert($author, '作者不存在');
        } else {
            $author = new self();
            $author->created = time();
        }

        $author->fill([
            'name' => $data['name'],
            'mail' => $data['mail'],
            'screenName' => $data['screenName'],
            'group' => $data['group'] ?? 'subscriber',
            'url' => $data['url'] ?? '',
            'seo_title' => $data['seo_title'] ?? '',
            'seo_keywords' => $data['seo_keywords'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
        ]);

        // 密码处理
        if (!empty($data['password'])) {
            $author->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $author->save();

        return $author;
    }

    /**
     * 批量删除作者
     */
    public static function deleteAuthors(array $uids): int
    {
        return self::whereIn('uid', $uids)->delete();
    }

    /**
     * 获取所有用户分组
     */
    public static function getAllGroups(): array
    {
        return [
            ['value' => 'administrator', 'label' => '管理员'],
            ['value' => 'editor', 'label' => '编辑'],
            ['value' => 'contributor', 'label' => '投稿者'],
            ['value' => 'subscriber', 'label' => '订阅者'],
        ];
    }
}
