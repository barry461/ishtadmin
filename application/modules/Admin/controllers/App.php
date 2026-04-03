<?php

class AppController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->status_str = AppModel::STATUS_TIPS[$item->status];
            $category = AppCategoryModel::where('id', $item->category_id)->first();
            $item->category_str = $category ? $category->name : '已删除';
            $item->type_str = AppModel::TYPE_TIPS[$item->type] ?? '未知';
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $categories = AppCategoryModel::get()->pluck('name', 'id')->toArray();
        $this->assign('categories', $categories);
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return AppModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     * @return string
     */
    protected function getLogDesc(): string
    {
        return '';
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        AppModel::clearCache();
    }

    public function batch_replaceAction(): bool
    {
        try {
            $from = trim($_POST['from'] ?? '');
            $to = trim($_POST['to'] ?? '');
            test_assert($from, '原网址不能为空');
            test_assert($to, '新网址不能为空');

            $record = AppModel::where('url', $from)->first();
            test_assert($record, '未找到此原始网址' . $from);

            $isOk = AppModel::where('url', $from)->update([
                'url'        => $to,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            test_assert($isOk, '系统异常');
            AppModel::clearCache();
            return $this->ajaxSuccess('已成功替换');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}