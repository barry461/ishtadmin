<?php

/**
 * Class OrdersController
 * @author xiongba
 * @date 2020-06-06 14:21:55
 */
class OrdersController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    protected $channelAll = [
        'AgentPay'          => 'AgentPay',
        'DBSGame'       => 'DBSGame',
        'DBSPayfixedAlipay'  => 'DBSPayfixedAlipay',
        'DBSPayfixedWechat' => 'DBSPayfixedWechat',
        'DBSPayusdt'       => 'DBSPayusdt',
        'YSPay'    =>    'YSPay',
        'SMPay'    =>    'SMPay',
        'LIKPay'    =>    'LIKPay',
        'XYPay'    =>    'XYPay',
        'BSPay'    =>    'BSPay',
        'LYPay'    =>    'LYPay',
        'PDPay'    =>    'PDPay',
        'LIONPay'   =>    'LIONPay',
        'QFPay'    =>    'QFPay',
        'CZPay'    =>    'CZPay',
        'HJPay'    =>    'HJPay',
        'YFPay'    =>    'YFPay',
        'DBSPayunion'    =>    'DBSPayunion',
        'YSPay2'    =>    'YSPay2',
        'JJYPay'    =>    'JJYPay',
    ];

    protected $payWay = [
        'payway_wechat' => '微信',
        'payway_bank'   => '银联',
        'payway_alipay' => '支付宝',
        'payway_visa'   => 'visa',
        'payway_huabei' => '花呗',
    ];

    /**
     * 列表数据过滤
     * @return Closure
     * @author xiongba
     * @date 2019-12-02 17:08:03
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            /** @var OrdersModel $item */
            //$item->product_name = $item->product->pname;
            return $item;
        };
    }

    protected function getDesc($query): string
    {
        $payed = clone $query;
        $payed = $payed->where('status', OrdersModel::PAY_STAT_SUCCESS);
        $count = $query->count('id');
        $payedCount = $payed->count('id');
        $orderTotal = $query->sum('amount') / 100;
        $payedTotal = $payed->sum('pay_amount') / 100;
        $payedRate = $count < 1 ? 0 : number_format(($payedCount / $count)*100, 2, '.', '');
        return sprintf("订单数：%.2f,成功单数：%.2f,支付成功率:%.2f , 订单总额:%.2f , 成交订单总额:%.2f"
            , htdiv($count), htdiv($payedCount), $payedRate, htdiv($orderTotal), htdiv($payedTotal));
    }


    public function formatKey($key, $value)
    {
        if (!preg_match_all("#^([a-zA-Z_\d]+)$#i", trim($key))) {
            return [false , $value];
        }

        if ($key === 'aff_code'){
            $key = 'aff';
            $value = get_num($value);
        }

        if ($key === 'aff'){
            $model = MemberModel::find($value);
            if (!empty($model)){
                $key = 'uuid';
                $value = $model->uuid;
            }
        }

        return [$key , $value];
    }


    public function delAllAction()
    {
    }

    public function delAction()
    {
    }


    protected function doSave($data)
    {
        $pkName = $this->getPkName();
        $data['_pk'] = $data['_pk'] ?? '';
        $where = [[$pkName, '=', $data['_pk']]];
        $order = OrdersModel::where($where)->first();
        if (empty($order)) {
            return $this->ajaxError('订单不存在');
        }
        try {
            /** @var OrdersModel $order */
            $oldModel = clone $order;
            $order->fill($data);
            $product = $oldModel->product;
            /** @var MemberModel $member */
            $member = MemberModel::where('uuid', $oldModel->uuid)->first();
            \DB::beginTransaction();
            if (empty($member)) {
                throw new Exception('未找到用户信息');
            }
            //没成功的状态
            $notSuccessState = OrdersModel::PAY_STAT;
            unset($notSuccessState[OrdersModel::PAY_STAT_SUCCESS]);
            $order->updated_at = time();
            $order->saveOrFail();
            //没有修改支付状态
            if ($oldModel->status == $order->status) {
                DB::commit();
                return $this->ajaxSuccess('操作成功');
                //修改前和修改后。支付状态都是没成功
            } elseif (isset($notSuccessState[$order->status]) && isset($notSuccessState[$oldModel->status])) {
                DB::commit();
                return $this->ajaxSuccess('操作成功');
                //买vip
            } elseif ($product->type == ProductModel::GOODS_TYPE_VIP) {
                if (empty($member->aff)) {
                    throw new Exception('未找到用户信息');
                }
                $validAt = $product->valid_date * 86400;
                //判断订单状态
                switch ($order->status) {
                    //订单支付成功
                    case OrdersModel::PAY_STAT_SUCCESS:
                        $re1 = $this->addAmountToPreLevels($member->aff, $order->amount, '手动修改订单状态');
                        if (empty($re1)) {
                            throw new \Exception('更新代理数据失败');
                        }
                        $member->expired_at = max($member->expired_at, TIMESTAMP) + $validAt;
                        $member->vip_level = max($product->vip_level, $member->vip_level);
                        break;
                    //其他状态
                    default:
                        $member->expired_at = max(max($member->expired_at, TIMESTAMP) - $validAt, 0);
                        break;
                }
                //买钻石
            } elseif ($product->type == ProductModel::GOODS_TYPE_COIN) {
                $totalCoins = $product->coins + $product->free_coins;
                switch ($order->status) {
                    //修改成支付
                    case OrdersModel::PAY_STAT_SUCCESS:
                        $order->msg = '后台修正数据';
                        $member->coins = max($member->coins + $totalCoins, $totalCoins);
                        break;
                    //其他状态
                    default:
                        $member->coins = max($member->coins - $totalCoins, 0);
                        break;
                }
            }
            $member->saveOrFail();
            DB::commit();
            return $this->ajaxSuccess('操作成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->ajaxError('操作失败');
        }
    }

    /**
     * 试图渲染
     * @return string
     * @author xiongba
     * @date 2020-03-12 20:25:10
     */
    public function indexAction()
    {
        //$this->assign('channelAll', SettingModel::getOrderChannelData());
        $this->assign('payChannelAll', $this->channelAll);
        $this->assign('payWay', $this->payWay);
        $this->display();
    }


    /** @deprecated  */
    protected function chartAjaxAction()
    {
        function fullSeries($base, $data)
        {
            $result = $data;
            $_tmpData = [];
            foreach ($base['data'] as $key => $datum) {
                if (!isset($data['data'][$key])) {
                    $_tmpData[$key] = 0;
                }else{
                    $_tmpData[$key] = $data['data'][$key];
                }
            }
            $result['data'] = $_tmpData;
            return $result;
        }
        /** @var OrdersModel $query */
        $queryRow = new OrdersModel;
        $query = clone $queryRow;
        //总订单, ios订单，安卓订单 ,ios支付订单 安卓支付订单
        $where = array_merge(
            $this->getSearchLikeParam(),
            $this->getSearchBetweenParam(),
            $this->getSearchWhereParam(),
            $this->listAjaxWhere()
        );

        $aff = data_get($_GET, 'aff', null);
        if (is_string($aff) && $aff == '__undefined__'){
            $aff = null;
        }
        if (!empty($aff) && ($member = MemberModel::firstAff($aff))){
            $where[] = ['uuid' , '=' , $member->uuid];
        }

        $stateSuccess = $queryRow::PAY_STAT_SUCCESS;
        $dataResultList = [
            $query->getSeriesData($where, '总订单'),
            $query->getSeriesData($where, 'iOS订单', ['oauth_type' => $queryRow::OAUTH_DEVICE_IOS]),
            $query->getSeriesData($where, 'iOS支付', ['oauth_type' => $queryRow::OAUTH_DEVICE_IOS , 'status'=>$stateSuccess]),
            $query->getSeriesData($where, 'Android订单', ['oauth_type' => $queryRow::OAUTH_DEVICE_ANDROID]),
            $query->getSeriesData($where, 'Android支付', ['oauth_type' => $queryRow::OAUTH_DEVICE_ANDROID , 'status'=>$stateSuccess]),
        ];

        $base = array_shift($dataResultList);
        foreach ($dataResultList as &$item){
            $item = fullSeries($base , $item);
        }
        array_unshift($dataResultList , $base);

        $category = array_keys($dataResultList[0]['data']);
        $legendData = array_column($dataResultList, 'name');
        $seriesData = array_map(function ($item) {
            return [
                'data'   => array_values($item['data']),
                'type'   => 'line',
                'smooth' => true,
                'name'   => $item['name'],
            ];
        }, $dataResultList);

        $dataResultListAmount = [
            $query->getSeriesDataAmount($where, '总订单'),
            $query->getSeriesDataAmount($where, 'iOS订单', ['oauth_type' => $queryRow::OAUTH_DEVICE_IOS]),
            $query->getSeriesDataAmount($where, 'iOS支付', ['oauth_type' => $queryRow::OAUTH_DEVICE_IOS , 'status'=>$stateSuccess]),
            $query->getSeriesDataAmount($where, 'Android订单', ['oauth_type' => $queryRow::OAUTH_DEVICE_ANDROID]),
            $query->getSeriesDataAmount($where, 'Android支付', ['oauth_type' => $queryRow::OAUTH_DEVICE_ANDROID , 'status'=>$stateSuccess]),
        ];

        $base = array_shift($dataResultListAmount);
        foreach ($dataResultListAmount as &$item){
            $item = fullSeries($base , $item);
        }
        array_unshift($dataResultListAmount , $base);

        $categoryAmount = array_keys($dataResultListAmount[0]['data']);
        $legendDataAmount = array_column($dataResultListAmount, 'name');
        $seriesDataAmount = array_map(function ($item) {
            return [
                'data'   => array_values($item['data']),
                'type'   => 'line',
                'smooth' => true,
                'name'   => $item['name'],
            ];
        }, $dataResultListAmount);

        $iosAmount = array_sum($dataResultListAmount[2]['data']);
        $androidAmount = array_sum($dataResultListAmount[4]['data']);

        return $this->ajaxSuccess([
            'total' => [
                'order'         => array_sum($dataResultList[0]['data']),
                'ios'           => array_sum($dataResultList[1]['data']),
                'iosFail'       => array_sum($dataResultList[2]['data']),
                'android'       => array_sum($dataResultList[3]['data']),
                'androidFail'   => array_sum($dataResultList[4]['data']),
                'iosAmount'     => sprintf("%.2f", $iosAmount),
                'androidAmount' => sprintf("%.2f", $androidAmount),
                'allAmount'     => sprintf("%.2f", $iosAmount + $androidAmount),
            ],
            'legendData' => $legendData,
            'category'   => $category,
            'seriesData' => $seriesData,
            'legendDataAmount' => $legendDataAmount,
            'categoryAmount'   => $categoryAmount,
            'seriesDataAmount' => $seriesDataAmount,
        ]);
    }


    /**
     * 试图渲染
     * @return string
     * @deprecated
     */
    public function statisticalAction()
    {
        exit('已弃用');
        //$this->assign('channelAll', SettingModel::getOrderChannelData());
        $this->assign('payChannelAll', $this->channelAll);
        $this->assign('payWay', $this->payWay);
        $this->display();
    }


    /**
     * 获取对应的model名称
     * @return string
     * @author xiongba
     * @date 2020-03-12 20:25:10
     */
    protected function getModelClass(): string
    {
        return OrdersModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     * @author xiongba
     * @date 2020-03-12 20:25:10
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:19:41
     */
    protected function getLogDesc(): string
    {
        // TODO: Implement getLogDesc() method.
        return '';
    }


    public function listAjaxWhere()
    {
        if (isset($_GET['between'])) {
            return [];
        }
        return [
            ['updated_at', '>=', strtotime(date('Y-m-d'))]
        ];
    }


    /**
     *
     * 订单统计
     *
     * @return bool
     */
    public function totalAction()
    {
        /*$data = [
            'count'      => 100,//订单数
            'payedCount' => 100,//成功订单数
            'payedRate'  => 100,//支付成功率
            'orderTotal' => 100,//订单总额
            'payedTotal' => 100,//成交订单总额
        ];
        return $this->ajaxSuccess($data);*/
        $where = array_merge(
            $this->getSearchLikeParam(),
            $this->getSearchWhereParam(),
            $this->getSearchBetweenParam()
        );
        $query = OrdersModel::query()->where($where);
        $payed = clone $query;
        $payed = $payed->where('status', OrdersModel::PAY_STAT_SUCCESS);
        $count = $query->count('id');
        $payedCount = $payed->count('id');
        $orderTotal = $query->sum('amount') / 100;
        $payedTotal = $payed->sum('pay_amount') / 100;
        $payedRate = $count < 1 ? 0 : number_format(($payedCount / $count)*100, 2, '.', '');
        $data = [
            'count'      => $count,//订单数
            'payedCount' => $payedCount,//成功订单数
            'payedRate'  => $payedRate,//支付成功率
            'orderTotal' => $orderTotal,//订单总额
            'payedTotal' => $payedTotal,//成交订单总额
        ];
        return $this->ajaxSuccess($data);
    }

}