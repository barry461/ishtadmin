<?php

/**
 * Class UserstatsController
 * 用户发帖统计
 * @author xiongba
 */
class UserstatsController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤 - 只计算通过率
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            // 计算通过率
            $item['pass_rate'] = $item['total_comments'] > 0 
                ? round(($item['approved_comments'] / $item['total_comments']) * 100, 2) . '%'
                : '0%';
            return $item;
        };
    }

    /**
     * Ajax 列表查询 - 使用原生 SQL 一次性查询所有统计数据（高性能）
     * @return bool
     */
    public function listAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->ajaxError('加载错误');
        }

        $get = $this->getRequest()->getQuery();
        $page = $get['page'] ?? 1;
        $limit = $get['limit'] ?? 50;
        $offset = ($page - 1) * $limit;

        // 获取表名前缀
        $tablePrefix = Yaf_Registry::get('database')->prefix;
        $usersTable = $tablePrefix . 'users';
        $contentsTable = $tablePrefix . 'contents';
        $commentsTable = $tablePrefix . 'comments';
        
        // 获取常量值
        $postType = ContentsModel::TYPE_POST;
        $approvedStatus = CommentsModel::STATUS_APPROVED;

        /**
         * 查询逻辑：
         * 1. 从 users 表获取用户基础信息（uid, name, screenName）
         * 2. LEFT JOIN contents 表，关联用户的文章（只统计 type='post' 的文章）
         * 3. LEFT JOIN comments 表，关联文章的评论
         * 4. GROUP BY 用户ID，聚合统计：
         *    - 发帖数：COUNT(DISTINCT contents.cid)
         *    - 评论通过数：SUM(CASE WHEN comments.status='approved' THEN 1 ELSE 0 END)
         *    - 真实评论数：COUNT(comments.coid)
         */
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
        $like = $get['like'] ?? [];
        if (!empty($like['screenName']) && $like['screenName'] !== '__undefined__') {
            $sql .= " AND {$usersTable}.screenName LIKE ?";
            $bindings[] = '%' . trim($like['screenName']) . '%';
        }

        // 时间范围过滤 - 筛选在指定时间内有发帖的用户
        $between = $get['between'] ?? [];
        if (!empty($between['created'])) {
            $from = $between['created']['from'] ?? null;
            $to = $between['created']['to'] ?? null;

            if ($from && $from !== '__undefined__') {
                $sql .= " AND {$contentsTable}.created >= ?";
                $bindings[] = strtotime($from);
            }
            if ($to && $to !== '__undefined__') {
                $sql .= " AND {$contentsTable}.created <= ?";
                $bindings[] = strtotime($to . ' 23:59:59');
            }
        }

        // GROUP BY 和排序
        $sql .= " GROUP BY {$usersTable}.uid, {$usersTable}.name, {$usersTable}.screenName";
        $sql .= " ORDER BY post_count DESC";

        // 获取总数 - 使用子查询
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as sub";
        $totalResult = \DB::select($countSql, $bindings);
        $total = $totalResult[0]->total ?? 0;

        // 分页查询
        $sql .= " LIMIT ? OFFSET ?";
        $bindings[] = $limit;
        $bindings[] = $offset;

        $data = \DB::select($sql, $bindings);

        // 转换为数组并处理
        $data = collect($data)->map(function($item) {
            return (array)$item;
        })->map($this->listAjaxIteration());

        $result = [
            'count' => $total,
            'data'  => $data,
            "msg"   => '',
            "desc"  => '',
            'code'  => 0
        ];

        return $this->ajaxReturn($result);
    }

    /**
     * 视图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->assign('get', $_GET);
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return UsersModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'uid';
    }

    /**
     * 定义数据操作日志
     * @return string
     */
    protected function getLogDesc(): string
    {
        return '用户发帖统计';
    }
}

