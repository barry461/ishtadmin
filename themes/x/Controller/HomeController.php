<?php

namespace Themes\Xx\Controller;

use WebController;

class HomeController extends WebController
{

    public function testAction()
    {
        phpinfo();
    }

}

class_alias(HomeController::class, '\Themes\Xliaohu\Controller\HomeController');