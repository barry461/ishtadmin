<?php

namespace website;

use website\Routing\RouteConfigurator;
use website\Routing\RouterAny;
use website\Routing\RouteTrie;
use Yaf_Application;
use Yaf_Request_Http;
use Yaf_Route_Interface;

/**
 * 路由类，支持多种 HTTP 方法的路由定义。
 *
 * @method static RouteConfigurator get(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator post(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator option(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator head(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator delete(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator put(string $path, $callback, $args = [], string $name = null)
 * @method static RouteConfigurator patch(string $path, $callback, $args = [], string $name = null)
 *
 * @method static self module(string $name, ?\Closure $callback = null)
 * @method static self prefix(string $name, ?\Closure $callback = null)
 * @method static self controller(string $name, ?\Closure $callback = null)
 * @method static self middleware(string[] $middleware, ?\Closure $callback = null)
 * @method static self script(string $script, array $option = [])
 */
class Router implements Yaf_Route_Interface
{
    private const SUPPORT_METHODS = ['get', 'post', 'head', 'options', 'delete', 'put', 'patch'];


    private static $_router = [];
    private static $namedRoutes = [];
    private static $currentModule = [];
    private static $currentController = [];
    private static $currentPrefix = [];
    private static $currentMiddleware = [];
    private static $middlewareRegistry = [];
    private static $scriptOptions = [];

    private $currentCall;
    private $currentName;
    /** @var RouteTrie[] */
    protected static $tries = [];
    protected static $trieDefault = '_default';
    protected static $script = [];

    public function __construct(?string $call = null, ?string $name = null)
    {
        $this->currentCall = $call;
        $this->currentName = $name;
    }

    public static function configure(array $array)
    {

    }

