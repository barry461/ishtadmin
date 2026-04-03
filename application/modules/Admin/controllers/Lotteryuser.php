<?php

class LotteryUserController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (LotteryUserModel $item) {
            $item->setHidden([]);
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
        $lotterys = LotteryModel::get()->pluck('lottery_name', 'id')->toArray();
        $this->assign('lotterys', $lotterys);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return LotteryUserModel::class;
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

    public function addLotteryTimesAction(){
        try {
            $aff = $_POST['aff'] ?? 0;
            $val = $_POST['val'] ?? 0;
            $egg_id = $_POST['lottery_id'] ?? 0;
            test_assert($aff, 'aff必填');
            test_assert($val, '次数必填');
            test_assert($egg_id, '活动必选');
            $member = MemberModel::firstAff($aff);
            test_assert($member, 'aff对应的用户不存在');
            LotteryUserModel::addUserLottery($member, $val, $egg_id);

            return $this->ajaxSuccessMsg("操作成功");
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

}