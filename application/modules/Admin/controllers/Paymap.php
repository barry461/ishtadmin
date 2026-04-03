<?php

/**
 * Class PaywayController
 * @author xiongba
 * @date 2020-06-08 07:41:00
 */
class PaymapController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PayMapModel $item) {
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $productArr = ProductModel::get()->toArray();
        $typeArr = PayTypeModel::get()->toArray();
        $wayArr = PayWayModel::get()->toArray();
        $productArr = array_column($productArr, 'pname', 'id');
        $typeArr = array_column($typeArr, 'name', 'id');
        $wayArr = array_column($wayArr, 'name', 'id');
        $this->assign('productArr', $productArr);
        $this->assign('typeArr', $typeArr);
        $this->assign('wayArr', $wayArr);
        $this->assign('query', $_GET);
        $this->display();
    }


    protected function getModelObject()
    {
        return PayMapModel::with(['product', 'way', 'type']);
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return PayMapModel::class;
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