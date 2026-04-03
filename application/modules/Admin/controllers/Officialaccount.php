<?php

/**
 * Class OfficialaccountController
 * @date 2024-07-04 13:11:24
 */
class OfficialaccountController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (OfficialAccountModel $item) {
            $item->setHidden([]);
            $item->nickname = $item->member->nickname;
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
       return OfficialAccountModel::class;
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

    public function createAfterCallback($model, $oldModel = null)
    {
        if ($model){
            redis()->sAdd(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $model->aff);
        }
    }

    public function deleteAfterCallback($model, $isDelete)
    {
        if ($isDelete){
            redis()->sRem(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $model->aff);
        }
    }

    public function refreshAction(){
        $list = OfficialAccountModel::query()->get()->pluck('aff')->toArray();
        redis()->del(OfficialAccountModel::OFFICIAL_ACCOUNT_SET);
        redis()->sAddArray(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $list);
        return $this->ajaxSuccessMsg('操作成功');
    }
}