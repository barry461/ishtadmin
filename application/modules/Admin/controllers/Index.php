<?php

use Carbon\Carbon;
use service\AgentChartService;
use service\ChartService;
use tools\RedisService;


/**
 * 主页,常用操作
 * Class IndexController
 */
class IndexController extends BackendBaseController
{

    static $baseDir = APP_PATH . '/storage/chart/';

    public $today_start;
    //曲线统计缓冲过期控制
    static $backendChartRedisExperid = 900;
    //面板控制缓存
    static $backendPannelRedisExperid = 360;

    public function init()
    {
        parent::init();
        $this->today_start = strtotime(date('Y-m-d 00:00:00', TIMESTAMP));
    }

    public function indexAction()
    {
        $roleId = $this->getUser()->role_id;
        $model = RoleModel::find($roleId);
        $menu = PermissionModel::getTreeAll(explode(',', $model->role_action_ids));
        $this->assign('username', $this->getUser()->username);
        $this->assign('name', $model->role_name);
        $this->display('index', ['menu' => $menu]);
    }

    // 使用每日统计中的数据
    public function chartAjax21Action(): bool
    {
        $day = 15;
        $redis = redis();
        $key = "admin:console:$day";
        $data = $redis->getWithSerialize($key);
        if (empty($data)) {
            // 图列头
            $regSeries = ['name' => '新增用户', 'data' => []];
            $sessionSeries = ['name' => '日活', 'data' => []];
            $orderSeries = ['name' => '订单', 'data' => []];
            $paySeries = ['name' => '充值', 'data' => []];
            $money = ['name' => '金币消耗笔数', 'data' => []];

            // 所有的时间
            $curAt = time();
            $curDate = date('Y-m-d', $curAt);
            $startAt = strtotime("-$day days 00:00:00", $curAt);
            $allAt = range($startAt, $startAt + $day * 86400, 86400);
            $dates = array_map(function ($item) {
                return date('Y-m-d', $item);
            }, $allAt);
            $formatDates = array_map(function ($at) {
                return date('Ymd', $at);
            }, $allAt);


            $stateInfo = \DayDataModel::whereIn('date', $dates)
                ->get()
                ->keyBy('date')
                ->toArray();


            $service = new \service\StatisticsService();
            $stateInfo[$curDate] = $service->getStatisticsInfo($curDate);

            foreach ($dates as $date) {
                $regSeries['data'][] = $stateInfo[$date]['reg_total'] ?? 0;
                $sessionSeries['data'][] = $stateInfo[$date]['active_total'] ?? 0;
                $orderSeries['data'][] = $stateInfo[$date]['pay_num'] ?? 0;
                $paySeries['data'][] = sprintf("%.02f", htdiv($stateInfo[$date]['pay_total'] ? (string)($stateInfo[$date]['pay_total'] * 100) : 0));
                $money['data'][] = $stateInfo[$date]['coins_consume_num'] ?? 0;
            }

            $extend = ['type' => 'line', 'smooth' => true];
            $series = [
                array_merge($regSeries, $extend),
                array_merge($sessionSeries, $extend),
                array_merge($orderSeries, $extend),
                array_merge($paySeries, $extend),
                array_merge($money, $extend),
            ];

            $data = [
                'legendData' => array_column($series, 'name'),
                'category'   => $formatDates,
                'seriesData' => $series,
                'title'      => "最近{$day}天数据",
            ];
            $redis->setWithSerialize($key, $data, 300);
        }

        $this->fakeUser(function () use (&$data){
            $seriesData = &$data['seriesData'];
            $rate = setting('adm:fake:rate' , 20);
            foreach ($seriesData as &$datum){
                foreach ($datum['data'] as &$d1){
                    $d1 *= $rate;
                }
                unset($d1);
            }
            unset($datum);
        });
        return $this->ajaxSuccess($data);
    }

