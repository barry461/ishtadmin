<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    private $config;
    private $database;

    public function _initLoader($dispatcher)
    {
        // 注册 Tbold 命名空间
        Yaf_Loader::getInstance()->registerNamespace('Tbold', APP_PATH . '/application/library/');
    }

    public function _initConfig()
    {
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->config);
        $site = @include(APP_PATH.'/application/config.php');
        Yaf_Registry::set('site', $site ?: []);
    }

    public static function error_handError($errno, $errStr, $errFile, $errLine)
    {
        if (in_array($errno, [
            YAF_ERR_NOTFOUND_CONTROLLER,
            YAF_ERR_NOTFOUND_ACTION,
            YAF_ERR_NOTFOUND_MODULE,
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

    public function _initErrorHandle($dispatcher)
    {
        $dispatcher->setErrorHandler([self::class, 'error_handError']);
        $dispatcher->catchException(true);
        $dispatcher->throwException(false);
    }

    // 注册 html 静态化插件
    public function _initStaticHtmlPlugin($dispatcher)
    {
        $dispatcher->registerPlugin(new StaticHtmlPlugin($dispatcher, $this));
    }

    // 初始化用户信息
    public function _initPlugins($dispatcher)
    {
        $dispatcher->registerPlugin(new RouterPlugin($dispatcher));
    }
    function __set($name,$val){return $this->{$name}=$val;}
    function __get($name){return $this->{$name};}
}