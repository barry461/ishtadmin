<?php

use service\UserService;
/**
 * Class WithdrawlogController
 */
class WithdrawlogController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    protected function sendWithdrawRequest(MemberModel $member, WithdrawLogModel $model)
    {
        //throw new \Exception('提现通知地址没有配置');
        $data = [
            'app_name' => VIA,
            'app_type' => in_array($member->oauth_type, ['pc', 'ios', 'android']) ? $member->oauth_type : 'pc',
            'username' => $model->name,
            'type' => WithdrawLogModel::DRAW_WAY[$model->type],
            'card_number' => (string)trim($model->account),
            'amount' => (string)$model->amount,
            'aff' => (string)$member->aff,
            'app_id' => (string)$model->id,
            'notify_url' => NOTIFY_URL .'/notify/notify_withdraw',
        ];

        ksort($data);
        $str = implode('', $data);
        $data['sign'] = md5($str . config('withdraw.key'));
        $curl = new \tools\HttpCurl();
        $result = $curl->post(config('withdraw.url'), $data);
        trigger_log('withdrawData' . $result);
        error_log('withdrawData：' . $result . PHP_EOL, 3, APP_PATH . '/storage/logs/withdraw.log');
        return json_decode($result, true);
    }


    public function getDesc($query): string
    {
        $proxyQuery = $query->clone()->where('withdraw_from' , WithdrawLogModel::WITHDRAW_FROM_PROXY);
        $incomeQuery = $query->clone()->where('withdraw_from' , WithdrawLogModel::WITHDRAW_FROM_INCOME);
        $gfQuery = $query->clone()->where('withdraw_from' , WithdrawLogModel::WITHDRAW_FROM_GAOFEI);
        $successWhere = [WithdrawLogModel::STATUS_PASS , WithdrawLogModel::STATUS_SUCCESS];

        return sprintf("总申请：%.2f, 代理提现通过:%.2f, 收益通过:%.2f, 稿费通过:%.2f, 总通过 :%.2f",
            htdiv($query->clone()->sum('amount')),
            htdiv($proxyQuery->whereIn('status' , $successWhere)->sum('amount')),
            htdiv($incomeQuery->whereIn('status' , $successWhere)->sum('amount')),
            htdiv($gfQuery->whereIn('status' , $successWhere)->sum('amount')),
            htdiv($query->whereIn('status' , $successWhere)->sum('amount'))
        );
    }


    /**
     * 手动标记提现成功
     * @return bool
     */
    public function withdrawAction(): bool
    {
        try {
            $id = $_POST['id'] ?? null;
            $key = 'withdraw:log:' . $id;
            $isOk = redis()->setnxttl($key, 1, 1800);
            test_assert($isOk, '操作每条提现，需要间隔半个小时');

            transaction(function () use ($id, $key) {
                /** @var WithdrawLogModel $model */
                $model = WithdrawLogModel::onWriteConnection()->lockForUpdate()->find($id);
                test_assert($model, '数据不存在');
                if ($model->type != 2){
                    if (!in_array($model->status, [WithdrawLogModel::STATUS_INIT, WithdrawLogModel::STATUS_UNFREEZE])) {
                        test_assert(false, '当前状态不能操作');
                    }
                }else{
                    if ($model->status != WithdrawLogModel::STATUS_PASS) {
                        test_assert(false, '当前状态不能操作');
                    }
                }

                $model->status = WithdrawLogModel::STATUS_SUCCESS;
                $model->descp = "手动标记成功";
                $model->channel = 'kefu';
                $model->cash_id = 'kefu';
                $isOk = $model->save();
                test_assert($isOk, '操作失败');
                cached('')->group('list_withdraw');
            });
            return $this->ajaxSuccessMsg('操作成功');
        }
        catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 通过提现审核
     * @return bool
     */
    public function pass_withdrawAction(): bool
    {
        try {
            $id = $_POST['id'] ?? null;
            $key = 'withdraw:log:' . $id;
            $isOk = redis()->setnxttl($key, 1, 1800);
            test_assert($isOk, '操作每条提现，需要间隔半个小时');

            transaction(function () use ($id, $key) {
                /** @var WithdrawLogModel $model */
                $model = WithdrawLogModel::onWriteConnection()->lockForUpdate()->find($id);
                test_assert($model, '数据不存在');
                if (!in_array($model->status, [WithdrawLogModel::STATUS_INIT, WithdrawLogModel::STATUS_UNFREEZE])) {
                    test_assert(false, '当前状态不能操作');
                }
                /** @var MemberModel $member */
                $member = MemberModel::findByAff($model->aff);
                test_assert($member, '用户不存在');
                //USDT不核实姓名
                if ($model->type != 2){
                    if ($model->name != $member->draw_name) {
                        redis()->del($key);
                        test_assert(false, '提现姓名没有核实');
                    }
                    $result = $this->sendWithdrawRequest($member, $model);
                    if (true !== data_get($result, 'success', false)) {
                        throw new \Exception('提现失败1');
                    }
                    $model->status = WithdrawLogModel::DRAW_STATUS_WAIT;
                    $model->channel = $result['data']['channel'];
                    $model->cash_id = $result['data']['order_id'];
                    $isOk = $model->save();
                    test_assert($isOk, '操作失败');
                }else{
                    $model->status = WithdrawLogModel::STATUS_PASS;
                    $model->channel = '';
                    $isOk = $model->save();
                    test_assert($isOk, '操作失败');
                }

                cached('')->group('list_withdraw');
            });
            return $this->ajaxSuccessMsg('操作成功');
        }
        catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     *  操作冻结和解冻
     */
    public function withdrawHandleAction() 
    {
        $id = intval($_POST['id'] ?? null);
        $type = intval($_POST['type'] ?? null);       // 1冻结   2解冻
        $content = strval($_POST['reply'] ?? null);

        if ($type == 1 && !$content) {
            return $this->ajaxError('冻结理由不能为空');
        }
        $withdraw = WithdrawLogModel::find($id);
        if (!$withdraw) {
            return $this->ajaxError('提现订单不存在');
        }

        switch ($withdraw->status) {
            case WithdrawLogModel::STATUS_INIT:
            case WithdrawLogModel::STATUS_UNFREEZE:
            case WithdrawLogModel::STATUS_FREEZE:
                if ($type == 1) {
                    $data = ['status' => WithdrawLogModel::STATUS_FREEZE, 'descp' => $content];
                    $txt = '手动冻结';
                } else {
                    $data = ['status' => WithdrawLogModel::STATUS_UNFREEZE, 'descp' => ''];
                    $txt = '手动解冻';
                }
                WithdrawLogModel::where('id', $id)->update($data);
                return $this->ajaxSuccess('操作成功');
            default:
                return $this->ajaxError('此订单状态不能再进行解冻和冻结处理');
        }

    }

    // 用户提现拒绝
    public function noWithdrawAction(): bool
    {
        $id = intval($_POST['id'] ?? null);
        $content = strval($_POST['reply'] ?? null);
        if(!$id) {
            return $this->ajaxError('该订单不存在!');
        }
        if (!$content) {
            return $this->ajaxError('拒绝理由不能为空!');
        }
        /** @var WithdrawLogModel $withdraw */
        $withdraw = WithdrawLogModel::useWritePdo()->find($id);
        if (!$withdraw) {
            return $this->ajaxError('该订单不存在或者是该订单不是待审核状态');
        }
        if ($withdraw->status !== WithdrawLogModel::STATUS_INIT) {
            return $this->ajaxError('该订单不是待审核状态');
        }

        /** @var MemberModel $member */
        $member = MemberModel::findByAff($withdraw->aff);
        try {
            transaction(function () use ($withdraw, $content, $member) {
                $isOk = $withdraw->update(['status' => WithdrawLogModel::STATUS_PERSON_FAILURE, 'descp' => $content]);
                test_assert($isOk, '更新失败');
                $source = $withdraw->withdraw_from == WithdrawLogModel::WITHDRAW_FROM_GAOFEI
                    ? MoneyIncomeLogModel::SOURCE_GAOFEI
                    : MoneyIncomeLogModel::SOURCE_ADD_WITHDRAW;
                $member->addIncome($withdraw->coins, $member, $withdraw, $source, '提现退回', false);
                test_assert($isOk, '记录日志失败');
                MemberModel::clearFor($member);
                cached('')->group('list_withdraw');
            });
            return $this->ajaxSuccess('操作成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 查看内容
     * @return bool
     * @author xiongba
     */
    public function viewerAction()
    {
        $id = $_POST['id'] ?? null;
        $model = WithdrawLogModel::find($id);
        if (empty($model)) {
            return $this->ajaxSuccessMsg('参数错误');
        }
        $uuid = $model->uuid;
        //--------显示的业务逻辑--------


        //----------------

        $html = "提现单号：$model->cash_id";
        return $this->ajaxSuccessMsg($html);
    }

    /**
     * 拉黑
     * @return bool
     * @author xiongba
     */
    public function blackAction()
    {
        $data = $_POST;
        $aff = $data['aff'] ?? 0;
        $reason = $data['reason'] ?? 0;
        $remark = $data['remark'] ?? '';
        if (!$aff || !$reason){
            return $this->ajaxSuccessMsg('参数错误');
        }
        $reason = WithdrawBlackModel::REASON_LIST[$reason];
        /* @var WithdrawBlackModel $model**/
        $model = WithdrawBlackModel::query()->where('aff',$aff)->first();
        if ($model){
            if ($model->status == WithdrawBlackModel::STAUS_OK){
                return $this->ajaxSuccessMsg('用户已经是拉黑状态');
            }
            $model->status = WithdrawBlackModel::STAUS_OK;
            $model->reason = $reason;
            $model->remark .= "//".$remark;
            $model->updated_at = \Carbon\Carbon::now();
        }else{
            $model = WithdrawBlackModel::make();
            $model->aff = $aff;
            $model->reason = $reason;
            $model->remark = $remark;
            $model->status = WithdrawBlackModel::STAUS_OK;
            $model->created_at = \Carbon\Carbon::now();
            $model->updated_at = \Carbon\Carbon::now();
        }
        $isOk = $model->save();
        if ($isOk){
            return $this->ajaxSuccessMsg('拉黑成功');
        }else{
            return $this->ajaxError('拉黑失败，请重试');
        }
    }



    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (WithdrawLogModel $item) {
            $item->setHidden([]);
            $item->nickname_str = '用户已注销';
            if ($item->member){
                $item->nickname_str = $item->member->nickname ?? '用户已注销';
            }
            $item->type_str = WithdrawLogModel::TYPE[$item->type];
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
       return WithdrawLogModel::class;
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


    public function delAction()
    {
    }

    public function saveAction()
    {
    }

    public function delAllAction()
    {
    }

}