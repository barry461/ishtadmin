<?php

use website\DefaultView;
use Yaf\Dispatcher;
use Yaf\Plugin_Abstract;
use Yaf\Registry;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use Illuminate\Database\Capsule\Manager;
use service\SpiderLogService;

class StaticHtmlPlugin extends Plugin_Abstract {
    use \website\HtmlCache;
    private $migrate = ['_initOne','_initDefaultDbAdapter','_initViews'];
    private $bootstrap;
    
    public function __construct($dispatcher, $bootstrap)
    {
        $this->dispatcher = $dispatcher;
        $this->bootstrap = $bootstrap;
        $options = require APP_PATH.'/application/html.php';
        $this->checkRouter();
        $this->NewHtmlCache(is_array($options) ? $options : []);
    }

    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response) {
        $this->__import();
        SpiderLogService::pushFromRequest($request);
        $this->dispatchStartup();
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        $this->dispatchShutdown($request, $response);
    }

    protected function __import()
    {
        require_once APP_PATH . "/vendor/autoload.php";
        foreach ($this->migrate as $method) {
            $this->{$method}($this->dispatcher);
        }
    }
    public function _initOne(Dispatcher $dispatcher)
    {
        Yaf\Loader::import('function/common.php');
        Yaf\Loader::import('function/helper.php');
        Yaf\Loader::import('function/v.php');
        Yaf\Loader::import('function/init.php');
        $dispatcher->disableView();
    }

    public function _initDefaultDbAdapter()
    {
        try {
            $object = new \Yaf\Config\Ini(APP_PATH.'/conf/database.ini', ini_get('yaf.environ'));
            if ($object->es) {
                class_alias(tools\Elasticsearch::class, '\LibEs');
                \LibEs::registerConfig([$object->es->toArray()]);
            }
        } catch (\Throwable $e) {
            $object = $this->bootstrap->config;
        }
        Registry::set('redis', $object->redis);
        Registry::set('database' , $object->database);
        Registry::set('config' , include(APP_PATH .'/application/config.php'));

        $capsule = new Manager;
        $config = $object->database->toArray();
        $config['timezone'] = '+8:00';
        if (isset($config['read'])){
            $read = explode(',', $config['read']['host']);
            if (count($read) > 1) {
                $config['read']['host'] = $read;
            }
        }

        $config['options'] = [PDO::ATTR_PERSISTENT => false];
        $capsule->addConnection($config);
        if ($object->manticore){
            $manticore = [
                'driver'   => 'mysql',
                'host'     => $object->manticore->host,
                'port'     => $object->manticore->port,
                'database' => null,
                'options'  => [ PDO::ATTR_EMULATE_PREPARES => true ],
            ];
            $capsule->addConnection($manticore , 'manticore');
        }

        //$tbrConfig = $object->tbr->toArray();
        //$tbrConfig['timezone'] = '+8:00';
        //$capsule->addConnection($tbrConfig, 'tbr_db');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        class_alias(Manager::class, 'DB');
    }
    public function _initViews(Dispatcher $dispatcher)
    {
        if (APP_MODULE === 'staff') {
            $smarty = new Smarty\adapter(null, $this->bootstrap->config->smarty->toArray());
            $dispatcher->setView($smarty);
        }elseif (APP_MODULE == 'index'){
            $config = Registry::get('config');
            //$dispatcher->setView(DefaultView::newInstance('51cg'));
            $resource = APP_PATH ."/themes/{$config['theme']}";
            $cache = APP_PATH .'/storage/views';
            new Theme($config['theme']);
            $dispatcher->setView(new \website\BladeView($resource, $cache));

        }
    }


    /**
     * 检查是否应该阻止镜像站访问sitemap
     * 未配置 siteUrl 时允许所有站点访问（向后兼容）
     */
    private function checkRouter(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? "";
        if(APP_MODULE == "index" && preg_match("/sitemap/i", $uri) ) {
            $http_host = $_SERVER['HTTP_HOST'] ?? "";
            $site = Registry::get('site');
            $siteUrl = $site['site_url'] ?? "";
            if ($http_host != parse_url($siteUrl, PHP_URL_HOST)) {
                header('HTTP/1.0 403 Forbidden');
                http_response_code(403);
                die();
            }
        }elseif(APP_MODULE == "index" && preg_match("/\/bkdg/i", $uri) ){
                $site = Registry::get('site');
                $siteTheme = $site['theme'] ?? "";
                //将【必看大瓜】合并到【热门大瓜】之下，【必看大瓜】分类以及下面的内容做301重定向至新分类
                if($siteTheme == '818hl'){
                    header('HTTP/1.1 301 Moved Permanently');
                    http_response_code(301);
                    header("Location:/category/rmdg/");
                    die();
                }
        }elseif(preg_match("/search\?s=/i", $uri)){
            header("Location: /search/".urlencode($_GET['s'])."/");
            die();
        }
        return true;
    }
}