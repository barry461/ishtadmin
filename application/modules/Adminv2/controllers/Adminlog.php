<?php

/**
 * 管理日志 API 控制器 (RESTful)
 */
class AdminlogController extends AdminV2BaseController
{
    /**
     * 日志列表
     * GET /adminv2/adminlog/list
     * 
     * 参数:
     * - username: 用户名搜索
     * - action: 操作类型 (created/updated/deleted)
     * - ip: IP地址搜索
     * - keyword: 日志内容关键词
     * - date_from: 开始日期 (YYYY-MM-DD)
     * - date_to: 结束日期 (YYYY-MM-DD)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = AdminLogModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 日志详情
     * GET /adminv2/adminlog/detail
     * 
     * 参数:
     * - id: 日志ID (必填)
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少日志ID');
        }

        $log = AdminLogModel::find($id);
        if (!$log) {
            return $this->notFound('日志不存在');
        }

        $data = $log->getAttributes();
        $data['action_name'] = AdminLogModel::ACTION_TIPS[$log->action] ?? $log->action;
        
        // 直接格式化日期，避免触发访问器中的 Carbon
        if (!empty($data['created_at'])) {
            $timestamp = is_numeric($data['created_at']) ? $data['created_at'] : strtotime($data['created_at']);
            if ($timestamp) {
                $date = new \DateTime('@' . $timestamp);
                $date->setTimezone(new \DateTimeZone('Asia/Shanghai'));
                $data['created_at'] = $date->format('Y-m-d H:i:s');
            }
        }

        return $this->showJson($data);
    }

    /**
     * 获取操作类型选项
     * GET /adminv2/adminlog/actionOptions
     */
    public function actionOptionsAction()
    {
        $options = [];
        foreach (AdminLogModel::ACTION_TIPS as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $this->showJson($options);
    }
}
