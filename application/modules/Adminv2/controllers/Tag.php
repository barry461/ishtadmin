<?php

/**
 * 标签管理 API 控制器
 */
class TagController extends AdminV2BaseController
{
    /**
     * 标签列表
     * GET /adminv2/tag/list
     * 
     * 参数:
     * - keyword: 搜索关键词 (name)
     * - order_by: 排序字段 (默认 id)
     * - order_dir: 排序方向 (asc/desc, 默认 desc)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = TagsModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 标签详情
     * GET /adminv2/tag/detail
     * 
     * 参数:
     * - id: 标签ID (必填)
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少标签ID');
        }

        $tag = TagsModel::getDetail($id);
        if (!$tag) {
            return $this->notFound('标签不存在');
        }

        return $this->showJson($tag);
    }

    /**
     * 保存标签 (创建/更新)
     * POST /adminv2/tag/save
     * 
     * 参数:
     * - id: 标签ID (更新时必填)
     * - name: 标签名称 (必填)
     */
    public function saveAction()
    {
        if (empty($this->data['name'])) {
            return $this->validationError('标签名称不能为空');
        }

        try {
            $tag = transaction(function () {
                return TagsModel::saveTag($this->data);
            });

            if ($tag) {
                return $this->showJson(
                    ['id' => $tag->id],
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
     * 删除标签
     * POST /adminv2/tag/delete
     * 
     * 参数:
     * - ids: 标签ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        try {
            $result = transaction(function () use ($ids) {
                return TagsModel::deleteTags($ids);
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

