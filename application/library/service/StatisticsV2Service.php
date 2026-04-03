<?php
namespace service;

use CategoriesModel;
use CategoryRelationshipsModel;
use CommentsModel;
use ContentsModel;
use Illuminate\Support\Collection;
use MemberModel;
use OrdersModel;
use UserOnlineModel;
use UsersModel;

/**
 * 数据统计
 * 封装所有统计相关的数据查询逻辑
 */
class StatisticsV2Service
{

    /**
     * 获取系统概览数据
     * @return array
     */
    public static function getOverview()
    {
        $today = date('Y-m-d');
        $todayStart = strtotime($today);
        $todayEnd = strtotime($today . ' 23:59:59');

        // 今日数据
        $todayData = [
            'new_users' => MemberModel::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'new_contents' => ContentsModel::where('type', ContentsModel::TYPE_POST)
                ->whereBetween('created', [$todayStart, $todayEnd])->count(),
            'new_comments' => CommentsModel::whereBetween('created', [$todayStart, $todayEnd])->count(),
            'new_orders' => OrdersModel::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'revenue' => OrdersModel::whereBetween('created_at', [$todayStart, $todayEnd])
                ->where('status', 'completed')->sum('amount') ?? 0,
        ];

        // 总计数据
        $totalData = [
            'users' => MemberModel::count(),
            'contents' => ContentsModel::where('type', ContentsModel::TYPE_POST)->count(),
            'comments' => CommentsModel::count(),
            'orders' => OrdersModel::count(),
        ];

        return [
            'today' => $todayData,
            'total' => $totalData,
        ];
    }

    /**
     * 获取网站概要数据
     * @return array
     */
    public static function getSiteOverview()
    {
        // 获取当前在线人数
        $onlineCount = 0;
        try {
            $onlineData = \service\StatisticsService::getOnlineData('online');
            if (is_array($onlineData) && isset($onlineData['count'])) {
                $onlineCount = (int) $onlineData['count'];
            }
        } catch (\Exception $e) {
            // 如果获取在线人数失败，使用0作为默认值
            $onlineCount = 0;
        }

        return [
            'published_contents' => ContentsModel::getPublishedCount(),
            'total_categories' => CategoriesModel::getTotalCount(),
            'total_comments' => CommentsModel::getTotalCount(),
            'current_online' => $onlineCount,
        ];
    }