    // 使用每日统计中的数据
    public function chartAjax2Action(): bool
    {
        $legendChart = new \tools\LegendChart("最近15天数据");
        $category = [];
        for ($i = 15; $i > 0; $i--) {
            $category[] = date('Y-m-d', strtotime("-$i days"));
        }
        $data = cached('admin/char-ajax-'.date('d'))
            ->fetchJson(function () use ($category) {
                return \DayDataModel::whereIn('date', $category)
                    ->orderBy('date')
                    ->get()
                    ->map(function (DayDataModel $daily) {
                        return $daily->getAttributes();
                    })->toArray();
            });
        $all = DayDataModel::makeCollect($data);
        $all->add(value(function (){
            $curDate = date('Y-m-d' , time());
            $item = DayDataModel::make();
            $item->date = $curDate;
            $item->reg_total = SysTotalModel::getValueBy('member:create');
            $item->active_total = SysTotalModel::getValueBy('member:active');
            $item->pay_num = SysTotalModel::getValueBy('notify-order');
            $item->pay_total = ceil(\SysTotalModel::getValueBy('pay:recharge-amount-total'));
            $item->coins_consume_num = $this->getCoinsConsumeTotal();
            return $item;
        }));

        /** @var DayDataModel $item */
        foreach ($all as $item){
            $legendChart->addLine('新增用户',$item->date,$item->reg_total);
            $legendChart->addLine('日活',$item->date,$item->active_total);
            $legendChart->addLine('订单',$item->date,$item->pay_num);
            $legendChart->addLine('充值',$item->date,sprintf('%.2f',$item->pay_total / 100));
            $legendChart->addLine('金币消耗笔数',$item->date,$item->coins_consume_num);
        }

        return $this->ajaxSuccess($legendChart);
    }

    /**
     * 金币消耗数
     */
    private function getCoinsConsumeTotal()
    {
        $startDate = date('Y-m-d');
        $key = 'current:day:coins:consume:total';
        return cached($key)->expired(3600)->fetchPhp(function () use ($startDate){
            return \MoneyLogModel::where(['type' => \MoneyLogModel::TYPE_SUB])
                ->where('created_at','>=',$startDate)
                ->count('aff');
        });
    }

    protected function fakeUser($call){

        if ($this->user['uid'] == setting('fake:user:id')){
            $call();
        }

    }



    public function chartAJaxAction()
    {
        $day = 15;
        $redis = redis();
        $key = "admin:console:$day";
        $data = $redis->getWithSerialize($key);
        if (empty($data)){
            $_7firstSecond = strtotime("-$day days 00:00:00");
            $secondAry = range($_7firstSecond, $_7firstSecond + $day * 86400, 86400);
            $_7firstSecond = date('Y-m-d H:i:s', $_7firstSecond);

            $regSeries = ['name' => '新增用户', 'data' => []];
            $sessionSeries = ['name' => '日活', 'data' => []];
            $orderSeries = ['name' => '订单', 'data' => []];
            $paySeries = ['name' => '充值', 'data' => []];
            $money = ['name' => '金币消耗笔数', 'data' => []];
            $dates = [];
            foreach ($secondAry as $item){
                $dates[] = date('Y-m-d' , $item);
            }
            $members = SysTotalModel::getRangeValue('member:create' , $dates);
            $sessions = SysTotalModel::getRangeValue('member:active', $dates);

            $orderCt = 0;//$this->getGroupCount('tb-order-count',OrdersModel::class, 'updated_at', $_7firstSecond, ['status' => OrdersModel::PAY_STAT_SUCCESS]);
            $payCt = 0;//$this->getGroupCount('tb-order-pay',OrdersModel::class, 'updated_at', $_7firstSecond, ['status' => OrdersModel::PAY_STAT_SUCCESS], 'sum(pay_amount)');
            $moneyCt = 0;//$this->getGroupCount('tb-moneylog-add',MoneyLogModel::class, 'created_at', $_7firstSecond, ['type' => MoneyLogModel::TYPE_SUB]);

            foreach ($secondAry as $item){
                $date = date('Y-m-d' , $item);
                $huoyue = $members->get($date)->value + $sessions->get($date)->value;
                $regSeries['data'][] = $members->get($date)->value ?? 0;
                $sessionSeries['data'][] = $huoyue;
                $orderSeries['data'][] = $orderCt[$date] ?? 0;
                $paySeries['data'][] = sprintf("%.02f", div_allow_zero($payCt[$date] ?? 0, 100));
                $money['data'][] = $moneyCt[$date] ?? 0;
            }
            $extend = ['type' => 'line', 'smooth' => true];
            $series = [
                array_merge($regSeries, $extend),
                array_merge($sessionSeries, $extend),
                array_merge($orderSeries, $extend),
                array_merge($paySeries, $extend),
                array_merge($money, $extend),
            ];

            $data = [
                'legendData' => array_column($series , 'name'),
                'category'   => array_map(function ($i){return date('Ymd' , $i);} , $secondAry),
                'seriesData' => $series,
                'title' => "最近{$day}天数据",
            ];
            $redis->setWithSerialize($key , $data , 300);
        }

        return $this->ajaxSuccess($data);

    }

