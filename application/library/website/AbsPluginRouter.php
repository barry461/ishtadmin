<?php

namespace website;

use Yaf_Application;
use Yaf_Plugin_Abstract;
use Yaf_Request_Abstract;
use Yaf_Response_Abstract;

class AbsPluginRouter extends Yaf_Plugin_Abstract
{
    /** @var \Yaf\Router  */
    protected $router = null;
    private $routerName = 'my-router-new';

    protected $dispatcher = null;

    public function __construct($dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: Yaf_Application::app()->getDispatcher();
        $this->router = $this->dispatcher->getRouter();
        $this->router->addRoute($this->routerName, new Router());
    }

    public function isRouted(): bool
    {
        return $this->router->getCurrentRoute() == $this->routerName;
    }


}