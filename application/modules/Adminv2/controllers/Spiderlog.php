<?php

use service\SpiderLogService;

/**
 * 蜘蛛访问记录管理（统计 + 列表）
 */
class SpiderlogController extends AdminV2BaseController
{
    /**
     * 统计
     * GET /adminv2/spiderlog/stats
     */
    public function statsAction()
    {
        $data = SpiderLogService::getStats();
        return $this->showJson($data);
    }

    /**
     * 列表
     * GET /adminv2/spiderlog/list
     *
     * 支持参数：
     * - spider_name  蜘蛛名称（模糊搜索）
     * - uri          访问 URI 关键字
     * - ip           访问 IP
     * - status       状态码（精确匹配）
     * - date_from    开始日期 Y-m-d
     * - date_to      结束日期 Y-m-d
     */
    public function listAction()
    {
        $query = SpiderLogModel::query();

        if (!empty($this->data['spider_name'])) {
            $name = trim($this->data['spider_name']);
            $query->where('spider_name', 'like', '%' . $name . '%');
        }

        if (!empty($this->data['uri'])) {
            $uri = trim($this->data['uri']);
            $query->where('request_uri', 'like', '%' . $uri . '%');
        }

        if (!empty($this->data['ip'])) {
            $ip = trim($this->data['ip']);
            $query->where('ip', 'like', '%' . $ip . '%');
        }

        if (isset($this->data['status']) && $this->data['status'] !== '') {
            $status = (int) $this->data['status'];
            if ($status > 0) {
                $query->where('status', $status);
            }
        }

        if (!empty($this->data['date_from'])) {
            $ts = strtotime($this->data['date_from']);
            if ($ts > 0) {
                $query->where('created_at', '>=', $ts);
            }
        }

        if (!empty($this->data['date_to'])) {
            $ts = strtotime($this->data['date_to'] . ' 23:59:59');
            if ($ts > 0) {
                $query->where('created_at', '<=', $ts);
            }
        }

        $total = $query->count();

        $list = $query
            ->orderBy('id', 'desc')
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();

        return $this->pageJson($list, $total);
    }
}

