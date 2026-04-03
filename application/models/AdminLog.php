<?php

/**
 * class AdminLogModel
 *
 * @property int $id
 * @property string $username 账号
 * @property string $action 操作
 * @property string $ip 操作ip
 * @property string $log 操作详情
 * @property string $referrer 操作url来源
 * @property string $context 操作http上下文,含有cookie,post,get
 * @property string $created_at 操作时间
 *
 * @author xiongba
 * @date 2020-01-17 16:08:56
 *
 * @mixin \Eloquent
 */
class AdminLogModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "admin_log";

    protected $primaryKey = 'id';

    protected $fillable = ['username', 'action', 'ip', 'log', 'referrer', 'context', 'created_at'];

    protected $guarded = 'id';


    const UPDATED_AT = null;

    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_VIEW = 'view';
    const ACTION_ACCESS = 'access';

    const ACTION_TIPS = [
        self::ACTION_CREATED => "创建",
        self::ACTION_UPDATED => "更新",
        self::ACTION_DELETED => "删除",
        self::ACTION_VIEW => "查看",
        self::ACTION_ACCESS => "访问",
    ];

    public function getCreatedAtAttribute($value): string
    {
        if (empty($value) || $value == 0 || $value === '0000-00-00 00:00:00') {
            return '';
        }
        // 使用原生PHP处理时间,避免Carbon依赖问题
        $timestamp = is_numeric($value) ? $value : strtotime($value);
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * 分页获取管理日志列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [$list, $total]
     */
    public static function getPageList(array $params, int $limit, int $offset): array
    {
        $query = self::query()->orderByDesc('id');

        // 用户名搜索
        if (!empty($params['username'])) {
            $query->where('username', 'like', '%' . $params['username'] . '%');
        }

        // 操作类型筛选
        if (!empty($params['action'])) {
            $query->where('action', $params['action']);
        }

        // IP搜索
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', '%' . $params['ip'] . '%');
        }

        // 日期范围
        if (!empty($params['date_from'])) {
            $query->where('created_at', '>=', $params['date_from'] . ' 00:00:00');
        }
        if (!empty($params['date_to'])) {
            $query->where('created_at', '<=', $params['date_to'] . ' 23:59:59');
        }

        // 关键词搜索 (日志内容)
        if (!empty($params['keyword'])) {
            $query->where('log', 'like', '%' . $params['keyword'] . '%');
        }

        $total = $query->count();

        $list = $query->offset($offset)->limit($limit)->get()->map(function ($item) {
            $data = $item->getAttributes();
            $data['action_name'] = self::ACTION_TIPS[$item->action] ?? $item->action;
            
            if (!empty($data['created_at'])) {
                $timestamp = is_numeric($data['created_at']) ? $data['created_at'] : strtotime($data['created_at']);
                if ($timestamp) {
                    $date = new \DateTime('@' . $timestamp);
                    $date->setTimezone(new \DateTimeZone('Asia/Shanghai'));
                    $data['created_at'] = $date->format('Y-m-d H:i:s');
                }
            }
            
            return $data;
        })->all();

        return [$list, $total];
    }
}
