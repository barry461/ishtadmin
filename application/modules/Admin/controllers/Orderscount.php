<?php

/**
 * Class OrdersController
 * @author xiongba
 * @date 2020-06-06 14:21:55
 */
class OrderscountController extends BackendBaseController
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

    public function listAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->ajaxError('加载错误');
        }
        $get = $this->getRequest()->getQuery();
        $page = $get['page'] ?? 1;
        $limit = $get['limit'] ?? 10;
        $offset = ($page - 1) * 10;
        $where = [];
        if (!isset($get['between']['updated_at']) || !$get['between']['updated_at']) {
            $start = strtotime(date('Y-m-d 00:00:00', TIMESTAMP));
            $end = strtotime(date('Y-m-d 23:59:59', TIMESTAMP));
            $where = [
                ['updated_at', '>=', $start],
                ['updated_at', '<=', $end]
            ];
        }
        $where = array_merge($this->getSearchBetweenParam(), $where);
        $data = OrdersModel::selectRaw('sum(amount)/10000 amount, sum(pay_amount)/10000 pay_amount, count(id) num, channel')->where('status', OrdersModel::PAY_STAT_SUCCESS);
        if (isset($get['where']['order_type']) && $get['where']['order_type'] == 1) {
            $where[] = ['order_type', '=', 1];
        }
        if (isset($get['where']['order_type']) && $get['where']['order_type'] == 2) {
            $data->whereIn('order_type', [2, 5]);
        }
        if ($where) {
            $data->where($where);
        }
        $newData = clone $data;
        $data = $data->groupBy('channel');
        $total = \DB::table(\DB::raw("({$data->toSql()}) as sub"))->mergeBindings($data->getQuery())->count();
        $data = $data->limit($limit)->offset($offset)->get();
        $list = $newData->get()->toArray();
        $total = $list[0]['amount'] ?? 0;
        $payTotal = $list[0]['pay_amount'] ?? 0;
        $result = [
            'extend' => ['total' => floatval($total), 'pay_total' => floatval($payTotal)],
            'count' => empty($data) ? 0 : $total,
            'data'  => $data,
            "msg"   => '',
            'code'  => 0
        ];
        return $this->ajaxReturn($result);
    }

    /**
     * 试图渲染
     * @return string
     * @author xiongba
     * @date 2020-03-12 20:25:10
     */
    public function indexAction()
    {
        // $data = OrdersModel::selectRaw('sum(amount)/10000 amount, sum(pay_amount)/10000 pay_amount, count(id) num, channel')->where('status', OrdersModel::PAY_STAT_SUCCESS);
        // $list = $data->get()->toArray();
        // $total = $list[0]['amount'] ?? 0;
        // $payTotal = $list[0]['pay_amount'] ?? 0;
        // $this->assign('total', floatval($total));
        // $this->assign('pay_total', floatval($payTotal));
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
    

}