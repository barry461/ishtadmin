<?php

/**
 * Class VersionController
 * @author xiongba
 * @date 2020-05-22 08:22:44
 */
class VersionController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (VersionModel $item) {
            $item->status_str = VersionModel::STATUS[$item->status];
            $item->custom_str = VersionModel::CUSTOM_TIPS[$item->custom];
            return $item;
        };
    }

    protected function saveAfterCallback($model , $oldModel = null)
    {
        VersionModel::clearRedis();
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
        return VersionModel::class;
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
}