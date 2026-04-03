<?php

/**
 * Class ProductController
 * @author xiongba
 * @date 2020-06-06 15:02:11
 */
class ProductrightmapController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (ProductRightMapModel $item) {
            $item->status_str = ProductRightMapModel::STATUS[$item->status];
            $item->product_name = $item->product->pname;
            $item->right_name = $item->right->name;
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $productArr = ProductModel::get()->toArray();
        $productRightArr = ProductRightModel::get()->toArray();
        $productArr = array_column($productArr, 'pname', 'id');
        $productRightArr = array_column($productRightArr, 'name', 'id');
        $this->assign('productArr', $productArr);
        $this->assign('productRightArr', $productRightArr);
        $this->assign('query', $_GET);
        $this->display();
    }


    protected function getModelObject()
    {
        return ProductRightMapModel::with(
            ['product','right']
        );
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return ProductRightMapModel::class;
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