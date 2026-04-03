<?php

/**
 * Class WithdrawblackController
 * @date 2023-06-09 14:17:16
 */
class WithdrawblackController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (WithdrawBlackModel $item) {
            $item->setHidden([]);
            $item->nickname_str = '用户已注销';
            if ($item->member){
                $item->nickname_str = $item->member->nickname ?? '用户已注销';
            }
            $item->status_str = WithdrawBlackModel::STATUS_LIST[$item->status];

            return $item;
        };
    }

    public function unblackAction(){
        $id = $_POST['id'] ?? 0;
        if (!$id){
           return $this->ajaxError("数据异常");
        }
        $black = WithdrawBlackModel::find($id);
        if (!$black){
            return $this->ajaxError("数据异常");
        }

        $black->status = WithdrawBlackModel::STAUS_NO;
        $remark = sprintf('//取消拉黑状态时间:%s',\Carbon\Carbon::now());
        $black->remark .= $remark;
        $black->save();

        return $this->ajaxSuccessMsg('取消拉黑成功');
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
       return WithdrawBlackModel::class;
    }

    protected function getModelObject()
    {
        return WithdrawBlackModel::with('member');
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