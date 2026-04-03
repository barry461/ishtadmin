<?php

/**
 * Class ProductController
 * @author xiongba
 * @date 2020-06-06 15:02:11
 */
class ProductprivilegeController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (ProductPrivilegeModel $item) {
            $item->product_name = $item->product->pname;
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
        foreach ($productArr as &$v) {
            $v['pname'] .= '---'.$v['id'];
        }
        $productArr = array_column($productArr, 'pname', 'id');
        $this->assign('productArr', $productArr);
        $this->assign('privilegeArr', []);
        $this->display();
    }


    protected function getModelObject()
    {
        return ProductPrivilegeModel::with(['product']);
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return ProductPrivilegeModel::class;
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