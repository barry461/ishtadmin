<?php


class FixController extends \Yaf\Controller_Abstract
{


    public function init()
    {
        if (PHP_SAPI != 'cli') {
            die();
        }
    }

    // 修复连载的漫画和视频。前面两章免费
    public function fixAction()
    {
        MhModel::pluck('id')
            ->each(function ($id) {
                if (MhSeriesModel::where('pid', $id)->count() < 3){
                    return;
                }
                $idA = MhSeriesModel::where('pid', $id)->orderBy('episode')->limit(2)->pluck('id');
                MhSeriesModel::whereIn('id', $idA)->update(['is_free' => MhSeriesModel::TYPE_FREE]);
            });
        StoryModel::pluck('id')
            ->each(function ($id) {
                if (MhSeriesModel::where('pid', $id)->count() < 3){
                    return;
                }
                $idAry = StorySeriesModel::where('story_id', $id)->orderBy('series')->limit(2)->pluck('id');
                StorySeriesModel::whereIn('id', $idAry)->update(['is_free' => StorySeriesModel::TYPE_FREE]);
            });
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
            echo "[".date("Y-m-d H:i:s")."] 查询并更新广告中心广告到本地广告系统\n";
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
    public function syncadvertAction()
    {
        try{
            //第一次同步本地广告到广告中心
            echo "[".date("Y-m-d H:i:s")."] 第一次同步本地广告到广告中心\n";
            \service\SyncAdsCenterService::sync_advert();
        }catch (Exception $e){
            $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
            $errStr .= '  错误级别：'.$e->getCode()."\r\n";
            $errStr .= '  错误信息：'.$e->getMessage()."\r\n";
            $errStr .= '  错误文件：'.$e->getFile()."\r\n";
            $errStr .= '  错误行数：'.$e->getLine()."\r\n";
            echo $errStr;
        }
    }

    public function syncadvert_contentAction()
    {
        try{
            //第一次同步本地文章广告到广告中心
            echo "[".date('Y-m-d H:i:s')."] 第一次同步本地文章广告到广告中心\n";
            \service\SyncAdsCenterService::sync_advert_content();
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
        // 未启用 by 2025-12-05
        exit();
        try{
            //第一次同步本地广告到广告中心
            echo "第一次同步本地广告到广告中心\n";
            //\service\SyncAdsCenterService::sync_ads();
            \service\SyncAdsCenterService::sync_advert();
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
        // 未启用 by 2025-12-05
        exit();
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
