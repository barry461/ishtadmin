<?php

namespace website;

use website\Views\js\VueTemplateCompiler;
use Yaf\View_Interface;

class Template implements View_Interface
{

    protected $_tpl_vars = [];
    protected $_tpl_dir = null;
    protected $_options = null;
    protected $_compile_dir = null;
    protected static $tempVars = [];
    protected static $dataCallback = [];
    protected $lastDir = null;
    protected $themeDir = null;

    protected $componentMap = [];

    public function __construct() {
        $this->_compile_dir = APP_PATH . '/storage/views/';
        $this->_tpl_dir = APP_PATH . '/application/views/';
    }

    // 注册组件：组件名 → 编译后文件路径
    public function registerComponent($name, $file)
    {
        $this->componentMap[$name] = $file;
    }

    public static function bindComponent($name, $dataCallback)
    {
        self::$dataCallback[$name] = $dataCallback;
    }



    // 渲染组件（内部使用）
    protected function component($name, $props = [], $slots = [])
    {
        self::$tempVars['name'] = $name;
        self::$tempVars['file'] = $this->componentMap[$name];
        self::$tempVars['slots'] = $slots;
        ${$name} = isset(self::$dataCallback[$name])
            ? call_user_func(self::$dataCallback[$name] , $props)
            : null;
        extract($props);                         // 解构 props
        $__slot = self::$tempVars['slots']['default'] ?? null;     // 默认插槽
        $__slots = self::$tempVars['slots'];                       // 命名插槽预留
        include self::$tempVars['file'];      // 引入组件模板
    }


    function assign($name, $value = null)
    {
        $this->_tpl_vars[$name] = $value;
        return $this;
    }

    function getScriptPath()
    {
        return $this->_tpl_dir;
    }


    protected function componentPath($name)
    {
        return $this->getScriptPath() . "/" . strtolower($name) . ".vue";
    }

    function existsComponents($name)
    {
        return file_exists($this->componentPath($name));
    }


    protected function buildTemplate($tpl): string
    {
        $vue = new VueTemplateCompiler($this->_tpl_dir, $tpl,$this->_compile_dir , $this);
        return $vue->compileIfChange();
    }


    protected function _render($___file , $tpl_vars)
    {
        extract($tpl_vars);
        include $___file;
    }

    protected function mergeVars($tpl_vars): array
    {
        if (empty($tpl_vars)){
            $tpl_vars = [];
        }
        $tpl_vars = (array)$tpl_vars;
        return array_merge_recursive($this->_tpl_vars , $tpl_vars);
    }

    public function render($tpl, $tpl_vars = null)
    {
        $tpl_vars = $this->mergeVars($tpl_vars);
        $file = $this->buildTemplate($tpl);
        ob_start();
        $this->_render($file , $tpl_vars);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }


    public function display($tpl, $tpl_vars = null)
    {
        echo $this->render($tpl, $tpl_vars);
    }

    public function setScriptPath($template_dir)
    {
        $template_dir = sprintf("/%s/" , trim($template_dir , '/'));
        $this->themeDir = $template_dir;
        $this->_tpl_dir = APP_PATH . $template_dir;
    }
}