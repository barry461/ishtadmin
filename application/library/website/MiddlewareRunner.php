<?php

namespace Mvc;

use Exception;
use Yaf\Request_Abstract;

class MiddlewareRunner
{
    protected $middlewares = [];
    protected $middlewareMap = [];
    protected $except = [];

    public function __construct(array $middlewareMap, array $middlewares)
    {
        $this->middlewareMap = $middlewareMap;
        $this->middlewares = $middlewares;
    }

    public function handle(Request_Abstract $request, $response, callable $final)
    {
        $middlewareMap = $this->middlewareMap;
        if (empty($middlewareMap)){
            return null;
        }

        $stack = [];

        foreach ($this->middlewares as $item) {
            // 解析中间件+参数
            $segments = explode(':', $item);
            $name = $segments[0];
            $params = isset($segments[1]) ? explode(',', $segments[1]) : [];
            if (!isset($middlewareMap[$name])) {
                throw new Exception("Middleware [$name] not registered.");
            }
            $class = $middlewareMap[$name];
            $stack[] = [$class, $params];
        }

        $next = function () use (&$next, &$stack, $request, $response, $final) {
            if (empty($stack)) return $final();

            list($class, $params) = array_shift($stack);
            $instance = new $class();
            return $instance->handle($request, $response, $next, $params);
        };

        return $next();
    }
}
