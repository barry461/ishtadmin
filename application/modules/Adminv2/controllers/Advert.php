<?php

/**
 * 广告管理 API 控制器 (RESTful)
 */
class AdvertController extends AdminV2BaseController
{
    /**
     * 广告列表
     * GET /adminv2/advert/list
     * 
     * 参数:
     * - status: 状态筛选 (0/1)
     * - position: 位置筛选
     * - keyword: 标题搜索
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = AdvertModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 广告详情
     * GET /adminv2/advert/detail
     * 
     * 参数:
     * - id: 广告ID (必填)
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少广告ID');
        }

        $advert = AdvertModel::find($id);
        if (!$advert) {
            return $this->notFound('广告不存在');
        }

        // 查询分类
        $cid = AdsCategoryModel::where('aid', $id)->value('cid') ?? 0;
        $advert->category = $cid;

        return $this->showJson($advert);
    }

    /**
     * 保存广告 (创建/更新)
     * POST /adminv2/advert/save
     * 
     * 参数:
     * - id: 广告ID (更新时必填)
     * - title: 标题 (必填)
     * - link: 链接
     * - img_url: 图片地址
     * - position: 位置
     * - status: 状态 (0/1)
     * - sort: 排序
     * - category: 分类ID
     */
    public function saveAction()
    {
        if (empty($this->data['title'])) {
            return $this->validationError('广告标题不能为空');
        }

        // 应用广告必须选择分类
        if (($this->data['position'] ?? '') === AdvertModel::POSITION_ARTICLE_BOTTOM_BTN) {
            if (empty($this->data['category'])) {
                return $this->validationError('此广告为应用,请选择应用类型');
            }
        }

        try {
            $id = transaction(function () {
                return AdvertModel::saveAdvert($this->data);
            });

            return $this->showJson(['id' => $id], self::STATUS_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 删除广告
     * POST /adminv2/advert/delete
     * 
     * 参数:
     * - ids: 广告ID数组 (必填)
     */
    public function deleteAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少广告ID');
        }

        $res = transaction(function () use ($ids) {
            AdsCategoryModel::whereIn('aid', $ids)->delete();
            return AdvertModel::whereIn('id', $ids)->delete();
        });

        if ($res) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 批量替换链接
     * POST /adminv2/advert/batchReplace
     * 
     * 参数:
     * - from: 原链接 (必填)
     * - to: 新链接 (必填)
     */
    public function batchReplaceAction()
    {
        $from = trim($this->data['from'] ?? '');
        $to = trim($this->data['to'] ?? '');

        if (empty($from)) {
            return $this->validationError('原网址不能为空');
        }
        if (empty($to)) {
            return $this->validationError('新网址不能为空');
        }
        if ($from === $to) {
            return $this->validationError('两个网址不能相同');
        }

        // 检查原链接是否存在
        if (!AdvertModel::where('link', $from)->exists()) {
            return $this->notFound('未找到此原始网址');
        }

        $count = AdvertModel::batchReplaceLink($from, $to);

        return $this->showJson(['count' => $count], self::STATUS_SUCCESS, '已成功替换' . $count . '条记录');
    }

    /**
     * 获取位置选项
     * GET /adminv2/advert/positionOptions
     */
    public function positionOptionsAction()
    {
        $options = [];
        foreach (AdvertModel::POSITION_OPT as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $this->showJson($options);
    }

    /**
     * 获取状态选项
     * GET /adminv2/advert/statusOptions
     */
    public function statusOptionsAction()
    {
        $options = [];
        foreach (AdvertModel::STATUS_OPT as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $this->showJson($options);
    }

    /**
     * 获取分类选项
     * GET /adminv2/advert/categoryOptions
     */
    public function categoryOptionsAction()
    {
        return $this->showJson(AdvertModel::ADVERT_CATEGORY);
    }
}
