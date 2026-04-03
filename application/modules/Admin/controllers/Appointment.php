<?php

/**
 * Class AppointmentController
 * @date 2025-04-10 07:01:40
 */
class AppointmentController extends BackendBaseController
{

    use \repositories\HoutaiRepository;
    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (AppointmentModel $item) {
            $item->setHidden([]);
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
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
        return AppointmentModel::class;
    }

    protected function getModelObject()
    {
        return AppointmentModel::with("info");
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
    protected function getLogDesc(): string {
        return '';
    }
}