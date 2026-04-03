<?php
define('IN_APP', true);
define('RELATIVE_ROOT_PATH', './');
ini_set('magic_quotes_runtime', 0);
ini_set('arg_separator.output', '&amp;');
define("APP_PATH", realpath(dirname(__FILE__) . '/../')); // public 上级目录
date_default_timezone_set('Asia/Shanghai');

@header("Content-Type: text/html; charset=utf-8");
@header('P3P: CP="CAO PSA OUR"');
@header('Access-Control-Allow-Origin: *');
@header('Access-Control-Allow-Headers: *');
@header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
@header('Access-Control-Allow-Headers:content-type,token');
define("T_ENV",ini_get('yaf.environ'));
define("APP_MODULE",'cli');
if(PHP_SAPI != 'cli'){
    die();
}
try{
    $ary = explode('/', $argv[1]);
    list($c, $a) = $ary;
    $app = new Yaf\Application(APP_PATH . "/conf/app.ini");
    $app->bootstrap()->getDispatcher()->dispatch(new Yaf\Request\Simple("CLI", "Script", $c, $a));
}catch (\Throwable $exception){
    print_r($exception->getMessage());
}
