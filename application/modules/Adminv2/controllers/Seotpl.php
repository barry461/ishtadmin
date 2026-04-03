<?php

/**
 * SEO模板管理 API 控制器
 */
class SeotplController extends AdminV2BaseController
{
    /**
     * 列表
     * GET /adminv2/seotpl/list
     * 
     * 参数:
     * - keyword: 搜索关键词 (可选)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = SeoTplModel::getPageList($this->data, $this->limit, $this->offset);

        return $this->pageJson($list, $total);
    }

    /**
     * 详情
     * GET /adminv2/seotpl/detail
     * 
     * 参数:
     * - id: 模板ID (必填)
     */
    public function detailAction()
    {
        if (empty($id = (int) ($this->data['id'] ?? 0))) {
            return $this->validationError('id 参数必填');
        }

        $template = SeoTplModel::getDetail($id);

        if (!$template) {
            return $this->notFound('SEO模板不存在');
        }

        return $this->showJson($template);
    }

    /**
     * 保存 (创建/更新)
     * POST /adminv2/seotpl/save
     * 
     * 参数:
     * - id: 模板ID (更新时必填)
     * - key: 模板标识 (必填)
     * - val: SEO模板内容 (必填)
     * - desc: 模板名称 (可选)
     * - config: SEO配置模板 (可选)
     * - mark: 备注 (可选)
     */
    public function saveAction()
    {
        // 参数验证
        $errors = $this->validateSaveParams();
        if ($errors) {
            return $this->validationError('参数验证失败', $errors);
        }

        $template = transaction(function () {
            return SeoTplModel::saveTemplate($this->data);
        });

        return $this->showJson(
            ['id' => $template->id],
            self::STATUS_SUCCESS,
            '保存成功'
        );
    }

    /**
     * 删除
     * POST /adminv2/seotpl/delete
     * 
     * 参数:
     * - ids: 模板ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        $result = transaction(function () use ($ids) {
            return SeoTplModel::deleteByIds($ids);
        });

        if ($result !== false) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 验证保存参数
     */
    private function validateSaveParams(): array
    {
        $errors = [];

        // key 必填
        if (empty($this->data['key'])) {
            $errors['key'][] = '模板标识不能为空';
        } elseif (mb_strlen($this->data['key']) > 100) {
            $errors['key'][] = '模板标识长度不能超过100个字符';
        }

        // val 必填
        if (empty($this->data['val'])) {
            $errors['val'][] = 'SEO模板内容不能为空';
        }

//        if (mb_strlen($this->data['val']) > 1000) {
//            $errors['val'][] = 'SEO模板内容长度不能超过1000个字符';
//        }

        // desc 可选,长度限制
        if (isset($this->data['desc']) && mb_strlen($this->data['desc']) > 255) {
            $errors['desc'][] = '模板名称长度不能超过255个字符';
        }

        // // config 可选,长度限制
        // if (isset($this->data['config']) && mb_strlen($this->data['config']) > 1000) {
        //     $errors['config'][] = 'SEO配置模板长度不能超过1000个字符';
        // }

        // mark 可选,长度限制
        if (isset($this->data['mark']) && mb_strlen($this->data['mark']) > 500) {
            $errors['mark'][] = '备注长度不能超过500个字符';
        }

        return $errors;
    }
}
