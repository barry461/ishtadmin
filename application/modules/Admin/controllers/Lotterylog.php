<?php

class LotteryLogController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (LotteryLogModel $item) {
            $item->setHidden([]);
            $item->lottery_name = $item->lottery->lottery_name;
            $item->giveaway_type_str = LotteryItemModel::GIVEAWAY_TYPE[$item->giveaway_type];
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->assign('lotteryAry' , LotteryModel::pluck('lottery_name','id' )->toArray());
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return LotteryLogModel::class;
    }

    public function getModelObject()
    {
        return LotteryLogModel::with('lottery');
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'log_id';
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