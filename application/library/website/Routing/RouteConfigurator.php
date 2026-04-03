<?php

namespace website\Routing;

class RouteConfigurator
{
    protected $route;
    protected $names;

    public function __construct(array &$routes , array &$names)
    {
        $this->route = &$routes;
        $this->names = &$names;
    }

    public function middleware(array $middleware): self
    {
        $this->route['middleware'] = array_merge($this->route['middleware'], $middleware);
        return $this;
    }

    public function skipMiddleware($middleware): self
    {
        $this->route['middleware'] = array_diff($this->route['middleware'], (array)$middleware);
        return $this;
    }

    public function name(string $name): self
    {
        $this->names[$name] = $this->route['path'];
        return $this;
    }

//    public function where(array $where): self
//    {
//        $this->route['where'] = $where;
//        return $this;
//    }
}
