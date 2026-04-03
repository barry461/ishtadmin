<?php

use service\PayorderService;
use service\UserService;

class CallbackController extends BaseController
{
    public function init()
    {
    }

    public function pay_callbackAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        parse_input_post();
        PayorderService::callBackPayProcess($_POST);
    }

    public function notify_withdrawAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        parse_input_post();
        try {
            PayorderService::callBackWithDrayProccess($_POST);
        } catch (Throwable $e) {
            trigger_log($e);
            exit('fail');
        }
    }

    public function checkLineAction()
    {
        $this->position = IP_POSITION;
        if ($this->position['country'] != '美国') {
            echo 200;
        } else {
            // echo 0;
            echo 200;
        }
    }

}