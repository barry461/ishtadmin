<?php

/**
 * 统计与验证代码管理 API 控制器
 */
class SeostatcodeController extends AdminV2BaseController
{
    /**
     * 列表
     * GET /adminv2/seostatcode/list
     *
     * 参数:
     * - position: 位置 head/footer (可选)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        $query = SeoStatCodeModel::query();

        if (!empty($this->data['position'])) {
            $position = $this->data['position'] === 'footer' ? 'footer' : 'head';
            $query->where('position', $position);
        }

        if (!empty($this->data['keyword'])) {
            $keyword = trim($this->data['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            });
        }

        $total = $query->count();
        $list = $query
            ->orderBy('position', 'asc')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();

        return $this->pageJson($list, $total);
    }

    /**
     * 详情
     * GET /adminv2/seostatcode/detail
     *
     * 参数:
     * - id: 记录ID (必填)
     */
    public function detailAction()
    {
        if (empty($id = (int)($this->data['id'] ?? 0))) {
            return $this->validationError('id 参数必填');
        }

        $row = SeoStatCodeModel::query()->find($id);
        if (!$row) {
            return $this->notFound('记录不存在');
        }

        return $this->showJson($row);
    }

    /**
     * 保存 (创建/更新)
     * POST /adminv2/seostatcode/save
     *
     * 参数:
     * - id: 记录ID (更新时可选)
     * - name: 配置名称 (必填)
     * - position: 位置 head/footer (必填)
     * - code: 代码内容 (必填)
     * - status: 状态 0/1 (可选，默认1)
     * - sort: 排序 (可选，默认0)
     */
    public function saveAction()
    {
        $errors = $this->validateSaveParams();
        if ($errors) {
            return $this->validationError('参数验证失败', $errors);
        }

        $data = [
            'name' => trim($this->data['name']),
            'position' => $this->data['position'] === 'footer' ? 'footer' : 'head',
            'code' => $this->data['code'],
            'status' => isset($this->data['status']) ? (int)$this->data['status'] : 1,
            'sort' => isset($this->data['sort']) ? (int)$this->data['sort'] : 0,
        ];

        if (!empty($this->data['id'])) {
            $id = (int)$this->data['id'];
            $row = SeoStatCodeModel::query()->find($id);
            if (!$row) {
                return $this->notFound('记录不存在');
            }
            $row->fill($data);
            $row->save();
        } else {
            $row = SeoStatCodeModel::query()->create($data);
        }

        return $this->showJson(
            ['id' => $row->id],
            self::STATUS_SUCCESS,
            '保存成功'
        );
    }

    /**
     * 删除
     * POST /adminv2/seostatcode/delete
     *
     * 参数:
     * - ids: 记录ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($ids = (array)($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        $result = SeoStatCodeModel::query()
            ->whereIn('id', $ids)
            ->delete();

        if ($result > 0) {
            return $this->successMsg('删除成功');
        }

        return $this->errorJson('删除失败或记录不存在');
    }

    /**
     * 验证保存参数
     */
    private function validateSaveParams(): array
    {
        $errors = [];

        if (empty($this->data['name'])) {
            $errors['name'][] = '名称不能为空';
        } elseif (mb_strlen($this->data['name']) > 191) {
            $errors['name'][] = '名称长度不能超过191个字符';
        }

        if (empty($this->data['position'])) {
            $errors['position'][] = '位置不能为空';
        } elseif (!in_array($this->data['position'], ['head', 'footer'], true)) {
            $errors['position'][] = '位置必须是 head 或 footer';
        }

        if (empty($this->data['code'])) {
            $errors['code'][] = '代码内容不能为空';
        }

        return $errors;
    }
}

