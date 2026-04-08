<?php

use service\CommonService;
use service\UserService;
use MvModel;
use AdsModel;

class HomeController extends Yaf_Controller_Abstract
{
    /**
     * 首页
     */
    public function indexAction()
    {
        try {
            // 获取今日更新
            $todayList = MvModel::where('is_show', 1)
                ->where('is_latest', 1)
                ->orderBy('latest_sort', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 获取中文字幕
            $chineseList = MvModel::where('is_show', 1)
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 获取巨乳
            $bustyList = MvModel::where('is_show', 1)
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 获取女优（波多野结衣）
            $actressList = MvModel::where('is_show', 1)
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 获取轮播图
            $carouselList = MvModel::where('is_show', 1)
                ->where('is_hot', 1)
                ->orderBy('hot_sort', 'desc')
                ->take(4)
                ->get();

            // 获取广告列表
            $popupAdList = AdsModel::where('status', 1)
                ->where('position', 2001)
                ->orderBy('sort', 'desc')
                ->take(4)
                ->get();

            $adListA2 = AdsModel::where('status', 1)
                ->where('position', 251)
                ->orderBy('sort', 'desc')
                ->take(6)
                ->get();

            // 获取最新发布
            $releaseNewList = MvModel::where('is_show', 1)
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();

            // 获取主题列表
            $themeList = MvStyleModel::where('top_show', 1)
                ->orderBy('sort', 'desc')
                ->take(10)
                ->get();

            // 获取专题合集
            $specialCollectionList = MvTagModel::where('is_show', 1)
                ->where('is_hot', 1)
                ->orderBy('top_sort', 'desc')
                ->take(2)
                ->get();

            // 获取热门视频
            $popularList = MvModel::where('is_show', 1)
                ->where('is_hot', 1)
                ->orderBy('hot_total', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            // 获取他们在看
            $watchingList = MvModel::where('is_show', 1)
                ->orderBy('watch_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            $adListA4 = AdsModel::where('status', 1)
                ->where('position', 253)
                ->orderBy('sort', 'desc')
                ->take(5)
                ->get();

            // 渲染视图
            $this->getView()->assign('todayList', $todayList);
            $this->getView()->assign('chineseList', $chineseList);
            $this->getView()->assign('bustyList', $bustyList);
            $this->getView()->assign('actressList', $actressList);
            $this->getView()->assign('carouselList', $carouselList);
            $this->getView()->assign('popupAdList', $popupAdList);
            $this->getView()->assign('adListA2', $adListA2);
            $this->getView()->assign('releaseNewList', $releaseNewList);
            $this->getView()->assign('themeList', $themeList);
            $this->getView()->assign('specialCollectionList', $specialCollectionList);
            $this->getView()->assign('popularList', $popularList);
            $this->getView()->assign('watchingList', $watchingList);
            $this->getView()->assign('adListA4', $adListA4);

            // 设置视图
            $this->getView()->display('home/index.tpl');
        } catch (\Throwable $e) {
            trigger_log($e);
            return $this->errorJson($e->getMessage());
        }
    }
}
