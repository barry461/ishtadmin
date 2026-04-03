<?php

namespace website;

use Yaf\Application;
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

class AbsPluginRouter extends Plugin_Abstract
{
    /** @var \Yaf\Router  */
    protected $router = null;
    private $routerName = 'my-router-new';

    protected $dispatcher = null;

    public function __construct($dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: Application::app()->getDispatcher();
        $this->router = $this->dispatcher->getRouter();
        $this->router->addRoute($this->routerName, new Router());
    }

    public function isRouted(): bool
    {
        return $this->router->getCurrentRoute() == $this->routerName;
    }


}