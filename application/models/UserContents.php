<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class UserContentsModel
 *
 *
 * @property int $id
 * @property int $cid 内容id
 * @property int $income 受益
 * @property int $aff 用户aff
 * @property string $category_id 分类
 * @property string $title
 * @property string $body 内容
 * @property string $tags 标签
 * @property string $cover 封面
 * @property int $status 状态
 * @property string $denied_reason 拒绝的理由
 * @property int $denied_at 拒绝的时间
 * @property int $created_at
 * @property int $admin_id
 * @property int $user_type
 * @property int $created
 *
 * @property ManagerModel $manager
 * @property MemberModel $member
 *
 * @mixin \Eloquent
 */
class UserContentsModel extends BaseModel
{
    protected $table = 'user_contents';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'id',
            'cid',
            'income',
            'category_id',
            'aff',
            'title',
            'cover',
            'body',
            'tags',
            'status',
            'denied_reason',
            'denied_at',
            'created_at',
            'admin_id',
            'user_type',
            'created',
        ];
    protected $guarded = 'id';
    const STATUS_WAIT = 0;
    const STATUS_DENIED = 1;
    const STATUS_PASSED = 2;
    const STATUS_WAIT_SLICE = 3;
    const STATUS_DRAFT = 4;
    const STATUS
        = [
            self::STATUS_WAIT   => '待审',
            self::STATUS_DENIED => '拒绝',
            self::STATUS_PASSED => '通过',
            self::STATUS_WAIT_SLICE => '等待回调',
            self::STATUS_DRAFT => '草稿',
        ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    const USER_TYPE_APP = 0;
    const USER_TYPE_SNS = 1;

    protected $appends = [
        'cover_url',
        'tags_list',
        'category_list',
    ];

    public $timestamps = false;

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function manager()
    {
        return $this->hasOne(ManagerModel::class, 'uid', 'admin_id');
    }

    public function getCoverUrlAttribute(): string
    {
        return url_image($this->attributes['cover'] ?? '');
    }

    public function setCoverAttribute($value)
    {
        $this->resetSetPathAttribute('cover', $value);
    }

    public function getCreatedAttribute()
    {
        $created = $this->attributes['created'] ?? 0;
        if ($created > 0){
            if ($this->attributes['user_type'] == self::USER_TYPE_SNS){
                $created = date('Y-m-d H:i:s', $created - 3600);
            }else{
                $created = date('Y-m-d H:i:s', $created);
            }
        }else{
            $created = '';
        }
        return $created;
    }

    public function getTagsListAttribute()
    {
        $tags = $this->attributes['tags'] ?? '[]';
        $data =   json_decode($tags , 1);
        if (!is_array($data)){
            return [];
        }
        return $data;
    }

    public function getCategoryListAttribute()
    {
        $category_ids = isset($this->attributes['category_id']) ?? '[]';
        $data = json_decode($category_ids , true);
        if (json_last_error() != JSON_ERROR_NONE){
            return [];
        }
        return $data;
    }


}