<?php

use service\PayorderService;
use service\UserService;
use Carbon\Carbon;

class OrderController extends BaseController
{

    /**
     * 获取商品列表
     */
    public function goodsListAction():bool
    {
        $typeAry = [
            ProductModel::GOODS_TYPE_VIP,
            ProductModel::GOODS_TYPE_COIN,
            ProductModel::GOODS_TYPE_COIN_BUY_VIP ,
            ProductModel::GOODS_TYPE_SPECIAL_COIN_CARD
        ];
        $Validator = \helper\Validator::make($this->data, [
            'type' => 'required|enum:' . join(',',$typeAry)
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $showMore = $this->data['show_more'] ?? 1;
        $data['product'] = PayorderService::getProductList($this->member , $this->data['type'], $showMore);
//        $data['product_coins_text'] = $data['product_vip_text'] = '1.跳转后请及时付款，超时支付无法到账，需要重新发起。
//2.支付成功后，会有一定的时间延迟，请耐心等待。当等待时间过长时，欢迎骚扰客服小姐姐。
//3.每天发起支付不可超过5次，连续发起且未支付，当前账号将会加入黑名单。
//4.支付通道在夜间较忙碌，为保证您的体验，尽量选择白天支付。
//5.当选择的支付方式无法支付时，请切换不同支付方式尝试。';
        $data['product_coins_text'] = $data['product_vip_text'] = setting('pay_tips', '');
        if ($this->data['type'] != ProductModel::GOODS_TYPE_COIN_BUY_VIP) {
            $data['channel'] = PayorderService::getPayTypeList();
            $data['vip_value'] = MemberModel::getVipValue($this->member->vip_level);
            foreach ($data['product'] as $k => &$v) {
                if ($this->member->temp_vip == 1) {
                    if ($v['pname'] == '钻石会员7天体验卡') {
                        unset($data['product'][$k]);
                    }
                }
            }
            // shuffle($data['channel']);
        } else {
            foreach ($data['product'] as $k => &$v) {
                $v['price'] = $v['price'] * 10;
                $v['promo_price'] = $v['promo_price'] * 10;
            }
        }
        return $this->showJson($data);
    }

    /**
     * 获取订单列表
     */
    public function orderListAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'page' => 'required|numeric',
            'type' => 'required|enum:' . ProductModel::GOODS_TYPE_VIP . ',' . ProductModel::GOODS_TYPE_COIN. ',' . ProductModel::GOODS_TYPE_SPECIAL_COIN_CARD
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        // dd($this->member->uuid);
        $data = $this->data;
        $type = $data['type'];
        $member = $this->member->toArray();
        $page = $data['page'];
        $redisKey = OrdersModel::REDIS_KEY_ORDER_LIST . $this->member->aff . ':' . $page . $type;
        // $rows = redis()->getWithSerialize($redisKey);
        // if (!$rows) {
            $rows = PayorderService::getOrderList($member['uuid'], $page, 50, $type);
            redis()->setWithSerialize($redisKey, $rows, 600);
        // }
        return $this->showJson($rows);
    }

    /**
     * 创建交易订单
     */
    public function createPayingAction()
    {
        $payType = PayorderService::getPayType(true);
        $payWay = PayorderService::getPayWay(true);
        $Validator = \helper\Validator::make($this->data, [
            'pay_way' => 'required|enum:' . $payWay,
            'pay_type' => 'required|enum:' . $payType,
            'product_id' => 'required|numeric'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $member = $this->member;
        $data = $this->data;
        // $min = config('app.pay.min');   // 分钟
        // $count = config('app.pay.count'); // 笔数
        // $time = TIMESTAMP - ($min * 60);
        // $total = redis()->get(OrdersModel::REDIS_INIT_COUNT.$this->member->aff.':1');
        // if (!$total) {
        //     $total = OrdersModel::where([
        //         ['uuid', '=', $this->member->uuid],
        //         ['status', '=', OrdersModel::PAY_STAT_TO_PAY],
        //         ['created_at', '>=', $time],
        //         ['order_type', '!=', 99]
        //     ])->count();
        // }
        // if ($total >= $count) {
        //     $bool = UserService::checkCaptcha($this->member->aff, $data['code'] ?: 0);
        //     if (!$bool) {
        //         return $this->errorJson('验证码不正确');
        //     }
        // }
            
        $pay_way = $data['pay_way'];
        $pay_type = $data['pay_type'];
        if ($pay_way == 'agent') {
            $pay_type = 'agent';
            $pay_way = 'alipay';
        }
        $oauth_type = $member->oauth_type;

        $product_id = $data['product_id'];
        $sdk = $this->data['sdk'] ?? 0;
        if ($pay_way) {
            if ($pay_way == 'wechat' || $pay_way == 'usdt') {
                $sdk = 0;
            }
        }

        //1分钟内只能有一次未付款 的请求，免得恶意访问
        $orderLog = PayorderService::getLeastOrder($member->uuid);
        if ($orderLog){
            $orderCreatedAt = Carbon::parse($orderLog['created_at']);
            if($orderCreatedAt->gt(Carbon::now()->subMinute()) && $orderLog['product_id'] == $product_id && $orderLog['status'] == OrdersModel::PAY_STAT_TO_PAY) {
                return $this->errorJson('有未完成交易，请勿频繁请求');
            }
        }
        
        /** @var ProductModel $product */
        $product = PayorderService::getProductDetail($product_id);
        if (empty($product) || $product->status != ProductModel::STATUS_LISTED) {
            return $this->errorJson('该产品不存在');
        }
        if($product->type == ProductModel::GOODS_TYPE_SPECIAL_COIN_CARD){
            $cardInfo = ProductModel::isBuyCoinMonthCard($this->member->uuid);
            if($cardInfo){
                return $this->failMsg('不能重复购买');
            }
        }
        $postdata['app_name'] = config('pay.app_name', 'qq');
        $postdata['app_type'] = $oauth_type;
        if (in_array($postdata['app_type'], ['web','macos','windows'])) {
            $postdata['app_type'] = 'pc';
        }
        $postdata['aff'] = $member->aff;
        $pay_amount = ($product->promo_price > 0) ? $product->promo_price : $product->price;
        $postdata['amount'] = (string)($pay_amount / 100);
        if ($pay_way == 'usdt') {
            $postdata['amount'] = (string)(round($pay_amount / 100 / 7));
        }
        //(T_ENV !='product') && $postdata['amount'] = 100;
        $sign = PayorderService::makePaySign($postdata, config('pay.pay_signkey'));
        $postdata['pay_type'] = $pay_way;
        $postdata['type'] = $pay_type;
        switch ($product->type) {
            case ProductModel::GOODS_TYPE_VIP:
                $postdata['product'] = 'vip';
                break;
            case ProductModel::GOODS_TYPE_COIN:
                $postdata['product'] = 'coins';
                break;
            default:
                $postdata['product'] = 'unknown';
        }
        $postdata['sign'] = $sign;
        $postdata['is_sdk'] = $sdk;
        $postdata['ip'] = client_ip();
        //        trigger_error('支付请求数据:' . var_export($postdata, true));
        $retrun = [];
        $curl = new \tools\HttpCurl();
        $result = $curl->post(config('pay.pay_url'), $postdata);
        trigger_log('支付返回数据:' . $result);
        $result = json_decode($result, true);
        if (json_last_error() != JSON_ERROR_NONE){
            return $this->failMsg('订单创建失败了哦');
        }
        $result = my_addslashes($result);
        if (isset($result['success']) && ($result['success'] == true || $result['success'] == 1)) {
            // 返回信息
            $retrun['code_url'] = isset($result['data']['code_url']) ? str_replace('&amp;', '&', $result['data']['code_url']) : '';
            $retrun['chatUrl'] = isset($result['data']['message_url']) ? str_replace('&amp;', '&', $result['data']['message_url']) : '';
            $retrun['payUrl'] = isset($result['data']['pay_url']) ? str_replace('&amp;', '&', $result['data']['pay_url']) : '';
            if ($retrun['payUrl']) {
                $retrun['payUrl'] = str_replace('&quot;', '"', $retrun['payUrl']);
                $retrun['payUrl'] = str_replace('&amp;', '&', $retrun['payUrl']);
            }
            $retrun['type'] = $result['data']['type'];
            // $retrun['type'] = 'sdk';
            $retrun['order_id'] = isset($result['data']['order_id']) ? $result['data']['order_id'] : '';
            // 如果代理订单已存在， 直接返回订单地址
            // if ($result['data']['channel'] == 'AgentPay' or $result['data']['channel'] == 'NewAgentPay') {
            //     $order = PayorderService::getLeastOrder('', $result['data']['order_id']);
            //     if ($order) {
            //         return $this->showJson($retrun);
            //     }
            // }
            if ($result['data']['channel'] == 'AgentPay') {
                $pay_type = "agent";
                AgentTmpPayingModel::createBy($member, $product, $pay_amount);
                return $this->showJson($retrun);
            } elseif ($result['data']['channel'] == 'NewAgentPay') {
                $pay_type = "newAgent";
            } else {
                $pay_type = "online";
            }
            $order = array(
                'uuid' => $member->uuid,
                'oauth_type' => $oauth_type,
                'product_id' => $product_id,
                'amount' => $pay_amount,
                'status' => 0,
                'order_id' => $result['data']['order_id'],
                'channel' => $result['data']['channel'],
                'descp' => $product->pname,
                'order_type' => $product->type,
                //返利活动 活动结束注释
                //exchange 5070
                // 'gift_diamond' => 2,
                'pay_type_sdk' => $result['data']['type'],
                'payway' => $pay_way,
                'expired_at' => (ProductModel::GOODS_TYPE_VIP == $product->type) ? $product->valid_date : 0,
                'pay_type' => $pay_type,
                'pay_url' => $result['data']['channel'] == 'NewAgentPay' ? str_replace('&amp;', '&', $result['data']['message_url']) : str_replace('&amp;', '&', $result['data']['pay_url']),
                'goods_info' => json_encode($product->getAttributes(), JSON_UNESCAPED_UNICODE),
                'build_id' => $member['channel']
            );
            // 生成订单存入数据库
            // \tools\Report::addOrderQueue($order, $this->member->aff);
            if (OrdersModel::create($order)) {
                if ($member['channel'] != 'self' && ProductModel::GOODS_TYPE_GAME != $order['order_type']) {
                    $order['channel'] = $member['channel'];
                    $order['invited_by'] = $member['invited_by'];
                    $order['phone'] = $member['phone'];
                    \tools\Channel::addOrderQueue($order);
                }
                //统计总订单
                SysTotalModel::incrBy('add-order');
                return $this->showJson($retrun);
            } else {
                return $this->errorJson('生成订单失败,请稍后重试');
            }
        }
        $msg = '创建订单失败';
        return $this->errorJson($msg);
    }

    public function withdrawAction(): bool
    {
        $this->forward('Withdraw', 'create_withdraw');
        return false;
    }

    public function listWithdrawAction(): bool
    {
        $this->forward('Withdraw' , 'list_withdraw');
        return false;
    }

    public function exchangeAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'product_id' => 'required|numeric'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }

        $product_id = $this->data['product_id'];
        $product = ProductModel::find($product_id);
        if (
            !$product
            || $product->status != ProductModel::STATUS_LISTED
            || $product->type != ProductModel::GOODS_TYPE_VIP
        ) {
            return $this->errorJson('该产品不存在');
        }
        $pay_amount = ($product->promo_price > 0) ? $product->promo_price : $product->price;
        $amount = $pay_amount / 10;
        $pay = $amount;
        try {
            \DB::beginTransaction();
            $member = $this->member->refresh();
            if ($member->money < $pay) {
                throw new RuntimeException('余额不足');
            }
            if ($member->channel != 'self') {
                $timestamp = time();
                $today = Carbon::today()->format('Ymd');
                $channel = ChannelModel::findByChanId($member->channel);
                // return $channel->channel_num ?? $channel_num;//兼容错误的channel_id
                $data = [
                    'type'      => 'addOrder',
                    'content'   => [
                        'product_id'    => 12,
                        'order_sn'      => 'hbcg-' . $today . $timestamp,
                        'order_type'    => '0', // 0 vip  1 jY币
                        'uuid'          => $member->uuid,
                        'invite_by'    => $member->invited_by,
                        'channel'       => $channel->channel_num,
                        'order_amount'  => $pay, // 单位为元，请注意
                        'status'        => 0,
                        'created_at'    => $timestamp,
                        'phone'    => '',
                    ]
                ];
                $rs = \tools\Channel::manSend($data);
                // var_dump($rs);exit;
                $data = [
                    'type' => 'updateOrder',
                    'content' => [
                        'product_id' => 12,
                        'order_sn' => 'hbcg-' . $today . $timestamp,
                        'pay_amount' => $pay, // 单位为元，请注意
                        'status' => '1',
                        'updated_at' => $timestamp + 10,
                    ]
                ];
                \tools\Channel::manSend($data);
                trigger_log('exchange_report:' . 'hbcg-' . $today . $timestamp . '|' . $pay);
            }
            UserProductModel::buy($this->member->aff,$product);

            $exchange = ExchangeModel::create([
                'aff' => $member->aff,
                'old_vip_level' => $member->vip_level,
                'new_vip_level' => $product['vip_level'],
                'product_id' => $product['id'],
                'amount' => $pay
            ]);
            $member->subMoney($pay, MoneyLogModel::SOURCE_EXCHANGE, '兑换会员', $exchange);

            \DB::commit();
            return $this->successMsg('成功');
        }catch (\Throwable $e){
            DB::rollBack();
            return $this->errorJson($e->getMessage());
        }
    }
}
