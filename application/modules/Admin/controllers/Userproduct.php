<?php

class UserproductController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->status_str = UserProductModel::STATUS[$item->status];
            $item->type_str = UserProductModel::TYPE[$item->type];
            return $item;
        };
    }

    /**
     * 保存数据
     * @return bool
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        if (!$post['product_id'] || !$post['aff']) {
            return $this->ajaxError('参数必填');
        }
        $productModel = ProductModel::find($post['product_id']);
        UserProductModel::buy($post['aff'], $productModel);
        return $this->ajaxSuccessMsg('操作成功');
    }

    protected function deleteAfterCallback($model ,$isDelete)
    {
        if ($isDelete) {
            UserPrivilegeModel::where([
                ['product_id','=',$model->product_id],
                ['aff','=',$model->aff],
            ])->delete();
            //修改用户
            MemberModel::where('aff', $model->aff)->update([
                'vip_level' => 0,
                'expired_at' => date('Y-m-d H:i:s')
            ]);
            redis()->del(UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE.$model->aff);
        }
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $productArr = ProductModel::where('type', ProductModel::GOODS_TYPE_VIP)->get()->toArray();
        $productArr = array_column($productArr, 'pname', 'id');
        foreach ($productArr as $k => &$v) {
            $v .= "--{$k}";
        }
        $this->assign('productArr', $productArr);
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return UserProductModel::class;
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