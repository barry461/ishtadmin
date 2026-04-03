<?php
/**
 * 广告中心数据 同步
 *
 */
use service\SyncAdsCenterService;

class AdscenterController extends \Yaf\Controller_Abstract
{


    public function init()
    {
        if (PHP_SAPI != 'cli') {
            die();
        }
    }


    public function adstestAction()
    {
        try{
            //查询15分钟内广告中心可更新广告到本地广告系统
            echo "查询15分钟内广告中心可更新广告\n";
            \service\SyncAdsCenterService::adstest();
        }catch (Exception $e){
            $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
            $errStr .= '  错误级别：'.$e->getCode()."\r\n";
            $errStr .= '  错误信息：'.$e->getMessage()."\r\n";
            $errStr .= '  错误文件：'.$e->getFile()."\r\n";
            $errStr .= '  错误行数：'.$e->getLine()."\r\n";
            echo $errStr;
        }
    }
    public function adslistAction()
    {
        try{
            //查询并更新广告中心广告到本地广告系统
            echo "查询并更新广告中心广告到本地广告系统\n";
            \service\SyncAdsCenterService::ads_list();
        }catch (Exception $e){
            $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
            $errStr .= '  错误级别：'.$e->getCode()."\r\n";
            $errStr .= '  错误信息：'.$e->getMessage()."\r\n";
            $errStr .= '  错误文件：'.$e->getFile()."\r\n";
            $errStr .= '  错误行数：'.$e->getLine()."\r\n";
            echo $errStr;
        }
    }
    public function syncadsAction()
    {
        try{
            //第一次同步本地广告到广告中心
            echo "第一次同步本地广告到广告中心\n";
            \service\SyncAdsCenterService::sync_ads();
        }catch (Exception $e){
            $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
            $errStr .= '  错误级别：'.$e->getCode()."\r\n";
            $errStr .= '  错误信息：'.$e->getMessage()."\r\n";
            $errStr .= '  错误文件：'.$e->getFile()."\r\n";
            $errStr .= '  错误行数：'.$e->getLine()."\r\n";
            echo $errStr;
        }
    }
    public function syncnoticeAction()
    {
        try{
            //第一次同步本地pop到广告中心
            echo "第一次同步本地pop到广告中心\n";
            \service\SyncAdsCenterService::syncnotice();
        }catch (Exception $e){
            $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
            $errStr .= '  错误级别：'.$e->getCode()."\r\n";
            $errStr .= '  错误信息：'.$e->getMessage()."\r\n";
            $errStr .= '  错误文件：'.$e->getFile()."\r\n";
            $errStr .= '  错误行数：'.$e->getLine()."\r\n";
            echo $errStr;
        }
    }
}
