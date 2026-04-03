<?php

/**
 * 评论管理 API 控制器 (RESTful)
 */
class CommentsController extends AdminV2BaseController
{
    /**
     * 评论列表
     * GET /adminv2/comments/list
     *
     * 参数:
     * - status: 状态筛选 (approved/waiting/spam/filter)
     * - cid: 文章ID
     * - keyword: 评论内容关键词
     * - author: 作者名
     * - ip: IP地址
     * - date_from: 开始日期 (YYYY-MM-DD)
     * - date_to: 结束日期 (YYYY-MM-DD)
     * - order_by: 排序字段 (默认 created)
     * - order_dir: 排序方向 (asc/desc, 默认 desc)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = CommentsModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 审核通过
     * POST /adminv2/comments/approve
     *
     * 参数:
     * - ids: 评论ID数组 (必填)
     */
    public function approveAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少评论ID');
        }

        $res = transaction(function () use ($ids) {
            return CommentsModel::batchApprove($ids, $this->user->uid);
        });

        if ($res) {
            // 清除前台文章详情页评论列表缓存，使二级评论审核通过后能立即展示
            cached('')->clearGroup(CommentsModel::GP_LIST_COMMENT_LIST);
            return $this->successMsg('审核通过成功');
        }
        return $this->errorJson('审核通过失败');
    }

    /**
     * 删除评论
     * POST /adminv2/comments/delete
     *
     * 参数:
     * - ids: 评论ID数组 (必填)
     */
    public function deleteAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少评论ID');
        }

        $res = transaction(function () use ($ids) {
            return CommentsModel::batchDelete($ids);
        });

        if ($res) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 标记为垃圾
     * POST /adminv2/comments/spam
     *
     * 参数:
     * - ids: 评论ID数组 (必填)
     */
    public function spamAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少评论ID');
        }

        $res = transaction(function () use ($ids) {
            return CommentsModel::batchUpdateStatus($ids, CommentsModel::STATUS_SPAM, $this->user->uid);
        });

        if ($res) {
            return $this->successMsg('标记成功');
        }
        return $this->errorJson('标记失败');
    }

    /**
     * 标记为过滤
     * POST /adminv2/comments/filter
     *
     * 参数:
     * - ids: 评论ID数组 (必填)
     */
    public function filterAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少评论ID');
        }

        $res = transaction(function () use ($ids) {
            return CommentsModel::batchUpdateStatus($ids, CommentsModel::STATUS_FILTER, $this->user->uid);
        });

        if ($res) {
            return $this->successMsg('标记成功');
        }
        return $this->errorJson('标记失败');
    }

    /**
     * 标记为待审核
     * POST /adminv2/comments/waiting
     *
     * 参数:
     * - ids: 评论ID数组 (必填)
     */
    public function waitingAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少评论ID');
        }

        $res = transaction(function () use ($ids) {
            return CommentsModel::batchUpdateStatus($ids, CommentsModel::STATUS_WAITING, $this->user->uid);
        });

        if ($res) {
            return $this->successMsg('标记成功');
        }
        return $this->errorJson('标记失败');
    }

    /**
     * 删除相同IP的评论
     * POST /adminv2/comments/deleteSameIp
     *
     * 参数:
     * - ip: IP地址 (必填)
     */
    public function deleteSameIpAction()
    {
        $ip = $this->data['ip'] ?? '';
        if (empty($ip)) {
            return $this->validationError('缺少IP地址');
        }

        $res = transaction(function () use ($ip) {
            return CommentsModel::deleteSameIp($ip);
        });

        if ($res) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 封禁IP
     * POST /adminv2/comments/banIp
     *
     * 参数:
     * - ip: IP地址 (必填)
     * - reason: 封禁原因 (可选)
     */
    public function banIpAction()
    {
        $ip = $this->data['ip'] ?? '';
        $reason = $this->data['reason'] ?? '违规评论';

        if (empty($ip)) {
            return $this->validationError('缺少IP地址');
        }

        $res = transaction(function () use ($ip, $reason) {
            return CommentsModel::banIp($ip, $reason, $this->user->uid);
        });

        if ($res) {
            return $this->successMsg('封禁成功');
        }
        return $this->errorJson('封禁失败');
    }

    /**
     * 封禁IP段
     * POST /adminv2/comments/banIpRange
     *
     * 参数:
     * - start_ip: 起始IP (必填)
     * - end_ip: 结束IP (必填)
     * - reason: 封禁原因 (可选)
     */
    public function banIpRangeAction()
    {
        $startIp = $this->data['start_ip'] ?? '';
        $endIp = $this->data['end_ip'] ?? '';
        $reason = $this->data['reason'] ?? '批量违规';

        if (empty($startIp) || empty($endIp)) {
            return $this->validationError('缺少IP地址');
        }

        $res = transaction(function () use ($startIp, $endIp, $reason) {
            return CommentsModel::banIpRange($startIp, $endIp, $reason, $this->user->uid);
        });

        if ($res) {
            return $this->successMsg('封禁成功');
        }
        return $this->errorJson('封禁失败');
    }

    /**
     * 保存评论 (管理员添加)
     * POST /adminv2/comments/save
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - app_aff: 用户标识 (必填)
     * - text: 评论内容 (必填)
     * - status: 状态 (可选, 默认 approved)
     * - is_top: 是否置顶 (可选, 默认 0)
     */
    public function saveAction()
    {
        $cid = $this->data['cid'] ?? '';
        $appAff = $this->data['app_aff'] ?? '';
        $text = $this->data['text'] ?? '';
        $status = $this->data['status'] ?? CommentsModel::STATUS_APPROVED;
        $isTop = (int) ($this->data['is_top'] ?? 0);

        // 检查参数
        if (empty($text)) {
            return $this->validationError('评论内容不能为空');
        }
        
        // 检查文章ID
        if (empty($cid)) {
            return $this->validationError('文章ID不能为空');
        }
        if (!is_numeric($cid)) {
            return $this->validationError('文章ID必须为数字');
        }
        $cid = (int) $cid;
        if ($cid <= 0) {
            return $this->validationError('文章ID必须大于0');
        }
        
        // 检查用户标识
        if (empty($appAff)) {
            return $this->validationError('用户标识不能为空');
        }
        if (!is_numeric($appAff)) {
            return $this->validationError('用户标识必须为数字');
        }
        $appAff = (int) $appAff;
        if ($appAff <= 0) {
            return $this->validationError('用户标识必须大于0');
        }

        try {
            $res = transaction(function () use ($cid, $appAff, $text, $status, $isTop) {
                // 检查文章是否存在
                $content = ContentsModel::find($cid);
                if (!$content) {
                    throw new \Exception('文章不存在');
                }
                if (!in_array($content->type, [ContentsModel::TYPE_POST, ContentsModel::TYPE_SKITS])) {
                    throw new \Exception('此类型文章不能添加评论');
                }

                // 查找用户，找不到就造一个假的
                $member = MemberModel::findByAff($appAff);
                if (!$member) {
                    $member = (object)[
                        'aff' => $appAff,
                        'nickname' => '用户' . $appAff,
                        'thumb' => '',
                        'uid' => 0
                    ];
                }

                // 必须是官方账号才能添加评论
                if (!redis()->sIsMember(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $appAff)) {
                    throw new \Exception('只有官方账号才能添加文章评论');
                }

                // 保存评论数据
                $data = [
                    'cid' => $cid,
                    'created' => time(),
                    'author' => $member->nickname ?? ('用户' . $appAff),
                    'reply_author' => '',
                    'reply_aff' => 0,
                    'thumb' => $member->thumb ?? '',
                    'app_aff' => $appAff,
                    'authorId' => $member->uid ?? 0,
                    'ownerId' => $content->authorId,
                    'mail' => '',
                    'url' => '',
                    'ip' => client_ip(),
                    'agent' => 'app',
                    'text' => $text,
                    'type' => CommentsModel::TYPE_COMMENT,
                    'status' => $status,
                    'is_top' => $isTop,
                    'parent' => 0,
                    'sec_parent' => 0,
                    'admin_id' => $this->user->uid
                ];

                return CommentsModel::createComment($data);
            });

            if ($res) {
                return $this->showJson(['coid' => $res->coid], self::STATUS_SUCCESS, '添加成功');
            }
            return $this->errorJson('添加失败');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 切换置顶状态
     * POST /adminv2/comments/toggleTop
     *
     * 参数:
     * - coid: 评论ID (必填)
     */
    public function toggleTopAction()
    {
        $coid = (int) ($this->data['coid'] ?? 0);
        if (!$coid) {
            return $this->validationError('缺少评论ID');
        }

        try {
            $res = transaction(function () use ($coid) {
                return CommentsModel::toggleTop($coid);
            });

            if ($res) {
                return $this->successMsg('操作成功');
            }
            return $this->errorJson('操作失败');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 最近评论
     * GET /adminv2/comments/recent
     *
     * 返回最近10条评论(日期、评论用户名、评论内容)
     */
    public function recentAction()
    {
        $list = CommentsModel::getRecentComments(10);
        return $this->showJson($list);
    }
}
