<?php

/**
 * Class FlutterrouterController
 * @author xiongba
 * @date 2022-10-18 02:51:41
 */
class FlutterrouterController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     */
    protected function listAjaxIteration()
    {
        return function (FlutterRouterModel $item) {
            $item->setHidden([]);
            $item->setAttribute('status_str' ,FlutterRouterModel::STATUS[$item->status]);
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
       return FlutterRouterModel::class;
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
    protected function getLogDesc(): string {
        return '';
    }
}