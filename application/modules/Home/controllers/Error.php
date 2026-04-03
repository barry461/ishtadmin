<?php


class ErrorController extends WebController
{

    public function x404Action()
    {
        $this->x404();
    }

    public function errorAction($exception)
    {
        //$this->x404(); //停用 20251110
        @header('Content-Type: application/json');
        $file = $exception->getFile();
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $line = $exception->getLine();
        if (\Illuminate\Support\Str::containsAll($message,
            ['Failed opening controller', ['No such file or directory']])
        ) {
            http_response_code(404);
            exit();
        }

        $errStr = '['.date('Y-m-d h:i:s')."] \r\n";
        $errStr .= '  错误级别：'.$code."\r\n";
        $errStr .= '  错误信息：'.$message."\r\n";
        $errStr .= '  错误文件：'.$file."\r\n";
        $errStr .= '  错误行数：'.$line."\r\n";
        $errStr .= '  请求路径：'.$_SERVER['PATH_INFO']." , ".$_SERVER['QUERY_STRING']."\r\n";
        $errStr .= "\r\n";
        // error_log — 发送错误信息到某个地方
        error_log($errStr, 3, APP_PATH . '/storage/logs/log.log');
        error_log((string)$exception, 3, APP_PATH . '/storage/logs/except.log');

        echo str_replace(APP_PATH,'',$errStr);
    }

    public function dispatch_themeAction()
    {
        try {
            $clazz = $this->getRequest()->getParam('clazz');
            test_assert($clazz, '404');
            $action = $this->getRequest()->getParam('action');
            test_assert($action, '404');
            $require_file = $this->getRequest()->getParam('require_file');
            test_assert($require_file, '404');
            if (($ok = $this->tryThemeAction($require_file, $clazz, $action)) !== false) {
                return $ok;
            }
            $this->x404Action();
        } catch (\Throwable $e) {
            $this->x404Action();
        }
    }

}
