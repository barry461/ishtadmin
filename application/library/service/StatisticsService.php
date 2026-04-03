<?php


namespace service;

use SysTotalModel;
use tools\HttpCurl;

class StatisticsService
{
    private function getVisitWebsite($date)
    {
        return SysTotalModel::getValueBy('welcome', $date);
    }

    private function getDownAnd($date)
    {
        return SysTotalModel::getValueBy('and:download', $date);
    }

    private function getDownWeb($date)
    {
        return SysTotalModel::getValueBy('pwa:download', $date);
    }

    private function getDownIos($date)
    {
        return SysTotalModel::getValueBy('ios:download', $date);
    }

    private function getDownWindow($date)
    {
        return SysTotalModel::getValueBy('window:download', $date);
    }

    private function getDownMacOS($date)
    {
        return SysTotalModel::getValueBy('macos:download', $date);
    }

    private function getCoinsConsumeTotal($startDate, $endDate)
    {
        return \MoneyLogModel::where(['type' => \MoneyLogModel::TYPE_SUB])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('coinCnt');
    }

    private function getCoinsConsumeNum($startDate, $endDate)
    {
        return \MoneyLogModel::where(['type' => \MoneyLogModel::TYPE_SUB])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count('aff');
    }

    public function getStatisticsInfo($date)
    {
        $visitWebsite = $this->getVisitWebsite($date);
        $downAnd = $this->getDownAnd($date);
        $downWeb = $this->getDownWeb($date);
        $downIos = $this->getDownIos($date);
        $downWindow = $this->getDownWindow($date);
        $downMacOS = $this->getDownMacOS($date);
        $downTotal = $downAnd + $downWeb + $downIos + $downWindow + $downMacOS;
        $downRate = $visitWebsite == 0 ? 0 : ($downTotal / $visitWebsite) * 100;
        $regPayTotal = SysTotalModel::getValueBy('pay:recharge-amount-new',$date);//新用户充值总额
        $payTotal = SysTotalModel::getValueBy('pay:recharge-amount-total',$date);//总充值
        $regPayScale = sprintf('%.2f',$payTotal == 0 ? 0 : ($regPayTotal / $payTotal) * 100);//新增充值占比
        $payNumSuccess = SysTotalModel::getValueBy('notify-order',$date);
        $payNum = SysTotalModel::getValueBy('add-order',$date);
        $paySuccessScale = sprintf('%.2f',$payNum == 0 ? 0 : ($payNumSuccess / $payNum) * 100);//成功率
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        $coinsConsumeTotal = $this->getCoinsConsumeTotal($startDate, $endDate);
        $coinsConsumeNum = $this->getCoinsConsumeNum($startDate, $endDate);
        $keyPro = "product:buy:".$date;
        $eachProductTotal = json_encode(redis()->hGetAll($keyPro),JSON_UNESCAPED_UNICODE);
        //线路统计
        $lineArr = $this->lineRate($date);

        return [
            'date'                => $date,
            'reg_total'           => SysTotalModel::getValueBy('member:create', $date),
//            'active_total'        => SysTotalModel::getValueBy('member:active', $date),
            'active_total'        => self::getOnlineData($date),
            'active_ios'          => SysTotalModel::getValueBy('member:active:ios', $date),
            'active_android'      => SysTotalModel::getValueBy('member:active:and', $date),
            'active_web'          => SysTotalModel::getValueBy('member:active:web', $date),
            'pay_total'           => $payTotal,//总充值
            'vip_total'           => SysTotalModel::getValueBy('pay:recharge-vip-amount',$date),//vip充值
            'pay_num'             => SysTotalModel::getValueBy('notify-order',$date),//总充值成功订单数
            'coins_total'         => SysTotalModel::getValueBy('pay:recharge-coins-amount',$date),//金币充值
            'reg_pay_total'       => $regPayTotal,//新用户充值总额
            'reg_pay_scale'       => $regPayScale,//新增充值占比
            'pay_success_scale'   => $paySuccessScale,
            'coins_consume_total' => $coinsConsumeTotal,
            'coins_consume_num'   => $coinsConsumeNum,
            'each_product_total'  => $eachProductTotal,
            'visit_website'       => $visitWebsite,
            'down_and'            => $downAnd,
            'down_web'            => $downWeb,
            'down_ios'            => $downIos,
            'down_window'         => $downWindow,
            'down_macos'          => $downMacOS,
            'down_total'          => $downTotal,
            'down_rate'           => $downRate,
            'wb_main_line'        => $lineArr['mDomain'],
            'main_line_suc'       => $lineArr['mSucc'],
            'main_line_fail'      => $lineArr['mError'],
            'main_line_rate'      => $lineArr['mRate'],
            'we_bk_line'          => $lineArr['bDomain'],
            'bk_line_suc'         => $lineArr['bSucc'],
            'bk_line_fail'        => $lineArr['bError'],
            'bk_line_rate'        => $lineArr['bRate'],
            'created_at'          => date('Y-m-d H:i:s', time()),
        ];
    }

    public function lineRate($date): array
    {
        //线路成功率相关的
        $arr = [];
        SysTotalModel::query()
            ->where('date',$date)
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

        $data = ['mDomain'=>'','bDomain'=>'','mSucc' => 0, 'mError' => 0, 'bSucc' => 0, 'bError' => 0, ];
        //主线路
        $mTmp = 0;
        if($arr) {
            foreach ($arr['main'] as $ak => $av) {
                if ($mTmp == 0) {
                    $data['mDomain'] = $ak;
                }
                if ($mTmp >= 3) {
                    break;
                }
                $data['mSucc'] += $av['success'];
                $data['mError'] += $av['error'];
                $mTmp++;
            }
        }
        $mTotal  = $data['mSucc'] + $data['mError'];
        $data['mRate'] = $mTotal > 0 ? sprintf('%.2f',($data['mSucc'] / $mTotal) * 100) : 0;
        //备用
        $bTmp = 0;
        if($arr) {
            foreach ($arr['bk'] as $ak => $av) {
                if ($bTmp == 0) {
                    $data['bDomain'] = $ak;
                }
                if ($bTmp > 3) {
                    break;
                }
                $data['bSucc'] += $av['success'];
                $data['bError'] += $av['error'];
                $bTmp++;
            }
        }
        $bTotal  = $data['bSucc'] + $data['bError'];
        $data['bRate'] = $bTotal > 0 ? sprintf('%.2f',($data['bSucc'] / $bTotal) * 100) : 0;

        return $data;
    }

    public static function getOnlineData($type='') {
        $propertys = options('google_properties', '');
        if( strtotime($type)!==false ){
            $old_starttime = strtotime('2025-09-01 00:00:00');
            $old_endttime = strtotime('2025-10-26 23:59:59');
            if(strtotime($type)>=$old_starttime && strtotime($type)<=$old_endttime){
                if(register('site.theme')=='007cg'){
                    $propertys = '475671946';
                }
            }
        }
        $time = time();
        $sign = md5($time . 'sajoeaefjojaoijz');
        $timestamp = $time;
        $url = "http://172.105.123.209/?properties={$propertys}&sign={$sign}&timestamp=$timestamp";
        if(in_array($type,[1,2])) $url .= '&type='.$type;
        elseif($type=='online') {}
        else $url .= '&date='.$type;
        $json = HttpCurl::get($url);
        $dau = json_decode($json, true);
        return ($type=='online') ? $dau :( $dau[0]['metricValues'][0]['value']??'0');
    }

}