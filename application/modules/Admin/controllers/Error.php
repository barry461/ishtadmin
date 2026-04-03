<?php

use Yaf\Controller_Abstract;

class ErrorController extends Controller_Abstract
{
    public function errorAction($exception)
    {
        @header('Content-Type: application/json');
        $file = $exception->getFile();
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $line = $exception->getLine();

        if (\Illuminate\Support\Str::containsAll($message,
            ['Failed opening controller', 'No such file or directory'])
        ) {
            http_response_code(404);
            exit();
        }

        $errStr = '['.date('Y-m-d H:i:s')."] \r\n";
        $errStr .= '  错误级别：'.$code."\r\n";
        $errStr .= '  错误信息：'.$message."\r\n";
        $errStr .= '  错误文件：'.$file."\r\n";
        $errStr .= '  错误行数：'.$line."\r\n";
        $errStr .= "\r\n";

        error_log($errStr, 3, APP_PATH . '/storage/logs/log.log');
        error_log((string)$exception, 3, APP_PATH . '/storage/logs/except.log');

        if ($code != '422') {
            $message = '系统错误';
        }

        $returnData = [
            'data' => [],
            'status' => 0,
            'msg' => $message,
            'crypt' => true,
        ];

        if (in_array(\Yaf\Application::app()->environ(), ['test', 'product'])) {
            $crypt = new LibCrypt();
            if (APP_MODULE == 'api') {
                $returnData = $crypt->replyDataPwa($returnData);
            } elseif (APP_MODULE == 'merchant') {
                $returnData = $crypt->replyData($returnData);
            }
        } else {
            $returnData = json_encode($returnData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        if (is_array($returnData)) {
            $returnData = json_encode($returnData);
        }

        return $this->getResponse()->setBody($returnData);
    }
}