    private static function addRoute(string $method, string $uri, $handler): RouteConfigurator
    {
        $module = end(self::$currentModule) ?: null;
        $controller = end(self::$currentController) ?: null;
        $prefix = end(self::$currentPrefix) ?: '';
        $middleware = end(self::$currentMiddleware) ?: [];
        $script = end(self::$script) ?: self::$trieDefault;
        $action = null;

        if (strpos($handler, '::') !== false) {
            [$controller, $action] = explode('::', $handler);
            $controller = str_replace('Controller' , '' , $controller);
            $action = str_replace('Action' , '' , $action);
            $handler = "$controller@$action";
        }
        if (strpos($handler, '@') !== false) {
            [$controller, $action] = explode('@', $handler);
        }
        if (strpos($controller, '/') !== false) {
            [$module, $controller] = explode('/', $controller);
        }

        $path = $prefix . $uri;
        $route = [
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'prefix' => $prefix,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        self::$_router[$script][$method][$path] = $route;

        if (!isset(self::$tries[$script])) {
            self::$tries[$script] = new RouteTrie();
        }
        self::$tries[$script]->add($method, $path, $route);

        return new RouteConfigurator(self::$_router[$script][$method][$path] , self::$namedRoutes);
    }

    public static function match(string $method, string $uri , $script = null)
    {
        if ($script === null) {
            $script = self::$trieDefault;
        }
        if (!isset(self::$tries[$script])){
            return false;
        }
        $match = self::$tries[$script]->match($method, $uri);
        if ($match) {
            return $match;
        }
        if ($script !== self::$trieDefault && isset(self::$tries[self::$trieDefault])) {
            $match = self::$tries[self::$trieDefault]->match($method, $uri);
        }
        return $match;
    }

    protected static function oldMatch()
    {
        //        foreach (self::$_router[$method] ?? [] as $route => $info) {
        //            $pattern = preg_replace_callback('#\\{(\w+)(?::([^}]+))?\}#', function ($m) {
        //                $name = $m[1];
        //                $regex = $m[2] ?? '[^/]+';
        //                return '(?P<' . $name . '>' . $regex . ')';
        //            }, $route);
        //            $pattern = '#^' . $pattern . '$#';
        //
        //            if (preg_match($pattern, $uri, $matches)) {
        //                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        //                return ['data' => $info, 'params' => $params];
        //            }
        //        }
    }

    private static function dispatcher($request, $response): bool
    {
        /** @var Yaf_Request_Http  $request */
        $requestUri = parse_url($request->getRequestUri(), PHP_URL_PATH);
        $script = basename($_SERVER["SCRIPT_FILENAME"]);
        if (!isset(self::$scriptOptions[$script])) {
            $script = self::$trieDefault;
        }
        $options = self::$scriptOptions[$script] ?? [];
        $method = strtolower($request->method);
        self::handlerOptions($request , $options);
        $match = self::match($method, $requestUri, $script);
        if ($match) {
            list('params' => $params, 'data' => $data) = $match;
            foreach ($params as $key => $value) {
                $request->setParam($key, $value);
            }
            self::applyMiddleware($request, $response, $data['middleware']);
            $request->setParam('::router::' , $data);
            return self::setRequestParams($request, $data);
        }
        return self::handlerFallback404($request, $options);
    }


    private static function handlerOptions($request, $page)
    {
        return null;
        $status = $page['502'] ?? null;
        if ($status === null) {
            return;
        }
        $app = Application::app();
        $dispatcher = $app->getDispatcher();
        $dispatcher->setErrorHandler(function ($errno, $errstr, $errfile, $errline) use ($dispatcher){
            switch ($errno) {
                case \Yaf\Err\NotFound\ACTION:
                case \Yaf\Err\NotFound\CONTROLLER:
                case \Yaf\Err\NotFound\MODULE:
                default:
                    $except = new \Exception($errstr, $errno);
                    echo "<pre>";
                    echo "Code: <strong>$errno</strong><br>";
                    echo "Msg: <span style='color: red'>$errstr</span><br>";
                    echo "Trace: <br>";
                    echo "<div style='background: #ccc;padding: 5px'>";
                    echo $except->getTraceAsString();
                    echo "</div>";
                    echo "</pre>";
                    exit();
                    //throw new \Exception($errstr, $errno);
            }
            return true;
        });
        $dispatcher->catchException(false);
        $dispatcher->throwException(false);
    }

    private static function handlerFallback404($request , $options):bool
    {
        if (empty($options)){
            return false;
        }
        $page = $options;
        $_404 = $page['404'] ?? null;
        if ($_404 === null) {
            return false;
        }
        $_404 = self::handlerStaticToAt($_404);
        $callback = function ($text){
            header("HTTP/1.0 404 Not Found");
            echo $text;
            ob_end_flush();
            exit();
        };

        if ($_404 === true) {
            $callback('not found!');
        }
        $pos = strpos($_404, '@');
        if ($pos === false) {
            $callback($_404);
        }elseif ($pos === 0){
            $text = @file_get_contents(substr($_404 , 1)) ?: $_404;
            $callback($text);
        }
        list($controller, $action) = explode("@", $_404);
        if (strpos($controller, '/') !== false) {
            [$module, $controller] = explode('/', $controller);
            $data = [
                'module'     => $module,
                'controller' => $controller,
                'action'     => $action,
            ];
        } else {
            $data = [
                'controller' => $controller,
                'action'     => $action,
            ];
        }
        return self::setRequestParams($request, $data);
    }


    private static function handlerStaticToAt($handler)
    {
        if (is_string($handler) && strpos($handler, '::') !== false) {
            [$controller, $action] = explode('::', $handler);
            $controller = str_replace('Controller' , '' , $controller);
            $action = str_replace('Action' , '' , $action);
            $handler = "$controller@$action";
        }
        return $handler;
    }


    private static function applyMiddleware($request, $response, array $middleware)
    {
        if (isset($middleware[0])){
            $runner = new MiddlewareRunner(self::$middlewareRegistry , $middleware);
            $runner->handle($request, $response, function () { });
        }
    }

    private static function setRequestParams($request, $item): bool
    {
        $module = $item['module'] ?? $request->module;
        $controller = $item['controller'] ?? $request->controller;
        $action = $item['action'] ?? $request->action;
        if (is_string($module)){
            $request->setModuleName($module);
        }
        if (is_string($controller)){
            $request->setControllerName($controller);
        }
        if (is_string($action)){
            $request->setActionName($action);
        }
        return true;
    }

    private static function buildTrie(): void
    {
        foreach (self::$_router as $script => $_router) {
            self::$tries[$script] = new RouteTrie();
            foreach ($_router as $method => $routes) {
                foreach ($routes as $uri => $info) {
                    self::$tries[$script]->add($method, $uri, $info);
                    if (!empty($info['name'])) {
                        self::$namedRoutes[$info['name']] = $uri;
                    }
                }
            }
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, self::SUPPORT_METHODS)) {
            return self::addRoute($name, ...$arguments);
        }

        $scopes = [
            'module'     => &self::$currentModule,
            'prefix'     => &self::$currentPrefix,
            'controller' => &self::$currentController,
            'middleware' => &self::$currentMiddleware,
            'script'     => &self::$script,
        ];

        if (isset($scopes[$name])) {
            return self::handleScope($scopes[$name], $name, ...$arguments);
        }
        trigger_error("Uncaught Error: Call to undefined method Router::$name()", E_USER_ERROR);
    }

    private static function handleScope(array &$scope, string $method, $arg0, $arg1 = null): self
    {
        $object = new self($method, $arg0);
        if ($method == 'script') {
            $scope[] = $arg0;
            self::$scriptOptions[$arg0] = $arg1;
        } elseif ($arg1 instanceof \Closure) {
            $scope[] = $arg0;
            $arg1($object);
            array_pop($scope);
        }
        return $object;
    }

