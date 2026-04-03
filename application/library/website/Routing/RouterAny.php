<?php

namespace website\Routing;

use website\Router;

class RouterAny
{

    protected $objects = [];

    public function add($method, string $path, $callback, $args)
    {
        $this->objects[] = Router::{$method}($path, $callback, $args);
    }


    public function name($name)
    {
        foreach ($this->objects as $object){
            $object->name($name);
        }
    }
}