    /**
     * 日活
     * @param int $item 日期的时间戳
     * @return int|mixed
     */
    protected function chartActiveUser($item)
    {
        $file = __FUNCTION__;
        $data = $this->getContentData($file);
        $date = date('Ymd', $item);
        $startItem = date('Y-m-d 00:00:00', $item);
        $endItem = date('Y-m-d 23:59:59', $item);
        if ($date === date('Ymd')) {
            $number = MemberLogModel::whereBetween('lastactivity', [$startItem, $endItem])->count('id');
            $data[$date] = $number;
            $this->setContentData($file , $data);
        }
        return $data[$date] ?? 0;
    }

    /**
     * 日活的依赖
     * @param $fileName
     * @param $data
     */
    protected function setContentData($fileName, $data)
    {
        $pathFile = self::$baseDir . $fileName . '.json';
        if (!file_exists(dirname($pathFile))) {
            @mkdir(dirname($pathFile), 0755, true);
        }
        file_put_contents($pathFile, json_encode($data));
    }

    /**
     * 日活的依赖
     * @param $fileName
     * @param $data
     */
    protected function getContentData($fileName)
    {
        $pathFile = self::$baseDir . $fileName . '.json';
        if (!file_exists($pathFile)) {
            return [];
        }
        $data = file_get_contents($pathFile);
        return json_decode($data, true);
    }


    public function consoleAction()
    {
            $todayFirstSecond = date('Y-m-d 00:00:00', TIMESTAMP);
            $date = date('Ymd');
//            $today_reg =cached('taday_reg')->expired(self::$backendPannelRedisExperid)
//                ->fetch(function ()use($todayFirstSecond){
//                    return MemberModel::query()
//                        ->where('regdate', '>=', $todayFirstSecond)
//                        ->count('uid');
//                });
//            $self_reg = cached('self_reg')->expired(self::$backendPannelRedisExperid)
//                ->fetch(function ()use($todayFirstSecond){
//                    return MemberModel::query()
//                        ->where('regdate', '>=', $todayFirstSecond)
//                        ->where('invited_by', 0)
//                        ->count('uid');
//                });

 //           $share_reg = $today_reg-$self_reg;

           /* $invite_reg = MemberModel::query()
                ->where('regdate', '>=', $todayFirstSecond)
                ->whereIn('invited_by', [])
                ->count('uid');*/
            // 日活
//            $session = cached('session')->expired(self::$backendPannelRedisExperid)
//                ->fetch(function ()use($todayFirstSecond){
//                    return MemberLogModel::query()
//                        ->where('lastactivity', '>=', $todayFirstSecond)
//                        ->count('id');
//                });



        // 充值
        $pay_vip = \SysTotalModel::getValueBy('pay:recharge-vip-amount');
        $pay_coin = \SysTotalModel::getValueBy('pay:recharge-coins-amount');
        $pay_total = $pay_vip+$pay_coin;
        $all_order = \SysTotalModel::getValueBy('add-order');
        $payed_order = \SysTotalModel::getValueBy('notify-order');
        $pay_percent = $all_order ? $payed_order/$all_order : 0;
        $visitWebsite = SysTotalModel::getValueBy('welcome');
        $downTotal = SysTotalModel::getValueBy('and:download') + SysTotalModel::getValueBy('pwa:download') + SysTotalModel::getValueBy('ios:download');
        $downTotal += + SysTotalModel::getValueBy('window:download') + SysTotalModel::getValueBy('macos:download');
        $downRate = $visitWebsite == 0 ? 0 : sprintf("%.02f",($downTotal / $visitWebsite) * 100);
        $rateList = $this->lineRate();

        $result = [
//                'session'       => $session,
//                'todayReg'      => $today_reg,
//                'self_reg'      => $self_reg,
//                'share_reg'     => $share_reg,
                'pay_vip'       => sprintf("%.02f",$pay_vip / 100),
                'pay_coin'      => sprintf("%.02f",$pay_coin / 100),
                'pay_total'     => sprintf("%.02f",$pay_total / 100),
                'pay_percent'   => sprintf("%.02f",$pay_percent * 100),

                'self_reg'      => SysTotalModel::getValueBy('welcome'),
                'share_reg'     => SysTotalModel::getValueBy('channel:welcome'),
                'session'       => SysTotalModel::getValueBy('member:active') - SysTotalModel::getValueBy('member:create'),
                'todayReg'      => SysTotalModel::getValueBy('member:create'),
                'huoyue'      => SysTotalModel::getValueBy('member:active'),
                'channel_reg'   => SysTotalModel::getValueBy('member:create-channel'),
                'invited_self'   => SysTotalModel::getValueBy('member:self-invited'),
                'invited_channel'   => SysTotalModel::getValueBy('member:channel-invited'),
                'keep_1day' => SysTotalModel::getValueBy('keep:1day'),
                'keep_3day' => SysTotalModel::getValueBy('keep:3day'),
                'keep_7day' => SysTotalModel::getValueBy('keep:7day'),
                'ckeep_1day' => SysTotalModel::getValueBy('ckeep:1day'),
                'ckeep_3day' => SysTotalModel::getValueBy('ckeep:3day'),
                'ckeep_7day' => SysTotalModel::getValueBy('ckeep:7day'),
                'newer_order' => SysTotalModel::getValueBy('order:new-user'),
                'newer_invited' => SysTotalModel::getValueBy('member:newer-invited'),
                'down_and'        => SysTotalModel::getValueBy('and:download'),
                'down_web'        => SysTotalModel::getValueBy('pwa:download'),
                'down_ios'        => SysTotalModel::getValueBy('ios:download'),
                'down_window'     => SysTotalModel::getValueBy('window:download'),
                'down_macos'      => SysTotalModel::getValueBy('macos:download'),
                'down_total'      => $downTotal,
                'down_rate'       => $downRate,
//              'pay_vip'       => sprintf("%.2f",$pay_vip / 100),
//              'pay_coin'      => sprintf("%.2f",$pay_coin / 100),
//              'pay_total'     => sprintf("%.2f",$pay_total / 100),
//              'pay_percent'   => sprintf("%.2f",$pay_percent * 100),
            ];
        $result = array_merge($result,$rateList);

        $this->fakeUser(function () use (&$result) {
            $rate = setting('adm:fake:rate', 20);
            foreach ($result as $key => &$val) {
                if ($key == 'down_rate') {
                    continue;
                }
                $val *= $rate;
            }
        });

        $this->assign('data', $result);
        $this->display('console');
    }

