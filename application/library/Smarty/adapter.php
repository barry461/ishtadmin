<?php
namespace Smarty;

use Yaf\View_Interface;

class adapter implements View_Interface
{
    public $_smarty;

    public function __construct($tempPath = null, $params = array())
    {
        $this->_smarty = new \Smarty();
        if ($tempPath !== null) {
            $this->setScriptPath($tempPath);
        }
        foreach ($params as $key => $param) {
            $this->_smarty->$key = $param;
        }
    }

    public function getEngine()
    {
        return $this->_smarty;
    }

    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->_smarty->assign($name);
            return;
        }
        $this->_smarty->assign($name, $value);
    }

    public function render($tpl, $var_array = array())
    {
        return $this->_smarty->fetch($tpl);
    }

    public function setScriptPath($tpl_dir)
    {
        if (is_readable($tpl_dir)) {
            $this->_smarty->setTemplateDir($tpl_dir);
        }
    }

    public function getScriptPath($request = NULL)
    {
        return $this->_smarty->getTemplateDir();
    }

    public function display($tpl, $var_array = array())
    {
        $this->_smarty->display($tpl);
    }
}