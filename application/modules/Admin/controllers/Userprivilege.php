<?php

class UserprivilegeController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->status_str = UserPrivilegeModel::STATUS[$item->status];
            $item->resource_type_str = ProductPrivilegeModel::RESOURCE_TYPE[$item->resource_type];
            $item->privilege_type_str = ProductPrivilegeModel::PRIVILEGE_TYPE[$item->privilege_type];
            $item->expired_time_str = date('Y-m-d', strtotime($item->expired_time));
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

        if (!$post['expired_time']) {
            return $this->ajaxError('过期时间必填');
        }
        try {
            if ($model = $this->doSave($post)) {
                return $this->ajaxSuccessMsg('操作成功', 0, call_user_func($this->listAjaxIteration(),$model));
            } else {
                return $this->ajaxError('操作错误');
            }
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        redis()->del(UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE.$model->aff);
    }

    protected function deleteAfterCallback($model ,$isDelete)
    {
        if ($isDelete) {
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
       return UserPrivilegeModel::class;
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