    public function lineRate(){
        //线路成功率相关的
        $key = 'line:success:rate';
        $arr = cached($key)->clearCached()->fetchPhp(function (){
            $arr = [];
            SysTotalModel::query()
                ->where('date',date('Y-m-d'))
                ->where('name','like','visit:%')
                ->orderByDesc('value')
                ->get()->map(function ($item) use(&$arr){
                    if (strpos($item->name,'visit:success:') !== false){
                        $domain = str_replace('visit:success:','',$item->name);
                        $arr['main'][$domain]['success'] = $item->value;
                    }
                    if (strpos($item->name,'visit:x-success:') !== false){
                        $domain = str_replace('visit:x-success:','',$item->name);
                        $arr['bk'][$domain]['success'] = $item->value;
                    }
                    if (strpos($item->name,'visit:error:') !== false){
                        $domain = str_replace('visit:error:','',$item->name);
                        $arr['main'][$domain]['error'] = $item->value;
                    }
                    if (strpos($item->name,'visit:x-error:') !== false){
                        $domain = str_replace('visit:x-error:','',$item->name);
                        $arr['bk'][$domain]['error'] = $item->value;
                    }

                    return $item;
                });

                return $arr;
        });

        $data = ['bDomain'=>'' , 'mSucc' => 0 , 'mError' => 0];
        //主线路
        // $mTmp = 0;
        // foreach ($arr['main'] as $ak => $av){
        //     if ($mTmp == 0){
        //         $data['mDomain'] = $ak;
        //     }
        //     if ($mTmp >= 3){
        //         break;
        //     }
        //     $data['mSucc'] += $av['success'];
        //     $data['mError'] += $av['error'];
        //     $mTmp++;
        // }
        // $mTotal  = $data['mSucc'] + $data['mError'];
        // $data['mRate'] = $mTotal > 0 ? sprintf('%.2f',($data['mSucc'] / $mTotal) * 100) : 0;
        // //备用
        // $bTmp = 0;
        // foreach ($arr['bk'] as $ak => $av){
        //     if ($bTmp == 0){
        //         $data['bDomain'] = $ak;
        //     }
        //     if ($bTmp > 3){
        //         break;
        //     }
        //     $data['bSucc'] += $av['success'];
        //     $data['bError'] += $av['error'];
        //     $bTmp++;
        // }
        // $bTotal  = $data['bSucc'] + $data['bError'];
        // $data['bRate'] = $bTotal > 0 ? sprintf('%.2f',($data['bSucc'] / $bTotal) * 100) : 0;

        return $data;
    }

    public function showNumAction()
    {
        //aff转推广码
        $str = $this->get['aff'] ?? '';
        if ($str) {
            return $this->showJson(get_num(trim($str)));
        }
    }
    public function affAction()
    {
        $this->getView()
            ->display('aff/page.phtml');
    }

    public function previewAction()
    {
        $this->assign('url' , $_GET['url'] ?? '');
        $this->display('play');
    }
}
