<?php

use service\StatisticsV2Service;


/**
 * 数据统计 API 控制器 (重构版)
 * Controller 层只负责参数验证和响应,所有数据查询在 StatisticsV2Service 中
 */
class StatisticsController extends AdminV2BaseController
{
    /**
     * 系统概览
     * GET /adminv2/statistics/overview
     */
    public function overviewAction()
    {

        $data = StatisticsV2Service::getOverview();
        return $this->showJson($data);
    }

    /**
     * 内容统计
     * GET /adminv2/statistics/contents
     * 
     * 参数:
     * - date_from: 开始日期 (YYYY-MM-DD)
     * - date_to: 结束日期 (YYYY-MM-DD)
     * - group_by: 分组方式 (day/week/month, 默认 day)
     */
    public function contentsAction()
    {
        $dateFrom = $this->data['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->data['date_to'] ?? date('Y-m-d');
        $groupBy = $this->data['group_by'] ?? 'day';

        $data = StatisticsV2Service::getContentsStats($dateFrom, $dateTo, $groupBy);
        return $this->showJson($data);
    }

    /**
     * 用户统计
     * GET /adminv2/statistics/users
     * 
     * 参数:
     * - date_from: 开始日期
     * - date_to: 结束日期
     * - group_by: 分组方式
     */
    public function usersAction()
    {
        $dateFrom = $this->data['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->data['date_to'] ?? date('Y-m-d');
        $groupBy = $this->data['group_by'] ?? 'day';

        $data = StatisticsV2Service::getUsersStats($dateFrom, $dateTo, $groupBy);
        return $this->showJson($data);
    }

    /**
     * 评论统计
     * GET /adminv2/statistics/comments
     * 
     * 参数:
     * - date_from: 开始日期
     * - date_to: 结束日期
     */
    public function commentsAction()
    {
        $dateFrom = $this->data['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->data['date_to'] ?? date('Y-m-d');

        $data = StatisticsV2Service::getCommentsStats($dateFrom, $dateTo);
        return $this->showJson($data);
    }

    /**
     * 订单统计
     * GET /adminv2/statistics/orders
     * 
     * 参数:
     * - date_from: 开始日期
     * - date_to: 结束日期
     * - group_by: 分组方式
     */
    public function ordersAction()
    {
        $dateFrom = $this->data['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->data['date_to'] ?? date('Y-m-d');
        $groupBy = $this->data['group_by'] ?? 'day';

        $data = StatisticsV2Service::getOrdersStats($dateFrom, $dateTo, $groupBy);
        return $this->showJson($data);
    }

    /**
     * 用户发帖统计
     * GET /adminv2/statistics/userPosts
     * 
     * 参数:
     * - date_from: 开始日期
     * - date_to: 结束日期
     * - keyword: 用户昵称搜索
     * - page: 页码
     * - limit: 每页数量
     */
    public function userPostsAction()
    {
        [$list, $total] = StatisticsV2Service::getUserPostsStats($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 排行榜
     * GET /adminv2/statistics/ranking
     * 
     * 参数:
     * - type: 排行类型 (contents/users/comments)
     * - metric: 排序指标 (views/likes/comments/posts)
     * - limit: 返回数量 (默认 10)
     */
    public function rankingAction()
    {
        $type = $this->data['type'] ?? 'contents';
        $metric = $this->data['metric'] ?? 'views';
        $limit = (int) ($this->data['limit'] ?? 10);

        $ranking = [];

        switch ($type) {
            case 'contents':
                $ranking = StatisticsV2Service::getContentsRanking($metric, $limit);
                break;
            case 'users':
                $ranking = StatisticsV2Service::getUsersRanking($metric, $limit);
                break;
            case 'comments':
                $ranking = StatisticsV2Service::getCommentsRanking($metric, $limit);
                break;
        }

        return $this->showJson(['ranking' => $ranking]);
    }

    /**
     * 在线人数统计
     * GET /adminv2/statistics/onlineUsers
     * 
     * 参数:
     * - date_from: 开始日期 (YYYY-MM-DD, 可选, 默认7天前)
     * - date_to: 结束日期 (YYYY-MM-DD, 可选, 默认今天)
     */
    public function onlineUsersAction()
    {
        // 日期优先从 $this->data（含 $_GET）取，空则从 $_GET 兜底，再空则默认最近 7 天
        $dateFrom = trim((string) ($this->data['date_from'] ?? $_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($this->data['date_to'] ?? $_GET['date_to'] ?? ''));
        if ($dateFrom === '') {
            $dateFrom = date('Y-m-d', strtotime('-6 days'));
        }
        if ($dateTo === '') {
            $dateTo = date('Y-m-d');
        }

        $data = StatisticsV2Service::getOnlineUsersStats($dateFrom, $dateTo);
        return $this->showJson($data);
    }

    /**
     * 网站概要统计
     * GET /adminv2/statistics/siteOverview
     * 
     * 返回发布中的文章数量、分类数量、评论数量
     */
    public function siteOverviewAction()
    {
        $data = StatisticsV2Service::getSiteOverview();
        return $this->showJson($data);
    }

    /**
     * 今日发布文章作者排名
     * GET /adminv2/statistics/todayAuthorRanking
     * 
     * 参数:
     * - limit: 返回数量，默认10
     * 
     * 返回今日发布文章数量最多的作者排名
     */
    public function todayAuthorRankingAction()
    {
        $limit = (int) ($this->data['limit'] ?? 10);
        $limit = min(max($limit, 1), 50); // 限制在1-50之间
        
        $data = StatisticsV2Service::getTodayAuthorRanking($limit);
        return $this->showJson($data->toArray());
    }

    /**
     * 本月发布文章作者排名
     * GET /adminv2/statistics/monthAuthorRanking
     * 
     * 参数:
     * - limit: 返回数量，默认10
     * 
     * 返回本月发布文章数量最多的作者排名
     */
    public function monthAuthorRankingAction()
    {
        $limit = (int) ($this->data['limit'] ?? 10);
        $limit = min(max($limit, 1), 50); // 限制在1-50之间
        
        $data = StatisticsV2Service::getMonthAuthorRanking($limit);
        return $this->showJson($data->toArray());
    }
}
