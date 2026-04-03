<?php

class LotteryitemController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (LotteryItemModel $item) {
            $item->setHidden([]);
            $item->status_str = LotteryItemModel::STATUS_TIPS[$item->item_status] ?? '';
            $item->show_str = LotteryItemModel::STATUS_TIPS[$item->is_show] ?? '';
            $item->win_str = LotteryItemModel::WIN_TIPS[$item->is_win] ?? '';
            $item->giveaway_type_str = LotteryItemModel::GIVEAWAY_TYPE[$item->giveaway_type] ?? '';
            $item->lottery_name = $item->lottery->lottery_name;
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
        return LotteryItemModel::class;
    }

    public function getModelObject()
    {
        return LotteryItemModel::with('lottery');
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'item_id';
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