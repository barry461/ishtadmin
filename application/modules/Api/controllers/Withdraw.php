<?php

use service\UserService;

class WithdrawController extends BaseController
{

    public function indexAction(): bool
    {
        $min_money = WITHDRAW_MIN_LIMIT; //最小提现额度
        return $this->showJson([
            'rule_text' => str_render(setting('withdraw:rule-proxy','') , ['min_money' => $min_money]),
            'rule_coins_text' => str_render(setting('withdraw:rule-coins','') , ['min_money' => $min_money]),
            'income_money' => $this->member->income_money,
            'income_royalties' => $this->member->income_royalties ?? 0,
            'income_rate' => WithdrawLogModel::MONEY_CHARGE,
            'proxy_rate' => WithdrawLogModel::CHARGE,
            'proxy_money' => $this->member->proxy_money,
            'calc_formula' => setting('withdraw:calc',''),
            'calc_formula_royalties' => setting('withdraw:calc_royalties',''),
            'role_gaofei' => setting('withdraw:rule-gaofei',''),
            'scale_tip' => "100元=1000个金币",
        ]);
    }

    public function calcAction()
    {
        try {
            $money = intval($this->data['money'] ?? 0);
            if ($money < WITHDRAW_MIN_LIMIT) {
                throw new RuntimeException('价格错误');
            }
            return $this->showJson(['result' => WithdrawLogModel::calc($money)]);
        } catch (\Throwable $e) {
            return $this->showJson($e->getMessage());
        }
    }

