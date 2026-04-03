<?php

namespace service;

use SpiderLogModel;
use Yaf\Request_Abstract;

/**
 * 蜘蛛访问记录服务（供异步任务 worker 调用 & 插件复用）
 */
class SpiderLogService
{
    /**
     * 从当前请求环境中判断是否蜘蛛，并异步投递日志 Job
     *
     * @param Request_Abstract $request
     */
    public static function pushFromRequest(Request_Abstract $request): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        // 仅统计前台模块（Index/Home）
        $module = defined('APP_MODULE') ? APP_MODULE : $request->getModuleName();
        $moduleLower = strtolower((string)$module);
        if (!in_array($moduleLower, ['index', 'home'], true)) {
            return;
        }

        $server = $_SERVER;
        $uri = $server['REQUEST_URI'] ?? '';
        if ($uri === '') {
            return;
        }

        // 忽略静态资源
        if (preg_match('#\.(js|css|png|jpe?g|webp|gif|ico|svg|ttf|otf|woff2?)($|\?)#i', $uri)) {
            return;
        }

        $ua = $server['HTTP_USER_AGENT'] ?? '';
        if (!class_exists('SpiderDetector')) {
            return;
        }
        $detected = \SpiderDetector::detect($ua);
        if (!$detected) {
            return;
        }

        [$spiderName, $userAgent] = $detected;

        $logData = [
            'spider_name' => (string)$spiderName,
            'user_agent'  => (string)$userAgent,
            'request_uri' => (string)$uri,
            'referer'     => (string)($server['HTTP_REFERER'] ?? ''),
            'ip'          => (string)self::getClientIp($server),
            'http_method' => (string)($server['REQUEST_METHOD'] ?? 'GET'),
            'status'      => (int)http_response_code() ?: 200,
            'created_at'  => time(),
        ];

        self::pushJob($logData);
    }

    /**
     * 投递异步 Job 到 jobs:work:queue（或直接写入）
     *
     * @param array $logData
     */
    public static function pushJob(array $logData): void
    {
        // 优先使用全局 jobs() 助手
        if (function_exists('jobs')) {
            jobs([self::class, 'record'], [$logData]);
            return;
        }

        // 其次：直接按 JobsCommand 约定推入队列
        try {
            if (function_exists('redis')) {
                $queue = 'jobs:work:queue';
                $serialized = serialize([[self::class, 'record'], [$logData]]);
                $data = json_encode([$serialized]);
                redis()->rPush($queue, $data);
                return;
            }
        } catch (\Throwable $e) {
            // 队列失败则降级为同步写入
        }

        // 兜底：同步写入
        self::record($logData);
    }

    /**
     * 实际写入 spiderlog 表（由 worker 或兜底调用）
     *
     * @param array $log
     * @return void
     */
    public static function record(array $log): void
    {
        try {
            SpiderLogModel::query()->create($log);
        } catch (\Throwable $e) {
            error_log('SpiderLogService record failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取按蜘蛛名称聚合的统计数据：
     * 今日 / 昨日 / 最近7天 / 本月 / 上月 / 本月环比
     *
     * @return array[]
     */
    public static function getStats(): array
    {
        $now = time();
        $todayStart     = strtotime('today', $now);
        $yesterdayStart = strtotime('-1 day', $todayStart);
        $sevenDaysStart = strtotime('-6 days', $todayStart); // 含今日共7天

        $monthStart      = strtotime(date('Y-m-01', $now));            // 本月1号 00:00:00
        $lastMonthStart  = strtotime('-1 month', $monthStart);         // 上月1号 00:00:00
        $lastMonthEnd    = $monthStart - 1;                            // 上月最后一秒

        // 为了统计本月 & 上月，查询范围从上月1号开始
        $rows = SpiderLogModel::query()
            ->where('created_at', '>=', $lastMonthStart)
            ->get(['spider_name', 'created_at']);

        $stats = [];

        foreach ($rows as $row) {
            $name = (string)($row->spider_name ?: 'Unknown');
            // created_at 可能是时间戳或 datetime 字符串，这里做兼容转换
            $rawCreatedAt = $row->created_at;
            $ts = is_numeric($rawCreatedAt)
                ? (int)$rawCreatedAt
                : strtotime((string)$rawCreatedAt);
            if ($ts === false) {
                continue;
            }

            if (!isset($stats[$name])) {
                $stats[$name] = [
                    'spider_name' => $name,
                    'today'       => 0,
                    'yesterday'   => 0,
                    'last7'       => 0,
                    'this_month'  => 0,
                    'last_month'  => 0,
                    'month_ratio' => '-',   // 字符串，便于前端直接展示
                ];
            }

            // 最近7天（含今天）
            if ($ts >= $sevenDaysStart) {
                $stats[$name]['last7']++;
            }

            // 今日
            if ($ts >= $todayStart) {
                $stats[$name]['today']++;
            }
            // 昨日
            elseif ($ts >= $yesterdayStart && $ts < $todayStart) {
                $stats[$name]['yesterday']++;
            }

            // 本月
            if ($ts >= $monthStart) {
                $stats[$name]['this_month']++;
            }
            // 上月
            elseif ($ts >= $lastMonthStart && $ts <= $lastMonthEnd) {
                $stats[$name]['last_month']++;
            }
        }

        // 计算本月环比：本月 / 上月，保留两位小数；上月为 0 时用 '-' 表示
        foreach ($stats as $name => &$row) {
            $last = (int)$row['last_month'];
            $cur  = (int)$row['this_month'];
            if ($last > 0) {
                $row['month_ratio'] = round($cur / $last, 2);
            } elseif ($cur > 0) {
                $row['month_ratio'] = '∞';
            } else {
                $row['month_ratio'] = '-';
            }
        }
        unset($row);

        // 固定展示一些常见蜘蛛（即使当前为 0 也展示）
        $ordered = [];
        $known = [
            'Google' => '谷歌',
            'Yandex' => 'Yandex',
            'Bing'   => '必应',
            'Baidu'  => '百度',
            'Shenma' => '神马',
            '360'    => '360',
            'Toutiao'=> '头条',
            'Sogou'  => '搜狗',
            'Soso'   => '搜搜',
        ];

        foreach ($known as $key => $label) {
            $row = $stats[$key] ?? [
                'spider_name' => $key,
                'today'       => 0,
                'yesterday'   => 0,
                'last7'       => 0,
                'this_month'  => 0,
                'last_month'  => 0,
                'month_ratio' => '-',
            ];
            $row['label'] = $label;
            $ordered[] = $row;
            unset($stats[$key]);
        }

        // 其余未在 known 中的蜘蛛，追加在后面
        foreach ($stats as $name => $row) {
            $row['label'] = $name;
            $ordered[] = $row;
        }

        return $ordered;
    }

    /**
     * 获取客户端 IP
     *
     * @param array $server
     * @return string
     */
    protected static function getClientIp(array $server): string
    {
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $server['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }

        if (!empty($server['HTTP_CLIENT_IP'])) {
            return (string)$server['HTTP_CLIENT_IP'];
        }

        return (string)($server['REMOTE_ADDR'] ?? '');
    }
}

