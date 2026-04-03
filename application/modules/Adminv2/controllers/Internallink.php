<?php

/**
 * 内链管理 API 控制器
 *
 * 接口形式，供 public/admin SPA 调用。
 * - GET  internallink/config     获取全局配置
 * - POST internallink/saveConfig 保存全局配置
 * - GET  internallink/listAjax   分页列表（支持 where / like / page / limit）
 * - POST internallink/save      新增/编辑单条规则（_pk 为空为新增）
 * - POST internallink/del       删除单条（_pk）
 * - POST internallink/delAll    批量删除（value 为逗号分隔 id）
 */
class InternallinkController extends AdminV2BaseController
{
    /**
     * 获取全局内链规则配置
     * GET /adminv2/internallink/config
     */
    public function configAction()
    {
        $maxLinks = (int) setting('internal_link_max_per_article', 3);
        if ($maxLinks <= 0) {
            $maxLinks = 3;
        }
        return $this->showJson([
            'max_auto_links_per_article' => $maxLinks,
        ]);
    }

    /**
     * 保存全局内链规则配置
     * POST /adminv2/internallink/saveConfig
     *
     * 参数:
     * - max_auto_links_per_article: 单篇文章最多自动内链数 (整数，默认 3)
     */
    public function saveConfigAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $value = isset($this->data['max_auto_links_per_article'])
            ? (int) $this->data['max_auto_links_per_article']
            : 3;
        if ($value <= 0) {
            $value = 3;
        }

        SettingModel::set('internal_link_max_per_article', $value);

        return $this->successMsg('保存成功');
    }

    /**
     * 内链规则分页列表
     * GET /adminv2/internallink/listAjax
     *
     * 参数:
     * - page: 页码
     * - limit: 每页条数
     * - where: 精确条件 { status: 0|1 }
     * - like: 模糊条件 { keyword: '', target_url: '' }
     *
    * 返回: pageJson(list, total)
     */
    public function listAjaxAction()
    {
        $builder = InternalLinkRuleModel::query()->orderBy('id', 'desc');

        $where = $this->data['where'] ?? [];
        if (is_array($where) && isset($where['status']) && $where['status'] !== '' && $where['status'] !== null) {
            $builder->where('status', (int) $where['status']);
        }

        $like = $this->data['like'] ?? [];
        if (is_array($like)) {
            if (!empty($like['keyword'])) {
                $builder->where('keyword', 'like', '%' . trim($like['keyword']) . '%');
            }
            if (!empty($like['target_url'])) {
                $builder->where('target_url', 'like', '%' . trim($like['target_url']) . '%');
            }
        }

        $total = $builder->count();
        $list = $builder->offset($this->offset)->limit($this->limit)->get();

        $list = $list->map(function (InternalLinkRuleModel $item) {
            $item->status_str = InternalLinkRuleModel::STATUS[$item->status] ?? '';
            return $item;
        });

        return $this->pageJson($list->toArray(), $total);
    }

    /**
     * 保存单条内链规则（新增或更新）
     * POST /adminv2/internallink/save
     *
     * 参数:
     * - _pk: 主键 id，空为新增
     * - keyword: 关键词 (必填)
     * - target_url: 指向链接，相对路径或 http(s) (必填)
     * - max_per_article: 单篇最多插入次数，默认 1
     * - priority: 优先级，默认 0
     * - status: 状态 0 暂停 / 1 启用
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $pk = isset($this->data['_pk']) ? trim((string) $this->data['_pk']) : '';
        $keyword = isset($this->data['keyword']) ? trim((string) $this->data['keyword']) : '';
        $targetUrl = isset($this->data['target_url']) ? trim((string) $this->data['target_url']) : '';
        $maxPerArticle = isset($this->data['max_per_article']) ? (int) $this->data['max_per_article'] : 1;
        $priority = isset($this->data['priority']) ? (int) $this->data['priority'] : 0;
        $status = isset($this->data['status']) ? (int) $this->data['status'] : 1;

        if ($keyword === '') {
            return $this->validationError('关键词不能为空');
        }
        if ($targetUrl === '') {
            return $this->validationError('指向链接不能为空');
        }
        if (!preg_match('/^\//', $targetUrl) && !preg_match('/^https?:\/\//i', $targetUrl)) {
            return $this->validationError('指向链接须为站内相对路径或 http(s) 链接');
        }
        if ($maxPerArticle < 1) {
            $maxPerArticle = 1;
        }
        if ($status !== InternalLinkRuleModel::STATUS_DISABLED && $status !== InternalLinkRuleModel::STATUS_ENABLED) {
            $status = InternalLinkRuleModel::STATUS_ENABLED;
        }

        try {
            if ($pk === '') {
                $model = InternalLinkRuleModel::create([
                    'keyword'           => $keyword,
                    'target_url'        => $targetUrl,
                    'max_per_article'   => $maxPerArticle,
                    'priority'          => $priority,
                    'status'            => $status,
                    'inserted_article_count' => 0,
                ]);
            } else {
                $model = InternalLinkRuleModel::find((int) $pk);
                if (!$model) {
                    return $this->notFound('规则不存在');
                }
                $model->keyword = $keyword;
                $model->target_url = $targetUrl;
                $model->max_per_article = $maxPerArticle;
                $model->priority = $priority;
                $model->status = $status;
                $model->save();
            }

            $row = $model->toArray();
            $row['status_str'] = InternalLinkRuleModel::STATUS[$model->status] ?? '';
            return $this->successMsg('操作成功', $row);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 删除单条内链规则
     * POST /adminv2/internallink/del
     *
     * 参数:
     * - _pk: 规则 id
     */
    public function delAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $pk = isset($this->data['_pk']) ? (int) $this->data['_pk'] : 0;
        if (!$pk) {
            return $this->validationError('缺少 _pk');
        }

        $model = InternalLinkRuleModel::find($pk);
        if (!$model) {
            return $this->successMsg('操作成功');
        }
        $model->delete();
        return $this->successMsg('操作成功');
    }

    /**
     * 批量删除内链规则
     * POST /adminv2/internallink/delAll
     *
     * 参数:
     * - value: 逗号分隔的 id，如 "1,2,3"
     */
    public function delAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $value = isset($this->data['value']) ? trim((string) $this->data['value']) : '';
        $ids = array_filter(array_map('intval', explode(',', $value)));
        if (empty($ids)) {
            return $this->validationError('请选择要删除的规则');
        }

        try {
            InternalLinkRuleModel::whereIn('id', $ids)->delete();
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}