    public function create_withdraw_logAction(): bool
    {
        $Validator = \helper\Validator::make($this->data, [
            'amount' => 'required|numeric|min:100'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $amount = $this->data['amount'] ?? 0;
        if ($amount % 100 != 0){
            return $this->errorJson('申请的提现金额，只能是100的整数倍！');
        }
        $member = $this->member;

        try {
            $this->verifyMemberSayRole();
            $this->verifyFrequency(1 , 5 , '' , '每次提现，需要间隔至少5秒钟');

            $log = WithdrawLogModel::where('aff', $member->aff)
                ->where('status', WithdrawLogModel::STATUS_INIT)
                ->first();
            if ($log) {
                throw new RuntimeException('您有一笔提现未处理,请耐心等待');
            }
            transaction(function () use ($member , $amount){
                // $needCoin = intval($amount * WithdrawLogModel::COINS_RATIO * (1 + WithdrawLogModel::MONEY_CHARGE));
                $needCoin = $amount; // 没有手续费
                if ($member->income_royalties < $needCoin) {
                    throw new RuntimeException('稿费余额不足');
                }
                $payMoney = $amount;
                $logData = [
                    'uuid' => $member->uuid,      //用户uuid
                    'aff' => $member->aff,      //用户uuid
                    'cash_id' => 'kefu',   //提现订单号
                    'type' => '0',      //提现方式 0 支付宝 1银行卡 2转会员卡 3购买浪花
                    'account' => 'kefu',   //提现账号
                    'name' => 'kefu',      //提现订单号
                    'status' => WithdrawLogModel::STATUS_INIT,    //订单状态
                    'ip' => $this->position['ip'],
                    'local' => $this->position['area'],
                    'withdraw_from' => WithdrawLogModel::WITHDRAW_FROM_GAOFEI,
                    'descp' => '申请提现',
                    'amount' => $payMoney,
                    'coins' => $needCoin,
                    'charge' => WithdrawLogModel::MONEY_CHARGE,
                ];
                $withdraw = WithdrawLogModel::create($logData);
                test_assert($withdraw, '提现操作一次，请重试');
                $isOk = $member->subIncome($needCoin , $member , $withdraw , MoneyIncomeLogModel::SOURCE_GAOFEI , '提现扣除');
                test_assert($isOk, '扣款失败');
                cached('')->group('list_withdraw');
            });

            return $this->successMsg('发起提现成功！请尽快使用电报联系官方负责人提交收款信息进行审核～ 
电报联系官方负责人： https://t.me/limingK0229

温馨提示:
Telegram ，又名电报，该聊天软件需要开启VPN翻墙后才能正常使用。
电报官网：https://www.telegram.org/


按钮【前往联系官方审核】 https://t.me/limingK0229');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }


    }

    public function create_withdrawAction(): bool
    {
        // return $this->errorJson('操作失败！请稍后重试');
        $Validator = \helper\Validator::make($this->data, [
            'card_id' => 'required|numeric',
            'withdraw_from' => 'required|enum:1,2,3',
            'amount' => 'required|numeric|min:100'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        //判断是否是提现黑名单
        $isBlack = WithdrawBlackModel::isBlack($this->member->aff);
        if ($isBlack){
            return $this->errorJson('提现黑名单不能提现,请联系管理员');
        }

        $fromLog = data_get($this->data ,'from_log');



        /** @var UserBankcardModel $cardModel */
        $cardModel = UserBankcardModel::where([
            'aff' => $this->member->aff,
            'id' => $this->data['card_id'],
        ])->first();
        if (empty($cardModel)) {
            return $this->errorJson('请先绑定银行卡');
        }

        $account = $cardModel->card;   // 提现账号/USDT地址
        $name = $cardModel->name;   // 提现名字/USDT协议
        $amount = $this->data['amount'];                // 提现金额
        $withdraw_from = (int)$this->data['withdraw_from'] ?? 0;  //1 全民代理，2 收益 3=投稿收益提现
        $cash_id = generate_order_no();  // 提现订单号
        $min_money = WITHDRAW_MIN_LIMIT; //最小提现额度
        $data['code'] = 0;
        $data['msg'] = '官方代理不支持当前渠道提现！';

        if ($cardModel->type == UserBankcardModel::TYPE_BANK){
            $type = 1;                  // 提现方式 0 支付宝 1银行卡 2 USDT
        }elseif ($cardModel->type == UserBankcardModel::TYPE_USDT){
            if ($amount < 10000){
                return $this->errorJson('单笔USDT提现到账金额应大于10000RMB');
            }
            $type = 2;                  // 提现方式 0 支付宝 1银行卡 2 USDT
        }else{
            return $this->errorJson('卡类型错误');
        }

        try {
            $this->verifyMemberSayRole();
            $this->verifyFrequency(1 , 5 , '' , '每次提现，需要间隔至少5秒钟');
            if ($amount < $min_money) {
                return $this->errorJson('为方便提现，请输入' . $min_money . '元金额的倍数');
            }

            test_assert(($amount % 100) == 0, '提现价格必须是100的整数');
            $member = $this->member->refresh();
            test_assert($member, '账号错误');
            $log = WithdrawLogModel::where('aff', $member->aff)->where('status', WithdrawLogModel::STATUS_INIT)->first();
            if ($log) {
                throw new RuntimeException('您有一笔提现未处理,请耐心等待');
            }

            transaction(function () use ($member, $amount, $cash_id, $type, $account, $name, $withdraw_from,$fromLog) {
                $logData = [
                    'uuid' => $member->uuid,      //用户uuid
                    'aff' => $member->aff,      //用户uuid
                    'cash_id' => $cash_id,   //提现订单号
                    'type' => $type,      //提现方式 0 支付宝 1银行卡 2USDT
                    'account' => $account,   //提现账号
                    'name' => $name,      //提现订单号
                    'status' => WithdrawLogModel::STATUS_INIT,    //订单状态
                    'ip' => $this->position['ip'],
                    'local' => $this->position['area'],
                    'withdraw_from' => $withdraw_from,
                    'descp' => '申请提现',
                ];
                if ($withdraw_from == WithdrawLogModel::WITHDRAW_FROM_PROXY) {
                    throw new RuntimeException('代理提现通道关闭');
                    $coin = $member->proxy_money;
                    $needCoin = $amount * (1 + WithdrawLogModel::CHARGE);
                    if (bccomp($coin, $needCoin) == -1) {
                        throw new RuntimeException('代理可提现余额不足');
                    }
                    $payMoney = $amount;
                    $logData = array_merge($logData, [
                        'amount' => $payMoney,  //金额
                        'coins' => $needCoin,
                        'charge' => WithdrawLogModel::CHARGE,
                    ]);
                    $withdraw = WithdrawLogModel::create($logData);
                    test_assert($withdraw, '提现操作一次，请重试');
                    UserService::updateProxyMoney(
                        $this->member,
                        MoneyLogModel::TYPE_SUB,
                        UserProxyCashBackDetailModel::SOURCE_WITHDRAW,
                        $needCoin,
                        null,
                        null,
                        $member->aff,
                        $withdraw->id
                    );
                }
                elseif ($withdraw_from == WithdrawLogModel::WITHDRAW_FROM_INCOME) {
                    $needCoin = WithdrawLogModel::calc($amount);
                    if ($member->income_money < $needCoin) {
                        throw new \Exception('金币可提现余额不足');
                    }
                    $payMoney = $amount;
                    $logData = array_merge($logData, [
                        'amount' => $payMoney,
                        'coins' => $needCoin,
                        'charge' => WithdrawLogModel::MONEY_CHARGE,
                    ]);
                    $withdraw = WithdrawLogModel::create($logData);
                    test_assert($withdraw, '提现日志添加失败，请重试');
                    $isOk = $member->subIncome($needCoin,$member,$withdraw,MoneyIncomeLogModel::SOURCE_SUB_WITHDRAW,'提现');
                    test_assert($isOk, '提现操作失败，请重试');
                }
                elseif ($withdraw_from == WithdrawLogModel::WITHDRAW_FROM_GAOFEI) {
                    if ($member->income_royalties < $amount) {
                        throw new \Exception('可提现稿费余额不足');
                    }
                    $logData = array_merge($logData, [
                        'amount' => $amount,
                        'coins' => $amount,
                        'charge' => 0,
                    ]);
                    $withdraw = WithdrawLogModel::create($logData);
                    test_assert($withdraw, '提现日志添加失败，请重试');
                    $isOk = $member->subIncome($amount,$member,$withdraw,MoneyIncomeLogModel::SOURCE_GAOFEI,'稿费提现');
                    test_assert($isOk, '提现操作失败，请重试');
                } else {
                    throw new \Exception('不支持的提现类型');
                }
            });
            $this->member->clearCached();
            cached('')->clearGroup('list_withdraw');
            return $this->successMsg('提现申请成功，请等待工作人员审核。审核通过后1个工作日内到账，请注意查收。');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_withdrawAction(): bool
    {
        $Validator = \helper\Validator::make($this->data, [
            'page' => 'required|numeric'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $limit = 10;
        $redisKey = WithdrawLogModel::REDIS_KEY_WITHDRAW_LIST . $this->member->aff . ':' . $this->page;
        $list = cached($redisKey)
            ->group('list_withdraw')
            ->fetchPhp(function () use ($limit) {
                return WithdrawLogModel::where('uuid', $this->member->uuid)
                    ->forPage($this->page, $limit)
                    ->orderByDesc('id')
                    ->get();
            } , 600);
        return $this->showJson($list);
    }


}