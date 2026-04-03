<?php

/**
 * Class ProductController
 * @author xiongba
 * @date 2020-06-06 15:02:11
 */
class ProductController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (ProductModel $item) {
            $item->setHidden([]);
            $item->img_url = url_image($item->img);
            $item->second_img_url = url_image($item->second_img);
            $item->show_more_str = ProductModel::SHOW_MORE[$item->show_more];
            $item->promo_expire_time_str = date('Y-m-d', strtotime($item->promo_expire_time));
            $item->price_yuan = $item->price / 100;
            $item->promo_price_yuan = $item->promo_price / 100;
            $item->privileges = $item->privilege->groupBy('resource_type')->map(function (\Illuminate\Support\Collection $items){
                return $items->keyBy('privilege_type');
            });
            return $item;
        };
    }


    protected function getModelObject()
    {
        return ProductModel::with('privilege');
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        define('TITLE' , '');
        $this->assign('resource_list' , ProductPrivilegeModel::RESOURCE_TYPE);
        $this->assign('privilege_list' , ProductPrivilegeModel::PRIVILEGE_TYPE);
        $this->display();
    }


    public function save_qxAction()
    {

        $id = $_POST['product_id'];
        //
        $data = [];
        foreach ($_POST['privilege'] as $resource_type => $privilege_list) {
            foreach ($privilege_list as $privilege_type => $item) {
                if (isset($item['status'])) {
                    $data[] = [
                        'product_id' => $id,
                        'privilege_id' => 0,
                        'resource_type' => $resource_type,
                        'privilege_type' => $privilege_type,
                        'value' => intval($item['value'] ?? 0),
                    ];
                }
            }
        }

        if (!empty($data)){
            ProductPrivilegeModel::where('product_id' , $id)->delete();
            ProductPrivilegeModel::insert($data);
        }
        return $this->ajaxSuccessMsg('ok', 0, $data);
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return ProductModel::class;
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