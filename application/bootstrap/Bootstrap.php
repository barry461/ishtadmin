<?php

use Yaf\Dispatcher;
use Yaf\Application;
use Yaf\Bootstrap_Abstract;
use \Illuminate\Database\Capsule\Manager;
use Yaf\Registry;

class Bootstrap extends Bootstrap_Abstract
{
    private $config;
    private $database;

    public function _initLoader(Dispatcher $dispatcher)
    {
    }

    public function _initConfig()
    {
        $this->config = Application::app()->getConfig();
        Registry::set('config', $this->config);
        $site = @include(APP_PATH.'/application/config.php');
        Registry::set('site', $site ?: []);
    }

    public static function error_handError($errno, $errStr, $errFile, $errLine)
    {
        if (in_array($errno, [
            \Yaf\Err\NotFound\CONTROLLER,
            Yaf\ERR\NOTFOUND\ACTION,
            Yaf\ERR\NOTFOUND\MODULE,
        ])) {
            http_response_code(404);
            exit('not found');
        }
        ob_start();

        $except = new \Exception($errStr, $errno);
        echo "<pre style=''>";
        echo "Code: <strong>$errno</strong><br>";
        echo "Msg: <span style='color: red'>$errStr</span><br>";
        echo "Trace: <br>";
        echo "<div style='background: #ccc;padding: 5px'>";
        echo $except->getTraceAsString();
        echo "</div>";
        echo "</pre>";
        ob_end_flush();
        exit();
    }

    public function _initErrorHandle(Dispatcher $dispatcher)
    {
        $dispatcher->setErrorHandler([self::class, 'error_handError']);
        $dispatcher->catchException(true);
        $dispatcher->throwException(false);
    }

    // 注册 html 静态化插件
    public function _initStaticHtmlPlugin(Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new StaticHtmlPlugin($dispatcher, $this));
    }

    // 初始化用户信息
    public function _initPlugins(Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new RouterPlugin($dispatcher));
    }
    function __set($name,$val){return $this->{$name}=$val;}
    function __get($name){return $this->{$name};}
}