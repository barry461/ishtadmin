<?php
/*
 * @Author: zhoukai
 * @Date: 2020-07-04 15:23:43
 * @LastEditTime: 2020-07-13 09:18:38
 * @LastEditors: Please set LastEditors
 * @FilePath: /laochaguan/application/plugins/Router.php
 */ 

class RouterPlugin extends \website\AbsPluginRouter
{
    /**
     * 尝试走模版自定义controller
     * @param  \Yaf\Request_Abstract  $request
     * @param  \Yaf\Response_Abstract  $response
     *
     * @return void
     */
    public function tryThemeRouter(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        $config = \Yaf\Registry::get('site') ? : [];
        $theme = $config['theme'] ?? 'x';
        $key = substr(crc32(__DIR__).md5($request->getRequestUri()), 0, 40);
        $yac = new Yac('');
        $data = $yac->get($key);
        if ($data === false) {
            // 扫描自定义路由
            $data = [];
            $files = glob(APP_PATH . "/themes/{$theme}/Controller/*Controller.php");
            $re = '/\bfunction\s+([A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)Action\s*\(/i';
            foreach ($files as $file) {
                $class = basename($file, '.php');
                $class = "\\Themes\\X{$theme}\\Controller\\$class";
                $data[$file] = [
                    'class'   => $class,
                    'methods' => [],
                ];
                $code = file_get_contents($file);
                if (preg_match_all($re, $code, $m)) {
                    $data[$file]['methods'] = array_map('strtolower',$m[1]);
                }
            }
            $yac->set($key , $data);
        }
        if (!empty($data)) {
            $controller = $request->getControllerName();
            $action = strtolower($request->getActionName());
            $file = APP_PATH."/themes/{$theme}/Controller/{$controller}Controller.php";
            if (isset($data[$file]) && in_array($action, $data[$file]['methods'])) {
                // 派发到 Error::dispatch_theme 走自定义路由
                $request->setParam('clazz', $data[$file]['class']);
                $request->setParam('action', $request->getActionName());
                $request->setParam('require_file', $file);
                $request->setControllerName('Error');
                $request->setActionName('dispatch_theme');
            }
        }
    }


    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        // 路由处，还没加载任何东西
        if ($this->isRouted()) {
            $this->tryThemeRouter($request , $response);
            return;
        }
        // 其他逻辑可以写在下面 
        $script = str_replace('/', '', $request->getServer()['SCRIPT_NAME']);


        if (APP_MODULE === 'staff') {
            $request->setModuleName('Admin');
            return;
        }

        //$controller = "Index";
        //$action = 'index';
        switch ($script) {
            case 'pro.php':
                $module = "Pro";
                break;
            case 'api.php':
                $module = $request->getModuleName();
                $uriAry = explode('/', $_SERVER['PATH_INFO'] ?? '');
                $uriAry = array_values(array_filter($uriAry));
                if ($uriAry){


                    if ('Api' == $request->getModuleName() && count($uriAry) == 4) {
                        $module = ucfirst($uriAry[1] ?? 'Api');
                        $controller = ucfirst($uriAry[2] ?? 'index');
                        if ($controller == 'Union') {
                            $action = $_POST['gateway'] ?? 'index';
                            $action == 'sync.domain' && $action = 'syncDomain'; //兼容.
                        } else {
                            $action = $uriAry[3] ?? 'index';
                        }
                    }

                    if ('Api' == $request->getModuleName() && stristr($_SERVER['PATH_INFO'], 'union')) {
                        $module = ucfirst($uriAry[0] ?? 'Api');
                        $controller = ucfirst($uriAry[1] ?? 'Union');
                        if ($controller == 'Union') {
                            $action = $_POST['gateway'] ?? 'index';
                            $action == 'sync.domain' && $action = 'syncDomain'; //兼容.
                        } else {
                            $action = $uriAry[2] ?? 'index';
                        }
                    }

                }else{
                    $module = 'Api';
                    $controller = 'index';
                    $action = 'index';
                }
                break;
            case 'm.php':
                $module = 'Home';
                $controller = $request->getControllerName();
                $action = $request->getActionName();
                break;
             case 't.php':
                $module = 'Transit';
                $controller = $request->getControllerName();
                $action = $request->getActionName();
                break;
            case 'admin.php':
            case 'd.php':
                $module = "Admin";
                $controller = ucfirst($_GET['mod'] ?? ($_POST['mod'] ?? 'index'));
                $action = $_GET['code'] ?? ($_POST['code'] ?? 'index');
                break;
            case 'adminv2.php':
                $module = "Adminv2";
                $controller = $request->getControllerName();
                $action = $request->getActionName();
                break;
            case 'index.php':
                $module = "Index";

                $controller = ucfirst($_GET['m'] ?? ($_POST['m'] ?? ''));
                $action = $_GET['a'] ?? ($_POST['a'] ?? '');
                if (!$controller) {
                    $controller = $request->getControllerName();
                    $action = $request->getActionName();
                }
                break;
            default:
                $module = "Index";
        }

        if (PHP_SAPI === 'cli') {
            $module = 'Index';
            $controller = 'Command';
            $action = 'index';
        }
        if (isset($module)) {
            $request->setModuleName($module);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);
        }

        if (isset($action)) {
            $request->setActionName($action);
        }
        $this->resolveChannelLink($request , $response);
    }

    // 直接用正则表达式解决渠道链接
    protected function resolveChannelLink(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response ) {
        if (APP_MODULE != 'index'){
            return ;
        }
        $uri = $request->getRequestUri();
        if (preg_match("#^/aff-(?<code>[a-zA-Z\d]+)#", $uri, $ary)) {
            $request->setControllerName('Index');
            $request->setModuleName('Index');
            $request->setActionName('Index');
            $request->setParam([
                'code' => $ary['code'],
            ]);
        }elseif (preg_match("#^/chan-(?:\d+)/aff-(?<code>[a-zA-Z\d]+)#", $uri, $ary)) {
            $request->setControllerName('Index');
            $request->setModuleName('Index');
            $request->setActionName('Index');
            $request->setParam([
                'code' => $ary['code'],
            ]);
        }
    }
}
