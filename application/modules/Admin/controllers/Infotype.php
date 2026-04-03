<?php

/**
 * Class InfotypeController
 * @date 2025-04-17 07:56:19
 */
class InfotypeController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (InfoTypeModel $item) {
            $item->setHidden([]);

            $item->status_str = InfoTypeModel::STATUS_ARR[$item->status];
            $item->type_str = InfoTypeModel::TYPE[$item->type];
            $item->category_str = InfoVipModel::CATEGORY[$item->category];
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
       return InfoTypeModel::class;
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