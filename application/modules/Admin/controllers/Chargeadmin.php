<?php

/**
 * Class ChargeadminController
 * @author xiongba
 * @date 2020-08-14 09:45:57
 */
class ChargeadminController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->assign('admin', $this->getUser());
        $this->display();
    }


    // protected function getModelObject()
    // {
    //     return ChargeAdminModel::with('member');
    // }


    public function saveAction()
    {
        try {
            $data = $_POST;
            $logDesc = $_POST['logDesc'] ?? '';
            if (empty($logDesc)){
                $logDesc = '客服处理';
            }

            $model = ChargeAdminModel::make($data);
            $model->admin = $this->getUser()->username;
            $model->ip = client_ip();
            $model->addtime = time();
            $itOk = $model->save();
            test_assert($itOk , '上下分保存失败');

            $score = $model->score;

            /** @var MemberModel $member */
            $member = MemberModel::where('aff' ,$model->to_aff)->first();
            test_assert($member , '用户不存在');

            if ($model->score_type == ChargeAdminModel::SCORE_TYPE_MONEY) {
                $source = MoneyLogModel::SOURCE_MANAGEMENT_INSPECTION;
                if ($model->type == ChargeAdminModel::TYPE_SUB) {
                    $isOk = $member->subMoney($score, $source, $logDesc);
                } else {
                    $isOk = $member->addMoney($score, $source, $logDesc);
                }
                test_assert($isOk, '记录日志失败');
            } elseif ($model->score_type == ChargeAdminModel::SCORE_TYPE_INCOME) {
                $source = MoneyIncomeLogModel::SOURCE_KEFU;
                if ($model->type == ChargeAdminModel::TYPE_SUB) {
                    $isOk = $member->subIncome($score, $member, null,$source, $logDesc);
                } else {
                    $isOk = $member->addIncome($score, $member, null,$source, $logDesc);
                }
                test_assert($isOk, '记录日志失败');
            } elseif ($model->score_type == ChargeAdminModel::SCORE_TYPE_ROYALTIES) {
                $source = MoneyIncomeLogModel::SOURCE_GAOFEI;
                if ($model->type == ChargeAdminModel::TYPE_SUB) {
                    $isOk = $member->subIncome($score, $member, null,$source, $logDesc);
                } else {
                    $isOk = $member->addIncome($score, $member, null,$source, $logDesc);
                }
                test_assert($isOk, '记录日志失败');
            }
            if (empty($itOk)){
                throw new \Exception('日志操作失败');
            }
//            $itOk = $member->save();
//            if (empty($itOk)){
//                throw new \Exception('用户更新失败');
//            }
//            $member->clearCached();
            return $this->ajaxSuccessMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }


    }


    public function delAction()
    {
    }

    public function delAllAction()
    {
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return ChargeAdminModel::class;
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
}