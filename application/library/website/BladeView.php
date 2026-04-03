<?php

namespace website;

use Illuminate\Support\Str;
use Jenssegers\Blade\Blade;
use Yaf\Application;
use Yaf\Registry;
use Yaf\View_Interface;

class BladeView implements View_Interface
{

    protected $blade;

    protected $theme = null;

    protected $_vars = [];


    public function __construct($viewPath, $cachePath)
    {
        $this->blade = new Blade($viewPath, $cachePath);
        $this->blade->directive('show', function ($expression) {
            return "style=\"<?php echo ({$expression}) ? '' : 'display:none;'; ?>\"";
        });
        $this->blade->directive('importJs', function ($src , $attr = []) {
            return theme()->importJs($src , $attr);
        });
        $this->blade->directive('importCss', function ($expr , $attr = []) {
            return "<?php echo theme()->importCss($expr);?>";
        });
        $this->blade->directive('importJs', function ($expr ) {
            return "<?php echo theme()->importJs($expr );?>";
        });
        Registry::set("_view" , $this);
    }

    function assign($name, $value = null)
    {
        $this->_vars[$name] = $value;
    }


    function render($tpl, $tpl_vars = null)
    {
        return $this->blade->render($tpl , array_merge($tpl_vars ?? [] , $this->_vars));
    }

    function display($tpl, $tpl_vars = null)
    {
        echo $this->render($tpl, $tpl_vars);
    }

    function getScriptPath()
    {
        // TODO: Implement getScriptPath() method.
    }

    function setScriptPath($template_dir)
    {
        // TODO: Implement setScriptPath() method.
    }

    public function getEngine()
    {
        return $this->blade;
    }


    public function css($href)
    {
        if (!Str::endsWith($href , '.css')){
            $href .= '.css';
        }
        echo <<<HTML
<link rel="stylesheet" type="text/css" href="/themes/{$this->theme}/static/css/$href">
HTML;
    }

    public function static_dir($dir){
        echo "/themes/{$this->theme}/static/{$dir}/";
    }



    public function script($src , $attr = '')
    {
        if (!Str::endsWith($src , '.js')){
            $src .= '.js';
        }
        echo <<<HTML
<script type="text/javascript" $attr src="/themes/{$this->theme}/static/js/$src"></script>
HTML;

    }

    public function itemprop($src , $attr = '')
    {
        if (!Str::endsWith($src , '.png')){
            $src .= '.png';
        }
        return <<<HTML
<meta itemprop="url" content="/themes/{$this->theme}/static/Mirages/images/{$src}">
HTML;

    }

    public function image($src)
    {

        if (!preg_match('/\.(png|jpg|jpeg|gif|webp|svg)(\?|$)/i', $src)) {
            $src .= '.png';
        }

        return "/themes/{$this->theme}/static/Mirages/images/{$src}";
    }

    public function vShow($expr)
    {
        if ($expr){
            return ';display:none;';
        }
        return '';
    }


    public function footer()
    {
        $plugins = options('plugins');
        print_r($plugins);
        ob_flush();
        exit();
    }


}