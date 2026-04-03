<?php

class BannerController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration(): Closure
    {
        return function (BannerModel $item) {
            $item->setHidden([]);
            $item->status_str = BannerModel::STATUS[$item->status];
            $item->type_str = BannerModel::TYPE[$item->type];
            return $item;
        };
    }



    /**
     * 试图渲染
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
       return BannerModel::class;
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