    /**
     * 获取今日发布文章作者排名
     * @param int $limit 返回数量，默认10
     * @return Collection
     */
    public static function getTodayAuthorRanking(int $limit = 10)
    {
        $today = date('Y-m-d');
        $todayStart = strtotime($today);
        $todayEnd = strtotime($today . ' 23:59:59');

        // 获取表名前缀
        $tablePrefix = \Yaf\Registry::get('database')->prefix;
        $usersTable = $tablePrefix . 'users';
        $contentsTable = $tablePrefix . 'contents';

        // 获取常量值
        $postType = ContentsModel::TYPE_POST;
        $publishStatus = ContentsModel::STATUS_PUBLISH;

        // 构建SQL查询今日发布文章作者排名
        $sql = "
            SELECT 
                {$usersTable}.uid,
                {$usersTable}.name,
                {$usersTable}.screenName,
                COUNT({$contentsTable}.cid) as post_count
            FROM {$contentsTable}
            INNER JOIN {$usersTable} ON {$contentsTable}.authorId = {$usersTable}.uid
            WHERE {$contentsTable}.type = ?
                AND {$contentsTable}.status = ?
                AND {$contentsTable}.created >= ?
                AND {$contentsTable}.created <= ?
            GROUP BY {$usersTable}.uid, {$usersTable}.name, {$usersTable}.screenName
            HAVING post_count > 0
            ORDER BY post_count DESC
            LIMIT ?
        ";

        $bindings = [$postType, $publishStatus, $todayStart, $todayEnd, $limit];

        $data = \DB::select($sql, $bindings);

        // 转换为数组并添加排名
        $list = collect($data)->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'uid' => (int) $item->uid,
                'name' => $item->name ?? '',
                'screenName' => $item->screenName ?? '',
                'post_count' => (int) $item->post_count,
            ];
        });

        return $list;
    }

    /**
     * 获取本月发布文章作者排名
     * @param int $limit 返回数量，默认10
     * @return Collection
     */
    public static function getMonthAuthorRanking(int $limit = 10)
    {
        // 获取本月第一天和最后一天
        $monthStart = strtotime(date('Y-m-01 00:00:00'));
        $monthEnd = strtotime(date('Y-m-t 23:59:59'));

        // 获取表名前缀
        $tablePrefix = \Yaf\Registry::get('database')->prefix;
        $usersTable = $tablePrefix . 'users';
        $contentsTable = $tablePrefix . 'contents';

        // 获取常量值
        $postType = ContentsModel::TYPE_POST;
        $publishStatus = ContentsModel::STATUS_PUBLISH;

        // 构建SQL查询本月发布文章作者排名
        $sql = "
            SELECT 
                {$usersTable}.uid,
                {$usersTable}.name,
                {$usersTable}.screenName,
                COUNT({$contentsTable}.cid) as post_count
            FROM {$contentsTable}
            INNER JOIN {$usersTable} ON {$contentsTable}.authorId = {$usersTable}.uid
            WHERE {$contentsTable}.type = ?
                AND {$contentsTable}.status = ?
                AND {$contentsTable}.created >= ?
                AND {$contentsTable}.created <= ?
            GROUP BY {$usersTable}.uid, {$usersTable}.name, {$usersTable}.screenName
            HAVING post_count > 0
            ORDER BY post_count DESC
            LIMIT ?
        ";

        $bindings = [$postType, $publishStatus, $monthStart, $monthEnd, $limit];

        $data = \DB::select($sql, $bindings);

        // 转换为数组并添加排名
        $list = collect($data)->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'uid' => (int) $item->uid,
                'name' => $item->name ?? '',
                'screenName' => $item->screenName ?? '',
                'post_count' => (int) $item->post_count,
            ];
        });

        return $list;
    }

    /**
     * 获取内容统计数据
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $groupBy
     * @return array
     */
    public static function getContentsStats(string $dateFrom, string $dateTo, string $groupBy = 'day')
    {
        $startTime = strtotime($dateFrom);
        $endTime = strtotime($dateTo . ' 23:59:59');

        // 发布趋势
        $dateFormat = self::getSqlDateFormat($groupBy);
        $trend = ContentsModel::where('type', ContentsModel::TYPE_POST)
            ->whereBetween('created', [$startTime, $endTime])
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created), '{$dateFormat}') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 状态分布
        $statusDistribution = ContentsModel::where('type', ContentsModel::TYPE_POST)
            ->whereBetween('created', [$startTime, $endTime])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // 分类分布 (Top 10) - 使用 ORM 关系
        $categoryDistribution = CategoryRelationshipsModel::query()
            ->with('category:id,name')  // 只加载需要的字段
            ->whereHas('content', function ($query) use ($startTime, $endTime) {
                $query->where('type', ContentsModel::TYPE_POST)
                    ->whereBetween('created', [$startTime, $endTime]);
            })
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()->category->name ?? '未分类',
                    'count' => $items->count()
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        return [
            'trend' => $trend,
            'status_distribution' => $statusDistribution,
            'category_distribution' => $categoryDistribution,
        ];
    }

    /**
     * 获取用户统计数据
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $groupBy
     * @return array
     */
    public static function getUsersStats(string $dateFrom, string $dateTo, string $groupBy = 'day')
    {
        $startTime = strtotime($dateFrom);
        $endTime = strtotime($dateTo . ' 23:59:59');

        // 用户增长趋势
        $dateFormat = self::getSqlDateFormat($groupBy);
        $growthTrend = MemberModel::whereBetween('created_at', [$startTime, $endTime])
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created_at), '{$dateFormat}') as date, COUNT(*) as new_users")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 计算累计用户数
        $totalBefore = MemberModel::where('created_at', '<', $startTime)->count();
        $growthTrend->transform(function ($item) use (&$totalBefore) {
            $totalBefore += $item->new_users;
            $item->total_users = $totalBefore;
            return $item;
        });

        // 活跃用户
        $today = strtotime(date('Y-m-d'));
        $activeUsers = [
            'dau' => MemberModel::where('last_login', '>=', $today)->count(),
            'wau' => MemberModel::where('last_login', '>=', strtotime('-7 days'))->count(),
            'mau' => MemberModel::where('last_login', '>=', strtotime('-30 days'))->count(),
        ];

        return [
            'growth_trend' => $growthTrend,
            'active_users' => $activeUsers,
        ];
    }

    /**
     * 获取评论统计数据
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public static function getCommentsStats(string $dateFrom, string $dateTo)
    {
        $startTime = strtotime($dateFrom);
        $endTime = strtotime($dateTo . ' 23:59:59');

        // 评论趋势
        $trend = CommentsModel::whereBetween('created', [$startTime, $endTime])
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 状态分布
        $statusDistribution = CommentsModel::whereBetween('created', [$startTime, $endTime])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // 热门文章 (评论数最多的文章 Top 10) - 使用 ORM 关系
        $topArticles = CommentsModel::query()
            ->with('contents:cid,title')
            ->whereBetween('created', [$startTime, $endTime])
            ->whereHas('contents')  // 确保文章存在
            ->get()
            ->groupBy('cid')
            ->map(function ($comments) {
                $content = $comments->first()->contents;
                return [
                    'cid' => $content->cid,
                    'title' => $content->title,
                    'comment_count' => $comments->count()
                ];
            })
            ->sortByDesc('comment_count')
            ->take(10)
            ->values();

        return [
            'trend' => $trend,
            'status_distribution' => $statusDistribution,
            'top_articles' => $topArticles,
        ];
    }

    /**
     * 获取订单统计数据
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $groupBy
     * @return array
     */
    public static function getOrdersStats(string $dateFrom, string $dateTo, string $groupBy = 'day')
    {
        $startTime = strtotime($dateFrom);
        $endTime = strtotime($dateTo . ' 23:59:59');

        // 订单趋势
        $dateFormat = self::getSqlDateFormat($groupBy);
        $trend = OrdersModel::whereBetween('created_at', [$startTime, $endTime])
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created_at), '{$dateFormat}') as date, COUNT(*) as orders, SUM(amount) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 支付方式分布
        $paymentDistribution = OrdersModel::whereBetween('created_at', [$startTime, $endTime])
            ->where('status', 'completed')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // 产品分布 (Top 10)
        $productDistribution = OrdersModel::whereBetween('created_at', [$startTime, $endTime])
            ->where('status', 'completed')
            ->selectRaw('product_name, COUNT(*) as count, SUM(amount) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return [
            'trend' => $trend,
            'payment_distribution' => $paymentDistribution,
            'product_distribution' => $productDistribution,
        ];
    }

    /**
     * 获取用户发帖统计数据 (使用原生SQL优化性能)
     * 参考 Admin/Userstats 的 listAjaxAction 实现
     * @param array $params
     * @param int $limit
     * @param int $offset
     * @return array [$list, $total]
     */
    public static function getUserPostsStats(array $params, int $limit, int $offset)
    {
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;
        $keyword = $params['keyword'] ?? '';

        // 获取表名前缀
        $tablePrefix = \Yaf\Registry::get('database')->prefix;
        $usersTable = $tablePrefix . 'users';
        $contentsTable = $tablePrefix . 'contents';
        $commentsTable = $tablePrefix . 'comments';

        // 获取常量值
        $postType = ContentsModel::TYPE_POST;
        $approvedStatus = CommentsModel::STATUS_APPROVED;

        // 构建SQL
        $sql = "
            SELECT 
                {$usersTable}.uid,
                {$usersTable}.name,
                {$usersTable}.screenName,
                COUNT(DISTINCT {$contentsTable}.cid) as post_count,
                SUM(CASE WHEN {$commentsTable}.status = ? THEN 1 ELSE 0 END) as approved_comments,
                COUNT({$commentsTable}.coid) as total_comments
            FROM {$usersTable}
            LEFT JOIN {$contentsTable} ON {$usersTable}.uid = {$contentsTable}.authorId 
                AND {$contentsTable}.type = ?
            LEFT JOIN {$commentsTable} ON {$contentsTable}.cid = {$commentsTable}.cid
            WHERE 1=1
        ";

        $bindings = [$approvedStatus, $postType];

        // 昵称搜索
        if (!empty($keyword)) {
            $sql .= " AND {$usersTable}.screenName LIKE ?";
            $bindings[] = '%' . trim($keyword) . '%';
        }

        // 时间范围过滤
        if ($dateFrom) {
            $sql .= " AND {$contentsTable}.created >= ?";
            $bindings[] = strtotime($dateFrom);
        }
        if ($dateTo) {
            $sql .= " AND {$contentsTable}.created <= ?";
            $bindings[] = strtotime($dateTo . ' 23:59:59');
        }

        // GROUP BY 和排序
        $sql .= " GROUP BY {$usersTable}.uid, {$usersTable}.name, {$usersTable}.screenName";
        $sql .= " HAVING post_count > 0";
        $sql .= " ORDER BY post_count DESC";

        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as sub";
        $totalResult = \DB::select($countSql, $bindings);
        $total = $totalResult[0]->total ?? 0;

        // 分页查询
        $sql .= " LIMIT ? OFFSET ?";
        $bindings[] = $limit;
        $bindings[] = $offset;

        $data = \DB::select($sql, $bindings);

        // 转换为数组
        $list = collect($data)->map(function ($item) {
            return [
                'name' => $item->name,
                'screenName' => $item->screenName,
                'post_count' => (int) $item->post_count,
                'approved_comments' => (int) $item->approved_comments,
                'total_comments' => (int) $item->total_comments,
            ];
        });

        return [$list, $total];
    }

    /**
     * 获取内容排行
     * @param string $metric
     * @param int $limit
     * @return Collection
     */
    public static function getContentsRanking(string $metric, int $limit)
    {
        $query = ContentsModel::where('type', ContentsModel::TYPE_POST)
            ->where('status', ContentsModel::STATUS_PUBLISH);

        switch ($metric) {
            case 'views':
                $query->orderByDesc('fake_view');
                $field = 'fake_view';
                break;
            case 'likes':
                $query->orderByDesc('like_num');
                $field = 'like_num';
                break;
            case 'comments':
                $query->orderByDesc('commentsNum');
                $field = 'commentsNum';
                break;
            default:
                $query->orderByDesc('fake_view');
                $field = 'fake_view';
        }

        return $query->limit($limit)
            ->select('cid as id', 'title', "{$field} as value")
            ->get();
    }

    /**
     * 获取用户排行
     * @param string $metric
     * @param int $limit
     * @return Collection
     */
    public static function getUsersRanking(string $metric, int $limit)
    {
        switch ($metric) {
            case 'posts':
                // 使用 ORM 关系统计用户发帖数
                return ContentsModel::query()
                    ->with('author:uid,screenName')
                    ->where('type', ContentsModel::TYPE_POST)
                    ->whereHas('author')  // 确保作者存在
                    ->get()
                    ->groupBy('authorId')
                    ->map(function ($posts) {
                        $author = $posts->first()->author;
                        return [
                            'id' => $author->uid,
                            'title' => $author->screenName,
                            'value' => $posts->count()
                        ];
                    })
                    ->sortByDesc('value')
                    ->take($limit)
                    ->values();
            default:
                return collect([]);
        }
    }

    /**
     * 获取评论排行
     * @param string $metric
     * @param int $limit
     * @return Collection
     */
    public static function getCommentsRanking(string $metric, int $limit)
    {
        switch ($metric) {
            case 'likes':
                return CommentsModel::where('status', CommentsModel::STATUS_APPROVED)
                    ->orderByDesc('like_num')
                    ->limit($limit)
                    ->select('coid as id', 'text as title', 'like_num as value')
                    ->get();
            default:
                return collect([]);
        }
    }

    /**
     * 获取在线人数统计 (按小时)
     * @param string $dateFrom 开始日期
     * @param string $dateTo 结束日期
     * @return array
     */
    public static function getOnlineUsersStats(string $dateFrom, string $dateTo)
    {
        $data = UserOnlineModel::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        // 处理每条记录,计算环比数据
        $result = $data->map(function ($item) {
            $hourlyData = [];
            $totalCount = 0;

            // 获取7天前的数据用于环比
            $pre7date = date("Y-m-d", strtotime($item->date) - 86400 * 7);
            $pre7data = UserOnlineModel::where("date", $pre7date)->first();

            // 遍历24小时
            for ($hour = 0; $hour < 24; $hour++) {
                $key = "t" . $hour;
                $currentValue = $item->$key ?? 0;
                $totalCount += $currentValue;

                // 计算环比
                if ($currentValue == 0) {
                    $change = -100.00;
                    $type = "sub";
                } else {
                    if ($pre7data && isset($pre7data->$key)) {
                        $preValue = $pre7data->$key;
                        if ($preValue > 0) {
                            $change = number_format(($currentValue - $preValue) * 100 / $preValue, 2, '.', '');
                        } else {
                            $change = 100.00;
                        }
                        $type = $preValue > $currentValue ? "sub" : "add";
                    } else {
                        $change = 100.00;
                        $type = "add";
                    }
                }

                $hourlyData[$hour] = [
                    "hour" => $hour,
                    "number" => $currentValue,
                    "type" => $type,
                    "change" => $change
                ];
            }

            return [
                'date' => $item->date,
                'hourly_data' => $hourlyData,
                'total' => $totalCount
            ];
        });

        return $result->toArray();
    }

    /**
     * 获取 SQL 日期格式化字符串
     * @param string $groupBy
     * @return string
     */
    public static function getSqlDateFormat(string $groupBy)
    {
        switch ($groupBy) {
            case 'week':
                return '%Y-%u';
            case 'month':
                return '%Y-%m';
            case 'day':
            default:
                return '%Y-%m-%d';
        }
    }
}
