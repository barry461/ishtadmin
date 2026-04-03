<?php

define('IN_APP', true);
define('RELATIVE_ROOT_PATH', './');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
ini_set('magic_quotes_runtime', 0);
ini_set('arg_separator.output', '&amp;');
define("APP_PATH", realpath(dirname(__FILE__).'/../')); // public 上级目录

date_default_timezone_set('Asia/Shanghai');

header('P3P: CP="CAO PSA OUR"');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, HEAD');
define("T_ENV", ini_get('yaf.environ'));
define('APP_MODULE', 'index'); // 接口类型

$app = new Yaf\Application(APP_PATH."/conf/app.ini");
$app->bootstrap()->run();
