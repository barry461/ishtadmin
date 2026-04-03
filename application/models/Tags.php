<?php


/**
 * class TagsModel
 *
 * @property int $id
 * @property string $name 标签名
 * @property int $created_at
 * @property int $updated_at
 * @mixin \Eloquent
 */
// class TagsModel extends BaseModel
// {

//     protected $table = "tags";

//     protected $primaryKey = 'id';

//     protected $fillable = ['name', 'created_at', 'updated_at'];


//     protected $appends = ['updated_str', 'created_str'];

//     public function getCreatedStrAttribute()
//     {
//         return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
//     }

//     public function getUpdatedStrAttribute()
//     {
//         return date('Y-m-d H:i:s', $this->attributes['updated_at'] ?? 0);
//     }


// }

use Illuminate\Database\Eloquent\Model;

/**
 * Class TagModel
 *
 * @property int         $id          标签ID
 * @property string      $name        标签名称
 * @property \Carbon\Carbon|null $created_at  创建时间
 *
 * @mixin \Eloquent
 */
class TagsModel extends BaseModel
{
    
    protected $table = "tags";

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = false;

  
    protected $fillable = ['name'];

  
    protected $guarded = ['id'];

 
    const CK_TAG_ID = 'ck:tag:id:%d';
    const GP_TAG    = 'gp:tag:id';

    /**
     * 根据 ID 获取单条 Tag 记录（带缓存）
     *
     * @param  int  $id
     * @return array|null
     */
    public static function getByIdCached(int $id): ?array
    {
        return cached(sprintf(self::CK_TAG_ID, $id))
            ->group(self::GP_TAG)
            ->fetchJson(function () use ($id) {
                $record = self::find($id);
                return $record ? $record->toArray() : null;
            });
    }

    /**
     * 根据名称获取 Tag（不带缓存）
     *
     * @param  string  $name
     * @return TagModel|null
     */
    public static function getByName(string $name): ?TagsModel
    {
        return self::where('name', $name)->first();
    }

    /**
     * 与 TagRelationshipsModel 建立一对多关系：
     * 一个标签可以关联多条 content（通过中间表 typecho_tag_relationships）
     */
    public function relationships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            TagRelationshipsModel::class,
            'tag_id',
            'id'
        );
    }

    public function url(): ?ParseUrl
    {
       return new ParseUrl(url('tag.detail', ['tag' => $this->name]));
    }

    /**
     * 获取标签分页列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [list, total]
     */
    public static function getPageList(array $params, int $limit, int $offset)
    {
        $query = self::query();

        // 关键词搜索 (名称)
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // 排序
        $orderBy = !empty($params['order_by']) ? $params['order_by'] : 'id';
        $orderDir = $params['order_dir'] ?? 'desc';
        if (!in_array(strtolower($orderDir), ['asc', 'desc'])) {
            $orderDir = 'desc';
        } else {
            $orderDir = strtolower($orderDir);
        }
        $query->orderBy($orderBy, $orderDir);

        $total = $query->count();
        $list = $query->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            });

        return [$list, $total];
    }

    /**
     * 获取标签详情
     * @param int $id
     * @return array|null
     */
    public static function getDetail(int $id)
    {
        $tag = self::find($id);
        if (!$tag) {
            return null;
        }

        return [
            'id' => $tag->id,
            'name' => $tag->name,
        ];
    }

    /**
     * 保存标签 (创建/更新)
     * @param array $data
     * @return TagsModel|false
     */
    public static function saveTag(array $data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        if ($id > 0) {
            $tag = self::find($id);
            if (!$tag) {
                return false;
            }
        } else {
            $tag = new self();
        }

        // 验证必填字段
        if (empty($data['name'])) {
            throw new \Exception('标签名称不能为空');
        }

        // 检查名称是否重复
        $nameExists = self::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->exists();
        if ($nameExists) {
            throw new \Exception('标签名称已存在');
        }

        // 填充数据
        $tag->name = trim($data['name']);

        if ($tag->save()) {
            return $tag;
        }

        return false;
    }

    /**
     * 删除标签
     * @param array $ids
     * @return bool
     */
    public static function deleteTags(array $ids)
    {
        if (empty($ids)) {
            return false;
        }

        // 检查是否有关联的文章
        $hasContents = TagRelationshipsModel::whereIn('tag_id', $ids)->exists();
        if ($hasContents) {
            throw new \Exception('标签下存在文章，无法删除');
        }

        return self::whereIn('id', $ids)->delete();
    }
}
