<?php

/**
 * 分类管理 API 控制器
 */
class CategoryController extends AdminV2BaseController
{
    /**
     * 分类列表
     * GET /adminv2/category/list
     * 
     * 参数:
     * - tree: 是否返回树状结构 (1=是, 默认0)
     * - keyword: 搜索关键词 (name/slug/description)
     * - parent_id: 父级分类ID筛选 (0表示顶级分类)
     * - order_by: 排序字段 (默认 sort_order)
     * - order_dir: 排序方向 (asc/desc, 默认 asc)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        // 树状结构模式
        if (!empty($this->data['tree'])) {
            $tree = $this->buildCategoryTree();
            return $this->showJson($tree);
        }

        // 普通分页列表
        [$list, $total] = CategoriesModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 构建分类树状结构
     * 
     * @return array
     */
    private function buildCategoryTree(): array
    {
        $categories =  CategoriesModel::getAllCategories();
        // 构建树状结构
        $tree = [];
        $map = [];

        // 首先建立 id => item 的映射
        foreach ($categories as &$category) {
            $category['children'] = [];
            $map[$category['id']] = &$category;
        }
        unset($category);

        // 构建树
        foreach ($categories as &$category) {
            $parentId = (int) $category['parent_id'];
            if ($parentId === 0) {
                $tree[] = &$map[$category['id']];
            } elseif (isset($map[$parentId])) {
                $map[$parentId]['children'][] = &$map[$category['id']];
            }
        }
        unset($category);

        return $tree;
    }

    /**
     * 分类详情
     * GET /adminv2/category/detail
     * 
     * 参数:
     * - id: 分类ID (必填)
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少分类ID');
        }

        $category = CategoriesModel::getDetail($id);
        if (!$category) {
            return $this->notFound('分类不存在');
        }

        return $this->showJson($category);
    }

    /**
     * 保存分类 (创建/更新)
     * POST /adminv2/category/save
     * 
     * 参数:
     * - id: 分类ID (更新时必填)
     * - name: 分类名称 (必填)
     * - slug: 分类别名 (必填)
     * - description: 分类描述
     * - parent_id: 父级分类ID
     * - sort_order: 排序值
     * - sort_column: 排序列
     * - seo_title: SEO标题
     * - seo_keywords: SEO关键词
     * - seo_description: SEO描述
     */
    public function saveAction()
    {
        if (empty($this->data['name'])) {
            return $this->validationError('分类名称不能为空');
        }
        if (empty($this->data['slug'])) {
            return $this->validationError('分类别名不能为空');
        }

        try {
            $category = transaction(function () {
                return CategoriesModel::saveCategory($this->data);
            });

            if ($category) {
                return $this->showJson(
                    ['id' => $category->id],
                    self::STATUS_SUCCESS,
                    '保存成功'
                );
            } else {
                return $this->errorJson('保存失败');
            }
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 删除分类
     * POST /adminv2/category/delete
     * 
     * 参数:
     * - ids: 分类ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        try {
            $result = transaction(function () use ($ids) {
                return CategoriesModel::deleteCategories($ids);
            });

            if ($result !== false) {
                return $this->successMsg('删除成功');
            } else {
                return $this->errorJson('删除失败');
            }
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}
