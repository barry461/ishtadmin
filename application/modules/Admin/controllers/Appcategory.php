<?php

class AppcategoryController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->status_str = AppCategoryModel::STATUS_TIPS[$item->status];
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return AppCategoryModel::class;
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
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        AppCategoryModel::clearCache();
    }
}