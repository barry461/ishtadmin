<?php


// var_dump($_SERVER['REQUEST_METHOD']);die();
define('IN_APP', true);
define('RELATIVE_ROOT_PATH', './');
ini_set('magic_quotes_runtime', 0);
ini_set('arg_separator.output', '&amp;');
define("APP_PATH", realpath(dirname(__FILE__).'/../')); // public 上级目录

date_default_timezone_set('Asia/Shanghai');

@header('P3P: CP="CAO PSA OUR"');
@header('Access-Control-Allow-Origin: *');
@header('Access-Control-Allow-Headers: *');
@header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
@header('Access-Control-Allow-Headers:content-type,token');
define("T_ENV", ini_get('yaf.environ'));
define('APP_MODULE', 'adminv2'); // 接口类型
// 允许 GET 和 POST 请求
$app = new Yaf\Application(APP_PATH."/conf/app.ini");
$app->bootstrap()->run();
