<?php

use repositories\HoutaiRepository;

class InternallinkController extends BackendBaseController
{
    use HoutaiRepository;

    public function indexAction()
    {
        // 旧版后台模板入口，目前前端改为使用 public/admin SPA，不再依赖此渲染。
        // 保留空实现，避免误访问时报错。
        $this->ajaxSuccessMsg('ok');
    }

    /**
     * 保存全局内链规则配置
     */
    public function saveConfigAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $post = $this->postArray();
        $value = isset($post['max_auto_links_per_article'])
            ? (int) $post['max_auto_links_per_article']
            : 3;
        if ($value <= 0) {
            $value = 3;
        }

        SettingModel::set('internal_link_max_per_article', $value);

        return $this->ajaxSuccessMsg('保存成功');
    }

    /**
     * 获取全局内链规则配置
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
     * 列表数据过滤
     * @return \Closure
     */
    protected function listAjaxIteration(): \Closure
    {
        return function (InternalLinkRuleModel $item) {
            $item->status_str = InternalLinkRuleModel::STATUS[$item->status] ?? '';
            return $item;
        };
    }

    /**
     * 绑定的模型类
     */
    protected function getModelClass(): string
    {
        return InternalLinkRuleModel::class;
    }

    /**
     * 主键字段
     */
    protected function getPkName(): string
    {
        return 'id';
    }
}