    public static function any($methods , string $path, $callback, $args = [], string $name = null): RouterAny
    {
        if ($methods == '*'){
            $methods = ['get' , 'post', 'head','options','delete','put'];
        }
        $any = new RouterAny();
        foreach ($methods as $method) {
            $any->add($method, $path, $callback, $args, $name);
        }
        return $any;
    }


    public function group(\Closure $callback)
    {
        if ($this->currentCall == 'script'){
            $callback();
            array_pop(self::$script);
        }elseif ($this->currentCall){
            self::__callStatic($this->currentCall, [$this->currentName, $callback]);
        }else{
            $callback();
        }
    }

    private static function loadRouter(): void
    {
        $app = Yaf_Application::app();
        if (!$app) return;

        $config = $app->getConfig();
        $routerFile = $config->get('application.router') ?: $config->get('application.directory') . "/web.php";

        if (file_exists($routerFile)) {
            require $routerFile;
        }
    }

    public static function registerMiddleware(string $name, callable $middleware): void
    {
        self::$middlewareRegistry[$name] = $middleware;
    }

    /**
     * 导出当前 RouteTrie 到文件，供下次快速加载使用。
     *
     * @param string $path 文件保存路径
     */
    public static function dumpTrie(string $path): void
    {
        if (empty(self::$tries)) {
            self::buildTrie();
        }
        $array = [];
        foreach (self::$tries as $script => $trie) {
            $array[$script] = $trie->toArray();
        }
        $array['_script_options'] = self::$scriptOptions;
        $code = '<?php $data = ' . var_export($array, true) . ';return $data;';
        //$code = compress_php_code($code);
        file_put_contents($path, $code);
    }

    /**
     * 加载 RouteTrie 缓存。
     *
     * @param string $path 文件路径
     */
    public static function loadTrie(string $path): void
    {
        if (file_exists($path)) {
            $array = include $path;
            self::$scriptOptions = $array['_script_options'] ?? [];
            unset($array['_script_options']);
            foreach ($array as $script => $items) {
                self::$tries[$script] = RouteTrie::fromArray($array);
            }
        }
    }

    public static function genRoutePath(string $name ,array $params = [])
    {
        if (!isset(self::$namedRoutes[$name])){
            return $name;
        }
        $path = self::$namedRoutes[$name];
        $num = 0;
        return preg_replace_callback("#\{.*?}#", function ($matches) use ($params , &$num) {
            if (!isset($params[$num])){
                list($prefix) = explode(':' , $matches[0]);
                return "$prefix}";
            }
            return $params[$num++];
        }, $path);
    }

    public static function genRoute(string $name, array $params = []): ?string
    {
        if (!isset(self::$namedRoutes[$name])){
            return null;
        }

        $path = self::$namedRoutes[$name];

        // 需要URL编码的路由名称
        $encodeRoutes = ['search', 'search.page', 'tag.detail', 'tag_detail.page'];

        if (array_is_list($params)){
            $num = 0;
            $path = preg_replace_callback("#\{.*?}#", function ($matches) use ($params, &$num, $name, $encodeRoutes) {
                $param = $params[$num++];
                
                // 对特定路由的参数进行URL编码
                if (in_array($name, $encodeRoutes)) {
                    // 检查参数是否包含中文字符，如果包含则进行URL编码
                    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $param)) {
                        $param = rawurlencode($param);
                    }
                }
                
                return $param;
            }, $path);
        }else{
            foreach ($params as $key => $value) {
                $path = preg_replace_callback("#\\{{$key}(?::([^}]+))?\}#", function ($matches) use ($value, $name, $encodeRoutes) {
                    // 对特定路由的参数进行URL编码
                    if (in_array($name, $encodeRoutes)) {
                        // 检查参数是否包含中文字符，如果包含则进行URL编码
                        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $value)) {
                            $value = rawurlencode($value);
                        }
                    }
                    
                    return $value;
                }, $path);
            }
        }

        return $path;
    }

    public static function is($router , ...$args): bool
    {

        foreach ($args as $name){
            if (!isset(self::$namedRoutes[$name])) {
                continue;
            }
            $template = self::$namedRoutes[$name]; // e.g. /post/{id}
            if ($template == $router['path']){
                return true;
            }
            $pattern = preg_replace_callback('#\{(\w+)(?::([^}]+))?}#', function ($m) {
                $regex = $m[2] ?? '[^/]+';
                return "($regex)";
            }, $template);

            $pattern = "#^" . $pattern . "$#";
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (preg_match($pattern, $currentUri)){
                return true;
            }
        }
        return false;
    }

    function assemble($info, $query = null)
    {
    }

    public function route($request)
    {
        self::loadRouter();
        if (empty(self::$tries)) {
            self::buildTrie();
        }
        return self::dispatcher($request, null);
    }

    /**
     * 获取命名路由数组
     * @return array
     */
    public static function getNamedRoutes(): array
    {
        return self::$namedRoutes;
    }
}