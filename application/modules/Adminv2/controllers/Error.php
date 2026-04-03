<?php

class ErrorController extends AdminV2BaseController
{
    public function errorAction($exception)
    {
        @header('Content-Type: application/json; charset=utf-8');
        
        // 设置CORS响应头
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        @header('Access-Control-Allow-Origin: ' . $origin);
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        @header('Access-Control-Allow-Headers: Content-Type, Authorization, Token, X-Requested-With');
        @header('Access-Control-Max-Age: 86400');
        
        $file = $exception->getFile();
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $line = $exception->getLine();

        // 如果是控制器文件不存在，直接返回404
        if (\Illuminate\Support\Str::containsAll($message, ['Failed opening controller', 'No such file or directory'])) {
            http_response_code(404);
            $returnData = [
                'data' => [],
                'status' => self::STATUS_NOT_FOUND,
                'msg' => '接口不存在',
                'crypt' => true,
            ];
            $this->getResponse()->setBody(json_encode($returnData, JSON_UNESCAPED_UNICODE));
            return false;
        }

        // 记录错误日志
        $errStr = '[' . date('Y-m-d H:i:s') . "] \r\n";
        $errStr .= '  错误级别：' . $code . "\r\n";
        $errStr .= '  错误信息：' . $message . "\r\n";
        $errStr .= '  错误文件：' . $file . "\r\n";
        $errStr .= '  错误行数：' . $line . "\r\n";
        $errStr .= "\r\n";

        error_log($errStr, 3, APP_PATH . '/storage/logs/log.log');
        error_log((string)$exception, 3, APP_PATH . '/storage/logs/except.log');

        // 格式化错误消息
        if ($code != '422') {
            $message = '系统错误';
        }

        $returnData = [
            'data' => [],
            'status' => self::STATUS_SERVER_ERROR,
            'msg' => $message,
            'crypt' => true,
        ];

        // 根据环境决定是否加密
        if (in_array(\Yaf\Application::app()->environ(), ['test', 'product'])) {
            $crypt = new LibCrypt();
            $returnData = $crypt->replyDataPwa($returnData);
        } else {
            $returnData = json_encode($returnData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        if (is_array($returnData)) {
            $returnData = json_encode($returnData, JSON_UNESCAPED_UNICODE);
        }

        $this->getResponse()->setBody($returnData);
        return false;
    }
}
