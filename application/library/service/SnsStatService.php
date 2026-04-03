<?php


namespace service;

use tools\HttpCurl;
use Exception;

/**
 * 报表统计插件
 */
class SnsStatService
{
    public static function getConfig()
    {
        return $config = [
            'app_id'=>setting("snsstat_app_id"),
            'agent_id'=>setting("snsstat_agent_id"),
            'app_secret'=>setting("snsstat_app_secret"),
        ];
    }

    /**
     * 调用接口
     * @param $agent_id
     * @param $date_day
     * @return mixed
     */
    public static function getDauStat($agent_id, $date_day)
    {
        $param = ['agent_id'=>$agent_id, 'date_day'=>$date_day];
        $param['sign'] = strtoupper(md5($agent_id.'|'.$date_day . '|7FTWwEV865kZ4N4R'));
        $url = "https://ug.hao123apps.info/index.php/admin/login/sns_stat";
        $json = HttpCurl::post($url, $param);
        return json_decode($json, true);
    }

    /**
     * 字符串加密
     * @param $data
     * @param $config
     * @return void
     * @throws Exception
     */
    public static function checkSignByPing($data, $config)
    {
        if ($data['app_id'] != $config['app_id']) {
            throw new Exception('APPID错误');
        }
//            if (strpos($data['app_id'], 'demo') !== 0) {
//                throw new Exception('APPID错误');
//            }
        $appSecret = $config['app_secret'];
        $sign = $data['sign'] ?? null;
        if ($sign === null) {
            throw new Exception('签名为空');
        }
        unset($data['sign']);
        // 1. sha256加密
        ksort($data);    // 2. 生成待签名字符串：key1=val1&key2=val2...&secret=xxx
        $queryString = http_build_query($data, '', '&');
        $signString = $queryString . '&secret=' . $appSecret;
        // 3. 计算SHA256签名
        if ($sign !== hash('sha256', $signString)) {
            throw new Exception('签名错误');
        }
    }


}