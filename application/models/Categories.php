<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class CategoriesModel
 *
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $sort_order 排序
 * @property int sort_column 排序
 * @property int $parent_id 父级分类ID
 * @property string $created_at
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property string $update_at
 *
 * @mixin \Eloquent
 */
class CategoriesModel extends BaseModel
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'name',
            'slug',
            'description',
            'parent_id',
            'sort_order',
            'sort_column',
            'created_at',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'update_at',
        ];
    protected $guarded = 'id';
    public $timestamps = false;

    const GP_CONTENT_CATEGORY_LIST = "gp:content:category-list";
    const CN_CONTENT_CATEGORY_LIST = "WEB端分类下文章列表缓存";

    const GP_CONTENT_CATEGORY_LIST_COUNT = "gp:content:category-list-count";
    const CN_CONTENT_CATEGORY_LIST_COUNT = "WEB端分类下文章列表分页缓存";

    /**
     * 根据名称获取Category
     *
     * @param  string  $name
     * @return CategoriesModel|null
     */
    public static function getByName(string $name): ?CategoriesModel
    {
        return self::where('name', $name)->first();
    }

    /**
     * 获取分类总数
     * @return int
     */
    public static function getTotalCount(): int
    {
        return self::count();
    }

    /**
     * 与 CategoryRelationshipsModel 建立一对多关系：
     * 一个标签可以关联多条 content（通过中间表 typecho_tag_relationships）
     */
    public function relationships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            CategoryRelationshipsModel::class,
            'category_id',
            'id'
        );
    }

    public function category()
    {
        return $this->belongsTo(CategoriesModel::class, 'category_id');
    }

    public function url(): ?ParseUrl
    {
        return new ParseUrl(url('category', ['slug' => $this->slug]));
    }

    public function getTDK(): array
    {
        $title = $this->seo_title ?? $this->name;
        $desc = $this->seo_description ?? $this->description;
        $keywords = $this->seo_keywords ?? null;

        return [$title, $desc, $keywords];
    }

    /**
     * 获取分类树状结构
     * @return array
     */
    public static function getAllCategories(): array
    {
        // 获取所有分类
        return  self::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'slug', 'parent_id', 'sort_order', 'description'])
            ->toArray();
    }

    /**
     * 获取分类分页列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [list, total]
     */
    public static function getPageList(array $params, int $limit, int $offset)
    {
        $query = self::query();

        // 关键词搜索 (名称、slug、描述)
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('slug', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // 父级分类筛选
        if (isset($params['parent_id']) && $params['parent_id'] !== '') {
            $parentId = (int) $params['parent_id'];
            if ($parentId === 0) {
                $query->where('parent_id', 0);
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        // 排序
        $orderBy = !empty($params['order_by']) ? $params['order_by'] : 'sort_order';
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
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'parent_id' => $item->parent_id,
                    'sort_order' => $item->sort_order,
                    'sort_column' => $item->sort_column,
                    'seo_title' => $item->seo_title,
                    'seo_keywords' => $item->seo_keywords,
                    'seo_description' => $item->seo_description,
                    'created_at' => $item->created_at,
                    'update_at' => $item->update_at,
                ];
            });

        return [$list, $total];
    }

    /**
     * 获取分类详情
     * @param int $id
     * @return array|null
     */
    public static function getDetail(int $id)
    {
        $category = self::find($id);
        if (!$category) {
            return null;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'sort_column' => $category->sort_column,
            'seo_title' => $category->seo_title,
            'seo_keywords' => $category->seo_keywords,
            'seo_description' => $category->seo_description,
            'created_at' => $category->created_at,
            'update_at' => $category->update_at,
        ];
    }

    /**
     * 保存分类 (创建/更新)
     * @param array $data
     * @return CategoriesModel|false
     */
    public static function saveCategory(array $data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        if ($id > 0) {
            $category = self::find($id);
            if (!$category) {
                return false;
            }
        } else {
            $category = new self();
        }

        // 验证必填字段
        if (empty($data['name'])) {
            throw new \Exception('分类名称不能为空');
        }
        if (empty($data['slug'])) {
            throw new \Exception('分类别名不能为空');
        }

        // 检查 slug 是否重复
        $slugExists = self::where('slug', $data['slug'])
            ->where('id', '!=', $id)
            ->exists();
        if ($slugExists) {
            throw new \Exception('分类别名已存在');
        }

        // 填充数据
        $category->name = $data['name'];
        $category->slug = $data['slug'];
        $category->description = $data['description'] ?? '';
        $category->parent_id = isset($data['parent_id']) ? (int) $data['parent_id'] : 0;
        $category->sort_order = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        $category->sort_column = $data['sort_column'] ?? '';
        $category->seo_title = $data['seo_title'] ?? '';
        $category->seo_keywords = $data['seo_keywords'] ?? '';
        $category->seo_description = $data['seo_description'] ?? '';

        if ($id > 0) {
            $category->update_at = date('Y-m-d H:i:s');
        } else {
            $category->created_at = date('Y-m-d H:i:s');
            $category->update_at = date('Y-m-d H:i:s');
        }

        if ($category->save()) {
            return $category;
        }

        return false;
    }

    /**
     * 删除分类
     * @param array $ids
     * @return bool
     */
    public static function deleteCategories(array $ids)
    {
        if (empty($ids)) {
            return false;
        }

        // 检查是否有子分类
        $hasChildren = self::whereIn('parent_id', $ids)->exists();
        if ($hasChildren) {
            throw new \Exception('存在子分类，无法删除');
        }

        // 检查是否有关联的文章
        $hasContents = CategoryRelationshipsModel::whereIn('category_id', $ids)->exists();
        if ($hasContents) {
            throw new \Exception('分类下存在文章，无法删除');
        }

        return self::whereIn('id', $ids)->delete();
    }

}