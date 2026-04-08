<?php

use Yaf_Plugin_Abstract;
use Yaf_Request_Abstract;
use Yaf_Response_Abstract;

class SpiderLogPlugin extends Yaf_Plugin_Abstract
{
    /**
     * 在整个分发循环结束后记录蜘蛛访问
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     */
    public function dispatchLoopShutdown($request, $response)
    {
        trigger_log('123');
        // 仅处理 WEB 请求
        if (PHP_SAPI === 'cli') {
            return;
        }
        $module = defined('APP_MODULE') ? APP_MODULE : $request->getModuleName();
        $moduleLower = strtolower((string)$module);

        // 只在前台模块统计，比如 index / home
        if (!in_array($moduleLower, ['index', 'home'], true)) {
            return;
        }

        $server = $_SERVER;

        // 过滤静态资源与不需要记录的路径
        $uri = $server['REQUEST_URI'] ?? '';
        if ($uri === '') {
            return;
        }
        // 忽略常见静态资源、后台静态资源等
        if (preg_match('#\.(js|css|png|jpe?g|webp|gif|ico|svg|ttf|otf|woff2?)($|\?)#i', $uri)) {
            return;
        }
        if (strpos($uri, '/static/') !== false || strpos($uri, '/public/') !== false) {
            return;
        }

        $ua = $server['HTTP_USER_AGENT'] ?? '';
        trigger_log($ua);
        $detected = SpiderDetector::detect($ua);

        // 若不是蜘蛛则不记录
        if (!$detected) {
            return;
        }

        list($spiderName, $userAgent) = $detected;

        $log = [
            'spider_name' => $spiderName,
            'user_agent' => (string)$userAgent,
            'request_uri' => (string)($server['REQUEST_URI'] ?? ''),
            'referer' => (string)($server['HTTP_REFERER'] ?? ''),
            'ip' => (string)$this->getClientIp($server),
            'http_method' => (string)($server['REQUEST_METHOD'] ?? 'GET'),
            'status' => (int)http_response_code() ?: 200,
            'created_at' => time(),
        ];

        // 使用全局 DB（由 StaticHtmlPlugin 初始化的 Capsule/DB 别名）
        try {
            SpiderLogModel::query()->create($log);
        } catch (\Throwable $e) {
            // 不中断正常请求，可按需记录 error_log
            error_log('SpiderLog insert failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取客户端 IP
     *
     * @param array $server
     * @return string
     */
    protected function getClientIp(array $server): string
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

