<?php

/**
 * 自定义排序字段管理
 *
 * 用于配置文章列表中的自定义排序字段（如热度、权重等）
 */
class SortfieldController extends AdminV2BaseController
{
    /**
     * 自定义排序字段列表
     *
     * GET /adminv2/sortfield/list
     *
     * 参数：
     * - keyword: 搜索关键词（按名称或别名模糊搜索）
     * - status: 状态筛选（0=关闭，1=开启）
     * - order_by: 排序字段，默认 id
     * - order_dir: 排序方向 asc/desc，默认 desc
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = CustomSortModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }
    
    /**
     * 自定义排序字段详情
     *
     * GET /adminv2/sortfield/detail
     *
     * 参数：
     * - id: 字段ID（必填）
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少字段ID');
        }

        $customSort = CustomSortModel::getDetail($id);
        if (!$customSort) {
            return $this->notFound('字段不存在');
        }

        return $this->showJson($customSort);
    }
    
    /**
     * 保存自定义排序字段（新增 / 编辑）
     *
     * POST /adminv2/sortfield/save
     *
     * 参数：
     * - id: 字段ID（编辑时必填）
     * - name: 字段名称（必填，用于后台展示）
     * - slug: 字段别名（必填，对应 contents 表中的列名）
     * - status: 状态（0=关闭，1=开启，默认 1）
     */
    public function saveAction()
    {
        if (empty($this->data['name'])) {
            return $this->validationError('字段名称不能为空');
        }
        if (empty($this->data['slug'])) {
            return $this->validationError('字段别名不能为空');
        }

        try {
            $customSort = transaction(function () {
                return CustomSortModel::saveCustomSort($this->data);
            });

            if ($customSort) {
                return $this->showJson(
                    ['id' => $customSort->id],
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
     * 删除自定义排序字段
     *
     * POST /adminv2/sortfield/delete
     *
     * 参数：
     * - ids: 字段ID数组（必填）
     */
    public function deleteAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        try {
            $result = transaction(function () use ($ids) {
                return CustomSortModel::deleteCustomSorts($ids);
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
    
    /**
     * 切换字段启用状态
     *
     * POST /adminv2/sortfield/toggleStatus
     *
     * 参数：
     * - id: 字段ID（必填）
     */
    public function toggleStatusAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少字段ID');
        }

        $customSort = CustomSortModel::find($id);
        if (!$customSort) {
            return $this->notFound('字段不存在');
        }

        $newStatus = $customSort->status == CustomSortModel::OPTION_STATUS_OPEN
            ? CustomSortModel::OPTION_STATUS_CLOSE
            : CustomSortModel::OPTION_STATUS_OPEN;

        $customSort->status = $newStatus;
        $customSort->save();

        return $this->showJson([
            'id' => $id,
            'status' => $newStatus,
            'status_text' => CustomSortModel::OPTION_STATUS[$newStatus],
        ], self::STATUS_SUCCESS, '状态切换成功');
    }
    
    /**
     * 获取已开启的排序字段选项
     *
     * GET /adminv2/sortfield/options
     *
     * 返回字段：
     * - id: 字段ID
     * - name: 字段名称
     * - slug: 字段别名
     */
    public function optionsAction()
    {
        $list = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)
            ->select('id', 'name', 'slug')
            ->orderBy('id', 'asc')
            ->get();

        return $this->showJson($list);
    }
}

