<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class CustomSortModel
 *
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $status 状态 0=关闭
 *
 * @mixin \Eloquent
 */
class CustomSortModel extends BaseModel
{
    protected $table = 'custom_sort';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'name',
            'slug',
            'status',
        ];
    protected $guarded = 'id';
    public $timestamps = false;

    const OPTION_STATUS_CLOSE = 0;
    const OPTION_STATUS_OPEN = 1;
    const OPTION_STATUS = [
        self::OPTION_STATUS_CLOSE => '关闭',
        self::OPTION_STATUS_OPEN => '开启'
    ];

    const CACHE_SAVE_LOCK = 'custom_sort:lock';
    const CACHE_DELETE_LOCK = 'custom_sort:del:lock';

    /**
     * 根据名称获取Category你们工资
     *
     * @param  string  $name
     * @return CategoriesModel|null
     */
    public static function getByName(string $name): ?CustomSortModel
    {
        return self::where('name', $name)->first();
    }

    protected static function boot()
    {
        try {
            parent::boot();
            $contentModel = new ContentsModel();
            $customersortModel = new CustomSortModel();

            static::saving(function ($model) {
                if (empty($model->slug) || empty($model->name)) {
                    return false;
                }
                if(yac()->get(self::CACHE_SAVE_LOCK)){
                    test_assert(false, '前一个任务执行中，请稍后提交');
                    return false;
                }

                if( !$model->id ){
                    $isSort = CustomSortModel::where('slug', $model->slug)->first();
                    if($isSort){
                        test_assert(false, '已存在');
                        return false;
                    }
                }

                // 限制3分钟
                yac()->set(self::CACHE_SAVE_LOCK,1,600);
            });

            static::saved(function ($model) use ($contentModel) {
//                $contentModel->upSortFeild($model->slug, $model->name, 0);
                yac()->delete(self::CACHE_SAVE_LOCK);
            });

//            static::deleting(function ($model) use ($contentModel) {
//                if(yac()->get(self::CACHE_DELETE_LOCK)){
//                    test_assert(false, '前一个删除任务执行中，请稍后提交');
//                    return false;
//                }
//                // 限制3分钟
//                yac()->set(self::CACHE_DELETE_LOCK,1,600);
//
//                return $contentModel->downSortFeild($model->slug);
//            });
//            static::deleted(function ($model) use ($contentModel) {
//                yac()->delete(self::CACHE_DELETE_LOCK);
//            });

        }catch (\Exception $e){
            trigger_log("Error-新增排序字段错误：".$e->getMessage());
            return false;
        }catch (\Throwable $e){
            trigger_log("Error-新增排序字段错误：".$e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 获取自定义排序字段分页列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [list, total]
     */
    public static function getPageList(array $params, int $limit, int $offset)
    {
        $query = self::query();

        // 关键词搜索 (名称、slug)
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('slug', 'like', '%' . $keyword . '%');
            });
        }

        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $status = (int) $params['status'];
            $query->where('status', $status);
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
                    'slug' => $item->slug,
                    'status' => $item->status,
                    'status_text' => self::OPTION_STATUS[$item->status] ?? '未知',
                ];
            });

        return [$list, $total];
    }

    /**
     * 获取自定义排序字段详情
     * @param int $id
     * @return array|null
     */
    public static function getDetail(int $id)
    {
        $customSort = self::find($id);
        if (!$customSort) {
            return null;
        }

        return [
            'id' => $customSort->id,
            'name' => $customSort->name,
            'slug' => $customSort->slug,
            'status' => $customSort->status,
            'status_text' => self::OPTION_STATUS[$customSort->status] ?? '未知',
        ];
    }

    /**
     * 保存自定义排序字段 (创建/更新)
     * @param array $data
     * @return CustomSortModel|false
     */
    public static function saveCustomSort(array $data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        if ($id > 0) {
            $customSort = self::find($id);
            if (!$customSort) {
                return false;
            }
        } else {
            $customSort = new self();
        }

        // 验证必填字段
        if (empty($data['name'])) {
            throw new \Exception('字段名称不能为空');
        }
        if (empty($data['slug'])) {
            throw new \Exception('字段别名不能为空');
        }

        // 检查 slug 是否重复（更新时排除自己）
        $slugExists = self::where('slug', $data['slug'])
            ->where('id', '!=', $id)
            ->exists();
        if ($slugExists) {
            throw new \Exception('字段别名已存在');
        }

        // 填充数据
        $customSort->name = trim($data['name']);
        $customSort->slug = trim($data['slug']);
        $customSort->status = isset($data['status']) ? (int) $data['status'] : self::OPTION_STATUS_OPEN;

        // boot 方法会自动处理锁和验证
        if ($customSort->save()) {
            return $customSort;
        }

        return false;
    }

    /**
     * 删除自定义排序字段
     * @param array $ids
     * @return bool
     */
    public static function deleteCustomSorts(array $ids)
    {
        if (empty($ids)) {
            return false;
        }

        // 可以在这里添加业务逻辑检查，比如是否被使用等
        // 目前直接删除
        return self::whereIn('id', $ids)->delete();
    }

}