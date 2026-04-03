<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;

function my_stripslashes($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = my_stripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }

    return $string;
}

function my_addslashes($string)
{
    if (APP_MODULE === 'api' || APP_MODULE === 'apipc') {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = my_addslashes($val);
            }
        } else {
            $string = addslashes(htmlentities($string, ENT_QUOTES));
        }
    }

    return $string;
}

function xss_decode($str)
{
    if (is_array($str)) {
        foreach ($str as $k => $val) {
            $str[$k] = xss_decode($val);
        }
    } else {
        $str = htmlspecialchars_decode(stripslashes($str));
    }

    return $str;
}

function random($length, $numeric = 0)
{
    mt_srand((float)microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars
            = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }

    return $hash;
}

function cache_cmd($cmd, $key = '', $val = '', $life = 0)
{
    $cmd_s = array(
        'get'   => 1,
        'set'   => 1,
        'rm'    => 1,
        'del'   => 1,
        'clear' => 1,
        'clean' => 1,
    );
    if (isset($cmd_s[$cmd])) {
        switch ($cmd) {
            case 'get':
                return Yaf\Registry::get("cache")->get($key);
                break;
            case 'set':
                return Yaf\Registry::get("cache")->set($key, $val, $life);
                break;
            case 'rm':
            case 'del':
                return Yaf\Registry::get("cache")->rm($key, $val);
                break;
            case 'clear':
            case 'clean':
                Yaf\Registry::get("cache")->clear();
                break;
        }
    }

    return null;
}

function cache_file($cmd, $key = '', $val = '', $life = 0)
{
    return cache_cmd($cmd, $key, $val, $life);
}


function referer($default = '?')
{
    $DOMAIN = preg_replace("~^www\.~", '',
        strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST')
            : $_SERVER['HTTP_HOST']));
    $referer = $_REQUEST['referer'] ?? '';
    if ($referer == '') {
        $referer = $_SERVER['HTTP_REFERER'];
    }
    if ($referer == "" || strpos($referer, 'code=register') !== false
        || strpos($referer, 'mod=login') !== false
        || (strpos($referer, ":/"."/") !== false
            && strpos($referer, $DOMAIN) === false)
    ) {
        global $rewriteHandler;
        if ($rewriteHandler) {
            $default = $rewriteHandler->formatURL($default, false);
        }

        return $default;
    }

    return $referer;
}

if (!function_exists('jstrpos')) {
    function jstrpos($haystack, $needle, $offset = null)
    {
        $jstrpos = false;
        if (function_exists('mb_strpos')) {
            $jstrpos = mb_strpos($haystack, $needle, $offset, 'UTF-8');
        } elseif (function_exists('strpos')) {
            $jstrpos = strpos($haystack, $needle, $offset);
        }

        return $jstrpos;
    }
}

function str_exists($haystack, $needle)
{
    $arg_list = func_get_args();
    $arg_num = func_num_args();
    //0 为自己,排除
    for ($i = 1; $i < $arg_num; $i++) {
        if (strpos($haystack, $arg_list[$i]) !== false) {
            return true;
        }
    }

    return false;
}



function is_image(
    $filename,
    $allow_types = array('gif' => 1, 'jpg' => 1, 'png' => 1, 'bmp' => 1)
) {
    if (!is_file($filename)) {
        return false;
    }

    $imagetypes = array(
        '1'  => 'gif',
        '2'  => 'jpg',
        '3'  => 'png',
        '4'  => 'swf',
        '5'  => 'psd',
        '6'  => 'bmp',
        '7'  => 'tiff',
        '8'  => 'tiff',
        '9'  => 'jpc',
        '10' => 'jp2',
        '11' => 'jpx',
        '12' => 'jb2',
        '13' => 'swc',
        '14' => 'iff',
        '15' => 'wbmp',
        '16' => 'xbm',
    );
    if (!$allow_types) {
        $allow_types = array(
            'gif'  => 1,
            'jpg'  => 1,
            'png'  => 1,
            'bmp'  => 1,
            'jpeg' => 1,
        );
    }
    $typeid = 0;
    $imagetype = '';
    if (function_exists('exif_imagetype')) {
        $typeid = exif_imagetype($filename);
    } elseif (function_exists('getimagesize')) {
        $_tmps = getimagesize($filename);
        $typeid = (int)$_tmps[2];
    } else {
        if (($fh = @fopen($filename, "rb"))) {
            $strInfo = unpack("C2chars", fread($fh, 2));
            fclose($fh);
            $fileTypes = array(
                7790   => 'exe',
                7784   => 'midi',
                8297   => 'rar',
                255216 => 'jpg',
                7173   => 'gif',
                6677   => 'bmp',
                13780  => 'png',
            );
            $imagetype = $fileTypes[intval($strInfo['chars1']
                .$strInfo['chars2'])];
        }
    }
    $file_ext = strtolower(trim(substr(strrchr($filename, '.'), 1)));
    if ($typeid > 0) {
        $imagetype = $imagetypes[$typeid];
    } else {
        if (!$imagetype) {
            $imagetype = $file_ext;
        }
    }

    if ($allow_types && $imagetype && isset($allow_types[$imagetype])) {
        return true;
    }

    return false;
}

/*
对二维数组进行排序
*/
function array_sort_2($arr, $keys, $type = 'asc')
{
    $keys_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $keys_value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keys_value);
    } else {
        arsort($keys_value);
    }
    reset($keys_value);
    foreach ($keys_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }

    return $new_array;
}

/*
对三维数组进行排序，按最里层某键值排序
*/
function array_sort_3($arr, $keys, $type = 'asc', $keepKeySort = true)
{
    $keys_value = $new_array = array();
    foreach ($arr as $k => $vs) {
        foreach ($vs as $ks => $v) {
            isset($v[$keys]) && $keys_value[$k."-".$ks] = $v[$keys];
        }
    }
    if ($type == 'asc') {
        asort($keys_value);
    } else {
        arsort($keys_value);
    }
    reset($keys_value);
    foreach ($keys_value as $key => $v) {
        $k_ks = explode("-", $key);
        //是否保留原有数组键值关系
        if ($keepKeySort) {
            $new_array[$k_ks[0]] = $arr[$k_ks[0]];
        } else {
            $new_array[] = $arr[$k_ks[0]];
        }
    }

    return $new_array;
}

/*去除重复的键*/
function assoc_title($arr, $key)
{
    $tmp_arr = array();
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
    }

    return $arr;
}

function getcwords($words)
{
    $words = trim($words);
    $sh = scws_open();
    scws_set_charset($sh, 'utf8');
    scws_set_ignore($sh, true);
    scws_set_multi($sh, 2);
    scws_send_text($sh, $words);
    $return = array();
    while ($result = scws_get_result($sh)) {
        foreach ($result as $wordsAry) {
            if (mb_strlen($wordsAry['word'], "utf8") > 1) {
                $return[] = $wordsAry['word'];
            }
        }
    }
    $return[] = trim($words);

    return $return;
}

function is_mobile()
{
    $regex_match
        = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
    $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
    $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
    $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
    $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
    $regex_match .= ")/i";

    return isset($_SERVER['HTTP_X_WAP_PROFILE'])
        or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match,
            strtolower($_SERVER['HTTP_USER_AGENT']));
}

/**
 * 将数字转为短网址代码
 *
 * @param int $number 数字
 *
 * @return string 短网址代码
 */
function generate_code($number = 0)
{
    $number = (int)$number;
    if ($number < 0) {
        return '';
    }
    $out = "";
    $codes = "abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ";
    while ($number > 53) {
        $key = $number % 54;
        $number = floor($number / 54) - 1;
        $out = $codes[$key].$out;
    }

    return $codes["".$number].$out;
}

/**
 * 将短网址代码转为数字
 *
 * @param string $code 短网址代码
 *
 * @return int 数字
 */
function get_num($code = '')
{
    if (strlen($code) == 0) {
        return '';
    }
    $codes = "abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ";
    $num = 0;
    $i = strlen($code);
    for ($j = 0; $j < strlen($code); $j++) {
        $i--;
        $char = $code[$j];
        $pos = strpos($codes, $char);
        $num += (pow(54, $i) * ($pos + 1));
    }
    $num--;

    return $num;
}

/*
 * 唯一订单号生成方法
 */
function generate_order_no()
{
    //生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
    //订购日期
    $order_date = date('Y-m-d');
    //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
    $order_id_main = date('YmdHis').rand(10000000, 99999999);
    //订单号码主体长度
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for ($i = 0; $i < $order_id_len; $i++) {
        $order_id_sum += (int)(substr($order_id_main, $i, 1));
    }
    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
    $order_id = $order_id_main.str_pad((100 - $order_id_sum % 100) % 100, 2,
            '0', STR_PAD_LEFT);

    return $order_id;
}

/**
 * 科学计数
 *
 * @param string $number
 *
 * @return string
 */
function eNotation($number): string
{
    if (!$number or $number < 0) {
        return 0;
    }

    $number >= 10000 and $number = round($number / 10000, 2).'w';

    return $number;
}

/**
 * 字符串转数组
 *
 * @param string $tags
 *
 * @return array
 */
function stringToArrays($tags)
{
    if ($tags == '') {
        return [];
    }

    return explode(',', $tags);
}

/**
 * 将时长转为 H:i:s
 *
 * @param string $duration
 *
 * @return string
 */
function durationToString($duration): string
{
    $str = '';
    if ($duration) {
        if ($duration >= 3600) {
            return $str = floor($duration / 3600).":".date("i:s", $duration);
        }
        if ($duration >= 60) {
            return $str = date("i:s", $duration);
        }
        if ($duration < 60) {
            return $str = "00:".date("s", $duration);
        }
    }

    return $str;
}

/**
 * 百度A类签名
 *
 * @param string $url
 *
 * @return string
 */
function bdHash(string $url): string
{
    $key = config('app.hls_key');
    $parse = parse_url($url);

    $domain = $parse['host'];
    $filename = $parse['path'];
    $timestamp = time() + 120 * 60;
    $rand = 0;
    $string = "{$filename}-{$timestamp}-{$rand}-0-{$key}";
    $sign = md5($string);
    $query = "{$timestamp}-{$rand}-0-{$sign}";

    return "auth_key={$query}";
}

/**
 * m3u8签名
 *
 * @param string $url
 *
 * @return string
 */
function m3u8Hash(string $url): string
{
    if ($url) {
        $path = parse_url($url);
        $uri = $path['path'];
    } else {
        return '';
    }

    $key = 'hello&kitty@8888';
    $expires = TIMESTAMP + 7200;
    $md5Key = $key.$uri.$expires;
    $hash = str_replace('=', '',
        strtr(base64_encode(md5($md5Key, true)), "+/", "-_"));

    return "md5={$hash}&expires={$expires}";
}

function time_format(int $timestamps): string
{
    $timestamp = time() - $timestamps;
    if ($timestamp > 3600 * 24 * 7) {
        return date('Y-m-d', $timestamps);
    } elseif ($timestamp > 3600 * 24) {
        return floor($timestamp / (3600 * 24)).'天前';
    } elseif ($timestamp > 3600) {
        return floor($timestamp / 3600).'小时前';
    } elseif ($timestamp > 60) {
        return floor($timestamp / 60).'分钟前';
    } elseif ($timestamp >= 0) {
        return '刚刚';
    } else {
        return '未来';
    }
}



if (!function_exists('register')){
    function register($name)
    {
        $key = null;
        if (strpos($name, '.') !== false) {
            list($name, $key) = explode('.', $name, 2);
        }
        $data = \Yaf\Registry::get($name);
        if ($key) {
            return data_get($data, $key);
        }
        return $data;
    }
}


function copy_all_members( $from,  $to): void {
    // 逐层：当前类 -> 父类 -> 祖父类 -> ...
    for ($rc = new ReflectionObject($from); $rc; $rc = $rc->getParentClass()) {
        foreach ($rc->getProperties() as $prop) {
            // 只处理“这一层声明”的属性，避免重复
            if ($prop->getDeclaringClass()->getName() !== $rc->getName()) continue;
            if ($prop->isStatic()) continue;
            $prop->setAccessible(true);
            // PHP 7.4+: 未初始化的 typed property 读取会报错
            if (method_exists($prop, 'isInitialized') && !$prop->isInitialized($from)) {
                continue;
            }
            $value = $prop->getValue($from);
            // 在“同一个声明类”上获取目标侧同名属性（精确命中父类/祖先类的 private 槽位）
            $decl = $prop->getDeclaringClass();
            if (!$decl->hasProperty($prop->getName())) {
                continue; // 结构不一致时跳过（一般不会发生）
            }
            $targetProp = $decl->getProperty($prop->getName());
            $targetProp->setAccessible(true);
            // PHP 8.1+: readonly 属性不可写
            if (method_exists($targetProp, 'isReadOnly') && $targetProp->isReadOnly()) {
                continue;
            }
            $targetProp->setValue($to, $value);
        }
    }
    // 可选：补充复制“真正的动态公有属性”（未在类中声明的）
    foreach (get_object_vars($from) as $k => $v) {
        if (!property_exists($to, $k)) {
            $to->$k = $v; // PHP 8.2+ 若禁用动态属性，可能需要 #[AllowDynamicProperties]
        }
    }
}



if (!function_exists('mb_similar_text')) {
    
}

function trigger_logger($msg)
{
    if (is_array($msg)) {
        $msg = print_r($msg, 1);
    }
    error_log($msg, 3, APP_PATH.'/storage/logs/log.log');
}

/**
 * 草稿调试日志，写入 storage/logs/draft.log
 */
function draft_log($msg)
{
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    $file = defined('APP_PATH') ? (APP_PATH . '/storage/logs/draft.log') : (dirname(__DIR__, 3) . '/storage/logs/draft.log');
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

/**
 * 格式化时间戳
 *
 * @param string $timestamp
 *
 * @return string
 */
function formatTimestamp(string $timestamps): string
{
    if (empty($timestamps)) {
        return '';
    }
    $timestamp = TIMESTAMP - $timestamps;

    if ($timestamp > 3600 * 24 * 7) {
        return date('Y-m-d', $timestamps);
    }

    if ($timestamp > 3600 * 24) {
        return floor($timestamp / (3600 * 24)).'天前';
    }

    if ($timestamp > 3600) {
        return floor($timestamp / 3600).'小时前';
    }

    if ($timestamp > 60) {
        return floor($timestamp / 60).'分钟前';
    }

    return '刚刚';
}


/**
 * 格式化emoji表情
 *
 * @param $str
 *
 * @return mixed|string|void
 */
function emojiEncode($str)
{
    if (!is_string($str)) {
        return $str;
    }
    if (!$str || $str == 'undefined') {
        return '';
    }

    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
        return addslashes($str[0]);
    }, $text);

    return json_decode($text);
}

/**
 * 转码格式化后的emoji表情
 *
 * @param $str
 *
 * @return mixed|void
 */
function emojiDecode($str)
{
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
        return '\\';
    }, $text); //将两条斜杠变成一条，其他不动

    return json_decode($text);
}

/**
 * 二维数组排序
 *
 * @param $array
 * @param $keys
 * @param int $sort
 *
 * @return mixed
 */
function arraySort($array, $keys, $sort = SORT_DESC)
{
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);

    return $array;
}

/**
 * 截取字符串
 *
 * @param $str
 * @param $length
 * @param int $start
 * @param string $charset
 * @param bool $suffix
 *
 * @return string
 */
function my_substr(
    $str,
    $length,
    $start = 0,
    $charset = "utf-8",
    $suffix = true
) {
    if (function_exists("mb_substr")) {
        if ($suffix) {
            return mb_substr($str, $start, $length, $charset);
        } else {
            return mb_substr($str, $start, $length, $charset);
        }
    } elseif (function_exists('iconv_substr')) {
        if ($suffix) {
            return iconv_substr($str, $start, $length, $charset);
        } else {
            return iconv_substr($str, $start, $length, $charset);
        }
    }
    $re['utf-8']
        = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) {
        return $slice;
    } else {
        return $slice;
    }
}

function parse_input($input)
{
    $post = json_decode($input, 1);
    if (json_last_error()) {
        parse_str($input, $post);
        if (empty($post)) {
            $post = [];
        }
    }

    return $post;
}

function url_video($url, $preview = 0): string
{
    if (empty($url)) {
        return '';
    }
    if (APP_MODULE == 'staff') {
        if (\Illuminate\Support\Str::endsWith($url, '.mp4')) {
            return 'https://play.xmyy8.co/'.$url;
        }

        return TB_VIDEO_ADM_US.'/'.$url;
    }
    //new
    if (USER_COUNTRY == 'CN') {
        $baseAry = [
            '10'  => TB_VIDEO_PWA_CN_10S,
            '30'  => TB_SHORT_VIDEO_PWA_CN,
            '120' => TB_VIDEO_PWA_CN_120S,
        ];
        $base = $baseAry[$preview] ?? TB_VIDEO_PWA_CN;
    } else {
        $baseAry = [
            '10'  => TB_VIDEO_PWA_US_10S,
            '30'  => TB_SHORT_VIDEO_PWA_US,
            '120' => TB_VIDEO_PWA_US_120S,
        ];
        $base = $baseAry[$preview] ?? TB_VIDEO_PWA_US;
    }
    $com_url = $base.'/'.trim($url, '/');
    $hashkey = baiduHash($com_url, parse_url($com_url, PHP_URL_HOST));

    return $com_url.'?'.$hashkey;
}

function url_video_cn($url, $preview = 0): string
{
    if (empty($url)) {
        return '';
    }
    $baseAry = [
        '10'  => TB_VIDEO_PWA_CN_10S,
        '30'  => TB_SHORT_VIDEO_PWA_CN,
        '120' => TB_VIDEO_PWA_CN_120S,
    ];
    $base = $baseAry[$preview] ?? TB_VIDEO_PWA_CN;
    $com_url = $base.'/'.$url;
    $hashkey = baiduHash($url,parse_url($com_url,PHP_URL_PATH));

    return $com_url.'?'.$hashkey;
}

if (!function_exists('url_video_sns')){
    function url_video_sns($path , $v = '0')
    {
        $path = parse_url($path, PHP_URL_PATH);
        $time = time();
        $rand = uniqid();
        $uid = '0';
        if ($v == '1'){
            $data = sprintf("%s-%s-%s-%s-%s-%s", $path, $time, $rand, $uid, __client_ip(), config('web.hls_key'));
        }elseif ($v == '2'){
            $data = sprintf("%s-%s-%s-%s-%s", $path, $time, $rand, $uid, config('web.hls_key'));
        }else{
            $data = sprintf("%s-%s-%s-%s-%s-%s", $path, $time, $rand, $uid, $_SERVER['HTTP_USER_AGENT'] ?? '', config('web.hls_key'));
        }
        $sign = md5($data);
        return trim(HLS_URL, "/") . "$path?auth_key=$time-$rand-$uid-$sign&v=$v";
    }
}

if (!function_exists('__client_ip')){
    function __client_ip()
    {
        if (PHP_SAPI == "cli") {
            return '127.0.0.1';
        }
        if (isset($_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'])) {
            $ip = $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'];
        } else if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $xForwardedForArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $xForwardedForArray[0];
        } elseif (isset($_SERVER['X-REAL-IP'])) {
            $ip = $_SERVER['X-REAL-IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}


function baiduHash($url,$host = '')
{
    // $key = 'INhaDFiNgamplaTE';
    $key = config('app.hls_key');
    $parse = parse_url($url);

    $filename = $parse['path'];
    $timestamp = time() + 120 * 60;
    $rand = 0;
    $s = $host ? "$host-" :'';
    $string = "{$s}{$filename}-{$timestamp}-{$rand}-0-{$key}";
    $sign = md5($string);
    $query = "{$timestamp}-{$rand}-0-{$sign}";
    $s1 = $host ? "&v=2" :"";

    return "auth_key={$query}{$s1}";
}

function url_resource($url, $baseUrl): string
{
    $url = trim($url);
    if (empty($url) || strpos($url, '://') !== false) {
        return $url;
    }
    $baseUrl = rtrim($baseUrl, '/');
    $url = ltrim($url, '/');

    return $baseUrl.'/'.$url;
}

function url_image($url): string
{
    $url = trim($url);
    if (empty($url)) {
        return (string)$url;
    }
    if (strpos($url, '://') !== false) {
        $url = parse_url($url, PHP_URL_PATH);
    }
    return BASE_IMG_URL.'/'.trim($url, '/');
}



function redis(): \tools\RedisService
{
    static $redis = null;
    if ($redis === null) {
        $redis = \tools\RedisService::instance();
    }

    return $redis;
}

if (!function_exists('in_network')){
    function in_network($ip, $networks): bool
    {
        foreach ($networks as $v) {
            if ($v == $ip) {
                return true;
            }
            if (strpos($v, '/') && ip_in_network(USER_IP, $v)) {
                return true;
            }
        }
        return false;
    }
}






if (!function_exists('yacsys')) {
    function yacsys(string $prefix = ''): \tools\LibYac
    {
        static $instance = null;
        if ($prefix !== '') {
            return new \tools\LibYac($prefix);
        }
        if ($instance === null) {
            $instance = new \tools\LibYac('');
        }
        return $instance;
    }
}

if (!function_exists('yac')) {
    function yac(string $prefix = ''): \tools\LibYac
    {
        static $instance = null;
        if ($prefix !== '') {
            return new \tools\LibYac($prefix);
        }
        if ($instance === null) {
            $instance = new \tools\LibYac('');
        }
        return $instance;
    }
}

function cached($key): \Tbold\Library\CacheDb
{
    return \Tbold\Library\CacheDb::make(redis())->setKey($key)->usingFuck(false);
}




/**
 * @param array<\Illuminate\Support\Collection>|array $list
 * @param array|ArrayAccess $idx
 * @param string $key_index
 *
 * @return \Illuminate\Support\Collection
 */
function array_keep_idx($list, $idx, string $key_index = 'id')
{
    $object = collect([]);
    $ary = $list->keyBy($key_index);
    foreach ($idx as $id) {
        if (isset($ary[$id])) {
            $object->push($ary[$id]);
        }
    }

    return $object;
}

function random_ints($min, $max, $length): array
{
    $ary = [];
    try {
        for ($i = 0; $i < $length; $i++) {
            $ary[] = random_int($min, $max);
        }
    } catch (\Throwable $e) {
        $make_seed = function () {
            list($usec, $sec) = explode(' ', microtime());

            return $sec + $usec * 1000000;
        };
        mt_srand($make_seed());
        for ($i = 0; $i < $length - count($ary); $i++) {
            $ary[] = mt_rand($min, $max);
        }
    }

    return $ary;
}

function is_url(...$args):bool
{
    $request = \Yaf\Application::app()->getDispatcher()->getRequest();
    $router  = $request->getParam('::router::');

    return is_null($router) ? false : \website\Router::is($router , ...$args);
}


function url_raw($name , $params = [])
{
    return \website\Router::genRoutePath($name , $params);
}

/**
 * @param null $path
 * @param mixed $parameters
 * @param mixed $domain
 * @param mixed $script
 *
 * @return string
 * @author xiongba
 * @date 2019-12-05 18:53:11
 */
function url(
    $path = null,
    $parameters = [],
    $domain = true,
    $script = null
): string {
    $return_url = '';
    $request = \Yaf\Application::app()->getDispatcher()->getRequest();
       
    if (is_null($path)) {
        return $request->getRequestUri();
    }
    
    // CLI模式下跳过路由检查，直接使用简单URL生成
    if (PHP_SAPI !== 'cli') {
        // 确保路由被正确加载（仅在Web模式下）
        if (method_exists('\\website\\Router', 'loadRouter') && empty(\website\Router::getNamedRoutes())) {
            \website\Router::loadRouter();
            \website\Router::buildTrie();
        }
        
        if (method_exists('\\website\\Router', 'genRoute')) {
            $tmp = \website\Router::genRoute($path, $parameters);
            if (!empty($tmp)) {
                return $tmp;
            }
        }
    }

    $path = trim($path, '/\\ ');

 
    $ary = explode('/', $path, 4);
    switch (count($ary)) {
        case 4:
        case 5:
        case 3:
            list($module, $controller, $action) = $ary;
            break;
        case 2:
            list($controller, $action) = $ary;
            $module = lcfirst($request->getModuleName());
            break;
        default:
            list($action) = $ary;
            $controller = lcfirst($request->getControllerName());
            $module = lcfirst($request->getModuleName());
            break;
    }


    if (empty($script)) {
        $DOCUMENT_URI = \Yaf\Application::app()->getDispatcher()->getRequest()
            ->getServer('SCRIPT_NAME', '/');
    } else {
        $DOCUMENT_URI = "/$script";
    }


    if ($domain) {
        $return_url = sprintf(
            "%s/%s/%s/%s/%s%s",
            getHttpBaseUrl(),
            trim($DOCUMENT_URI, '/'),
            $module,
            $controller,
            $action,
            empty($parameters) ? ''
                : '?'.urldecode(http_build_query($parameters, '', '&'))
        );
    } else {
        $return_url = sprintf(
            "/%s/%s/%s/%s%s",
            trim($DOCUMENT_URI, '/'),
            $module,
            $controller,
            $action,
            empty($parameters) ? ''
                : '?'.urldecode(http_build_query($parameters, '', '&'))
        );
    }

    switch($path){
        case "tag.page":
        case "history.page":
            if(preg_match('/\/\d+$/', $return_url) && !str_ends_with($return_url, '.html')){
                $return_url .= '/';
            }
    }
    return $return_url;
}

function setting($varName, $default = null)
{
    static $setting = null;
    if ($setting === null) {
        $closure = SettingModel::closure('status', SettingModel::STATUS_YES)
            ->pluck('value', 'var_name')->toArray();
        $setting = yac()->fetch("system:setting", $closure, 300);
        
        // 确保返回数组类型，防止缓存问题
        if (!is_array($setting)) {
            $setting = lib_value($closure);
        }
    }

    return $setting[$varName] ?? $default;
}











/**
 * @return string
 * @author xiongba
 * @date 2019-12-05 18:52:37
 */
function getHttpBaseUrl()
{
    $request = \Yaf\Application::app()->getDispatcher()->getRequest();
    $host = $request->getServer('HTTP_HOST');
    $port = $request->getServer('SERVER_PORT');
    $scheme = $request->getServer('REQUEST_SCHEME');
    $port = $port == 80 ? '' : ':'.$port;
    $scheme = strtolower($scheme) == 'http' ? 'http' : 'https';

    if ($scheme == 'http') {
        $scheme = $request->getServer('HTTP_X_FORWARDED_PROTO', 'http');
    }

    return sprintf("%s://%s%s", $scheme, $host, $port);
}


/**
 * 数组转为树
 *
 * @param $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int $root
 *
 * @return array
 * @author xiongba
 * @date 2019-11-08 10:01:18
 */
function arrayToTree(
    $list,
    $pk = 'id',
    $pid = 'pid',
    $child = 'children',
    $root = 0
) {
    $tree = [];
    if (is_array($list)) {
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }

    return $tree;
}

function table_name($class)
{
    return $class::make()->getTable();
}



//-----------------------------------
//smarty 插件
//-----------------------------------






function json_str($value)
{
    return str_replace(
        ["'", '"'],
        ["\'", "'"],
        json_encode($value, JSON_UNESCAPED_UNICODE)
    );
}



function url_avatar($url)
{
    return url_image($url);
}

function getRand($length = 8)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $str;
}

function moneyFormat($money = 0.00, $inDB = false, $ratio = 100)
{
    if ($inDB) {
        //格式化为分 入库
        return (int)($money * $ratio);
    }

    //格式化为 元  输出展示
    return number_format($money / $ratio, 2, '.', '');
}

function matchVirNo($mobile)
{
    preg_match("/1(?:7[01]|6[57])\d{8}/", $mobile, $matches);
    if ($matches) {
        return true;
    } else {
        return false;
    }
}







/**
 * 计算代理收益
 *
 * @param $total
 *
 * @return float|int
 */
function countProxyMoney($total)
{
    //    0~1000 10%
    //    1000~2000 12%
    //    2000~5000 15%
    //    5000~10000 16%
    //    10000~20000 18%
    //    20000~40000 20%
    //    40000~70000 23%
    //    70000~100000 26%
    //    100000以上 30%
    $money = 0;
    if ($total <= 1000) {
        $money = $total * 0.1;
    } elseif ($total > 1000 && $total <= 2000) {
        $money = (1000 * 0.1) + ($total - 1000) * 0.12;
    } elseif ($total > 2000 && $total <= 5000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + ($total - 2000) * 0.15;
    } elseif ($total > 5000 && $total <= 10000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + ($total - 5000)
            * 0.16;
    } elseif ($total > 10000 && $total <= 20000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + (5000 * 0.16)
            + ($total - 10000) * 0.18;
    } elseif ($total > 20000 && $total <= 40000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + (5000 * 0.16)
            + (10000 * 0.18) + ($total - 20000) * 0.2;
    } elseif ($total > 40000 && $total <= 70000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + (5000 * 0.16)
            + (10000 * 0.18) + (20000 * 0.2) + ($total - 40000) * 0.23;
    } elseif ($total > 70000 && $total <= 100000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + (5000 * 0.16)
            + (10000 * 0.18) + (20000 * 0.2) + (30000 * 0.23) + ($total - 70000)
            * 0.26;
    } elseif ($total > 100000) {
        $money = (1000 * 0.1) + (1000 * 0.12) + (3000 * 0.15) + (5000 * 0.16)
            + (10000 * 0.18) + (20000 * 0.2) + (30000 * 0.23) + 30000 * 0.26
            + ($total - 100000) * 0.3;
    }

    return $money;
}

function calcProxyCoin($total, $money)
{
    $getCoin = 0;
    if ($total <= 1000) {
        $percent = 0.1;
    } elseif ($total > 1000 && $total <= 2000) {
        $percent = 0.12;
    } elseif ($total > 2000 && $total <= 5000) {
        $percent = 0.15;
    } elseif ($total > 5000 && $total <= 10000) {
        $percent = 0.16;
    } elseif ($total > 10000 && $total <= 20000) {
        $percent = 0.18;
    } elseif ($total > 20000 && $total <= 40000) {
        $percent = 0.2;
    } elseif ($total > 40000 && $total <= 70000) {
        $percent = 0.23;
    } elseif ($total > 70000 && $total <= 100000) {
        $percent = 0.26;
    } elseif ($total > 100000) {
        $percent = 0.3;
    }
    $getCoin = $percent * $money;

    return $getCoin;
}

// 二维数组分组
function arrayGroup($arr, $key)
{
    $result = [];  //初始化一个数组
    foreach ($arr as $k => $v) {
        $result[$v[$key]][] = $v;  //根据initial 进行数组重新赋值
    }

    return $result;
}

/**
 * 获取远程图片的宽高和体积大小
 *
 */
function myGetImageSize($urls)
{
    $client = new Client();
    foreach ($urls as $key => $url) {
        $promises[$key] = $client->getAsync($url);
    }
    // $promises = [
    //     'image' => $client->getAsync('/image'),
    //     'png'   => $client->getAsync('/image/png'),
    //     'jpeg'  => $client->getAsync('/image/jpeg'),
    //     'webp'  => $client->getAsync('/image/webp')
    // ];
    $results = Promise\inspect_all($promises);

    $return = [];
    // dd($results);
    foreach ($results as $k => $v) {
        if ($v['state'] == 'fulfilled') {
            $data = $v['value']->getBody()->getContents();
            // dd($data);
            $size = getimagesize('data://image/jpeg;base64,'
                .base64_encode($data));
            if (empty($size)) {
                $return[$k]['width'] = 'none';
                $return[$k]['height'] = 'none';
            }
            $return[$k]['width'] = $size[0];
            $return[$k]['height'] = $size[1];
        } else {
            $return[$k]['width'] = 'none';
            $return[$k]['height'] = 'none';
        }
    }

    return $return;
}




if (!function_exists('array_is_list')) {
    
}

function jaddslashes($string)
{
    if (APP_MODULE === 'api' || APP_MODULE === 'apipc') {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = jaddslashes($val);
            }
        } else {
            $string = addslashes(htmlspecialchars(trim($string)));
        }
    }

    return $string;
}

if (!function_exists('calc_formula')){
    if (!function_exists('bcscale')) {
        function bcscale() { }
        function bcadd($a1 , $a2) {return $a1 + $a2;}
        function bccomp($num1, $num2) {return $num1 == $num2 ? 0 : ($num1 > $num2 ? 1 : -1);}
        function bcdiv($num1, $num2){return $num1 / $num2;  }
        function bcmod($num1, $num2) {return $num1 % $num2; }
        function bcmul($num1, $num2){return $num1 * $num2; }
        function bcpow($num1, $num2){return pow($num1 , $num2); }
        function bcpowmod($x, $y, $mod) {return bcmod(bcpow($x, $y), $mod);  }
        function bcsqrt($num1) {return sqrt($num1); }
        function bcsub($num1, $num2) { return $num1 - $num2; }
    }

    /**
     * 计算字符串公式的结果
     * @param ...$args
     *
     * @return array|string|string[]|null
     */
    function calc_formula(...$args)
    {
        bcscale(3);
        $argv = func_get_args();
        $string = str_replace(' ', '', '('.$argv[0].')');
        $string = preg_replace_callback('/\$([0-9\.]+)/', function ($matches) {
            return '$argv[$1]';
        }, $string);
        while (preg_match('/(()?)\(([^\)\(]*)\)/', $string, $match)) {
            while (preg_match('/([0-9\.]+)(\^)([0-9\.]+)/', $match[3], $m)
                || preg_match('/([0-9\.]+)([\*\/\%])([0-9\.]+)/', $match[3], $m)
                || preg_match('/([0-9\.]+)([\+\-])([0-9\.]+)/', $match[3], $m)) {
                switch ($m[2]) {
                    case '+':
                        $result = bcadd($m[1], $m[3]);
                        break;
                    case '-':
                        $result = bcsub($m[1], $m[3]);
                        break;
                    case '*':
                        $result = bcmul($m[1], $m[3]);
                        break;
                    case '/':
                        $result = bcdiv($m[1], $m[3]);
                        break;
                    case '%':
                        $result = bcmod($m[1], $m[3]);
                        break;
                    case '^':
                        $result = bcpow($m[1], $m[3]);
                        break;
                }

                $match[3] = str_replace($m[0], $result, $match[3]);
            }
            if (!empty($match[1]) && function_exists($func = 'bc'.$match[1])) {
                $match[3] = $func($match[3]);
            }
            $string = str_replace($match[0], $match[3], $string);
        }
        return $string;
    }
}

if (!function_exists('manticore')){
    function manticore($index): \Illuminate\Database\Query\Builder
    {
        return DB::connection('manticore')->table($index);
    }
}
if (!function_exists('with_on')){
    function with_on(\Illuminate\Database\Eloquent\Relations\Relation $query , $connection): \Illuminate\Database\Eloquent\Relations\Relation
    {
        $tablePrefix = DB::connection($connection)->getConfig('prefix');
        $query->getQuery()->getGrammar()->setTablePrefix($tablePrefix);
        $query->getQuery()->getQuery()->connection = DB::connection($connection);
        return $query;
    }
}

if (!function_exists('validateEmail')) {
    function validateEmail($email)
    {
        $pattern = "/^\w+([-+.’]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
        if (preg_match($pattern, $email)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('tran2device')) {
    function tran2device($device): string
    {
        $devices = [
            'android' => 'and',
            'ios'     => 'pwa',
            'web'     => 'pwa',
            'pc'      => 'web',
            'other'   => 'web',
        ];
        return $devices[$device] ?? '';
    }
}

if (!function_exists('wf')) {
    function wf($tip, $data, $line = false, $file = '/storage/logs/log.log', $type = 3, $echo = false, $write = true)
    {
        if(defined('APP_MODULE') && APP_MODULE=="staff"){
            if(T_ENV=="product") return NULL;
        }
        if (defined('APP_PATH')) {
            $date = date('Y-m-d H:i:s');
            $option1 = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
            $option2 = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
            $option = $line ? $option1 : $option2;
            $data = json_encode($data, $option);
            $msg = sprintf('[%s]:%s - %s' . PHP_EOL, $date, $tip, trim($data, '"'));
            if ($echo) {
                echo $msg;
            }
            if ($write) {
                error_log($msg, $type, APP_PATH . $file);
            }
        }
    }
}

if (!function_exists('trigger_json')) {
    function trigger_json($msg, $file = '/storage/logs/log.log', $type = 3)
    {
        if (defined('APP_PATH')) {
            $msg = sprintf('[%s]-数据:' . PHP_EOL . '%s' . PHP_EOL, date('Y-m-d H:i:s', time()), json_encode($msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            error_log($msg . PHP_EOL, $type, APP_PATH . $file);
        }
    }
}

if (!function_exists('getID2Code')) {
    function getID2Code($id)
    {
        $aff_code = generate_code($id);
        $verify_code = substr(sha1($id), -4);
        return "{$aff_code}-{$verify_code}";
    }
}

if (!function_exists('getCode2ID')) {
    function getCode2ID($code)
    {
        if (empty($code)) {
            return '';
        }

        list($aff_code, $verfiy_code) = explode('-', $code);
        $id = get_num($aff_code);
        $verify_code_id = substr(sha1($id), -4);
        if ($verify_code_id == $verfiy_code) {
            return $id;
        }
        return 0;//返回一个0
    }
}


if (!function_exists('view')){
    function view():\website\BladeView
    {
        $view = \Yaf\Registry::get('_view');
        if (empty($view)){
            throw new RuntimeException('_view 不存在');
        }
        return $view;
    }
}

if (!function_exists('theme')){
    function theme():Theme
    {
       static $theme = null;
        if (empty($theme)){
            $theme = Theme::getInstance();
        }
        return $theme;
    }
}

if (!function_exists('theme_options')){
    function theme_options():ThemeOptions
    {
       static $theme = null;
        if (empty($theme)){
            $theme = ThemeOptions::getInstance();
        }
        return $theme;
    }
}

function fix_serialized_string($value) {
    return preg_replace_callback('/s:(\d+):"(.*?)";/s', function ($m) {
        return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
    }, $value);
}

if (!function_exists('options')){
    function options($name = null , $default = null){
        // yac()->delete('options:all');
        static $data = null;
        if ($data === null){
           $tmp = yac()->fetch('options:all', function () {
                $data = OptionsModel::query()->get()->toArray();
                $tmp = [];
                foreach ($data as $item) {
                    if (preg_match("#^a:\d+:\{#", $item['value'])) {
                        $value = fix_serialized_string($item['value']);
                        $value = unserialize($value);
                        $item['value'] = $value;
                    }
                    $tmp[$item['name']] = $item['value'];
                }
                return $tmp;
            });

            // 确保返回数组类型，防止缓存问题
            if (!is_array($tmp)) {
                $tmp = [];
            }

            $data = new MyArrayObject($tmp);
        }
        if ($name){
            return $data[$name] ?? $default;
        }
        return $data;
    }
}

if (!function_exists('options_share_domian')){
    function options_share_domian($name){
        return replace_share( options($name) );
    }
}

if (!function_exists('plugins')) {
    function plugins($Handle): \plugins\PluginHandle
    {
        static $new = null;
        if ($new === null){
            $new = new \plugins\PluginHandle($Handle);
        }
        return $new;
    }
}


function _mt($string) {
    if (func_num_args() <= 1) {
        //return I18n::translate($string);
        return $string;
    } else {
        $args = func_get_args();
        array_shift($args);
        //return vsprintf(I18n::translate($string), $args);
        return vsprintf($string, $args);
    }
}

function _me() {
    $args = func_get_args();
    echo call_user_func_array('_mt', $args);
}

function _bv($val)
{
    return var_export($val  ,1);
}


function category($field, $link = false)
{

    if (empty($field)) {
        return false;
    }
    $categories = array();
    foreach ($field as $item) {
        if (isset($item['meta']['type'])
            && $item['meta']['type'] === 'category') {
            array_push($categories, $item['meta']);
        }
    }

    // var_dump($categories);
    if ($categories) {
        $result = array();

        foreach ($categories as $category) {
            if ($link) {
                $result[] = '<a href="'.$category['link'].'">'
                    ._mt($category['name']).'</a>';
            } else {
                $result[] = _mt($category['name']);
            }
        }

        return implode(', ', $result);
    } else {
        return _mt('无分类');
    }
}

function formatPlayer($html){
    

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    // 包裹在一个 div 中防止 <html><body> 被自动添加
    $dom->loadHTML('<div id="wrapper">' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $xpath = new DOMXPath($dom);
    $videos = $xpath->query('//video');

    foreach ($videos as $video) {
        $src = $video->getAttribute('src');
        $pic = $video->getAttribute('pic');

        // 如果缺少 src 或 pic，则跳过
        if (!$src || !$pic) {
            continue;
        }

        $dplayerConfig = [
            'live' => false,
            'autoplay' => false,
            'theme' => '#FADFA3',
            'loop' => false,
            'screenshot' => false,
            'hotkey' => true,
            'preload' => 'metadata',
            'lang' => 'zh-cn',
            'volume' => 0.7,
            'mutex' => true,
            'video_ads_url'=>'',
            'ads_jump_url'=>'',
            'ads_jump_time'=>-1,
            'video' => [
                'url' => $src,
                'pic' => $pic,
                'type' => 'hls',
                'thumbnails' => null,
            ],
        ];

        // <div class="dplayer"
        // data-config='{"live":false,"autoplay":false,"theme":"#FADFA3","loop":false,"screenshot":false,"hotkey":true,
        // "preload":"metadata","lang":"zh-cn","logo":null,"volume":0.69999999999999996,
        // "mutex":true,"video_ads_url":"","ads_jump_url":"","ads_jump_time":-1,
        // "video":{"url":"https:\/\/hls.qzkj.tech\/videos5\/760848d4f6d1dec6f08b59b2314df6d5\/760848d4f6d1dec6f08b59b2314df6d5.m3u8?auth_key=1745996044-6811c90c20bde-0-c8264c121cce5b76dda2f150a11c13e4&v=3&time=0",
        //     "pic":"https:\/\/www.51cg1.com\/upload_01\/xiao\/20250423\/2025042318315948231.jpeg","type":"hls","thumbnails":null},
        //     "open_danmaku":"1"}'>

  

        $configJson = json_encode($dplayerConfig);
        $replacement = $dom->createElement('div');
        $replacement->setAttribute('class', 'dplayer');
        $replacement->setAttribute('data-config', $configJson);

        // 创建 dplayer-video-wrap
        $videoWrap = $dom->createElement('div');
        $videoWrap->setAttribute('class', 'dplayer-video-wrap');

        // 添加 danmaku div
        $danmakuDiv = $dom->createElement('div');
        $danmakuDiv->setAttribute('class', 'dplayer-danmaku');

        $danmakuItem = $dom->createElement('div');
        $danmakuItem->setAttribute('class', 'dplayer-danmaku-item dplayer-danmaku-item--demo');
        $danmakuItem->setAttribute('style', 'opacity: 0.7;');



        $video->parentNode->replaceChild($replacement, $video);
    }

    // 只返回 wrapper 内部的 HTML，去掉 wrapper 标签本身
    $wrapper = $dom->getElementById('wrapper');
    $newHtml = '';
    foreach ($wrapper->childNodes as $child) {
        $newHtml .= $dom->saveHTML($child);
    }

    return $newHtml;

}

/**
 *  HTML 内容转换为 Markdown
 */
function convertHtmlToMarkdown(string $html): string
{
    //替换 <br> 为换行
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

    //提取 <img> 的 data-xkrkllgl 属性作为真实图片地址
    $html = preg_replace_callback('/<img[^>]+>/i', function ($matches) {
        $imgTag = $matches[0];
        if (preg_match('/data-xkrkllgl=["\']([^"\']+)["\']/', $imgTag, $m)) {
            return '![](' . $m[1] . ')';
        }
        // 如果没有 data-xkrkllgl，则尝试 src
        if (preg_match('/src=["\']([^"\']+)["\']/', $imgTag, $m)) {
            return '![](' . $m[1] . ')';
        }
        return ''; // 无效图片
    }, $html);

    //提取 <div class="dplayer" data-config='...'> 的 video.url
    $html = preg_replace_callback(
        '/<div[^>]*class=["\']dplayer["\'][^>]*data-config=(["\'])(.*?)\1[^>]*><\/div>/is',
        function ($m) {
            $config = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5);
            $config = stripslashes($config);

            if (preg_match('/"url"\s*:\s*"([^"]+\.(m3u8|mp4)[^"]*)"/i', $config, $match)) {
                return '[dplayer url="' . $match[1] . '" /]' . "\n";
            }

            return '';
        },
        $html
    );

    //去除 <p> 标签
    $html = preg_replace('/<\/?p[^>]*>/i', '', $html);

    //去除其他 HTML 标签，只保留转换后的 Markdown
    $html = strip_tags($html);

    //去除多余空行
    $html = preg_replace("/\n{3,}/", "\n\n", $html);

    return trim($html);
}

if (!function_exists('get_title_desc')) {
    function get_title_desc($html): array
    {
        // 1. 创建 DOMDocument 并加载 HTML
        $doc = new DOMDocument();
        // 2. 避免因 HTML5 标签或不规范结构抛出警告
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $titleTags = $doc->getElementsByTagName('title');
        $title = $titleTags->length ? trim($titleTags->item(0)->textContent) : '';

        // 5. 用 XPath 提取 <meta name="description">
        $xpath = new DOMXPath($doc);
        $description = '';
        if ($node = $xpath->query('//meta[@name="description"]')->item(0)) {
            $description = trim($node->getAttribute('content'));
        }

        return [$title, $description];
    }
}

function get_content_thumbs(string $htmlOrMarkdown, int $limit = 3): array
{
    $images = [];

    $patterns = [
        '/!\[[^\]]*\]\((.*?)\)/',                
        '/<img[^>]+src=["\']([^"\']+)["\']/i',   
    ];

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $htmlOrMarkdown, $matches)) {
            foreach ($matches[1] as $src) {
                if (stripos($src, 'data:image') === 0) continue;
                $images[] = $src;
                if (count($images) >= $limit) return array_slice($images, 0, $limit);
            }
        }
    }

    if (preg_match_all('/\[(\d+)\]:\s*(https?:\/\/[^\s]+)/i', $htmlOrMarkdown, $referenceMatches)) {
        $refMap = [];
        foreach ($referenceMatches[1] as $i => $key) {
            $refMap[(string) $key] = $referenceMatches[2][$i];
        }

        if (preg_match_all('/!\[[^\]]*]\[(\d+)]/', $htmlOrMarkdown, $refCalls)) {
            foreach ($refCalls[1] as $refKey) {
                if (isset($refMap[(string) $refKey])) {
                    $src = $refMap[(string) $refKey];
                    if (stripos($src, 'data:image') === 0) continue;
                    $images[] = $src;
                    if (count($images) >= $limit) return array_slice($images, 0, $limit);
                }
            }
        }
    }

    return array_slice($images, 0, $limit);
}


if (!function_exists('safe_write')) {
    function safe_write($file, $data)
    {
        $fp = fopen($file, 'rb+');
        $try = 0;
        while (!flock($fp, LOCK_EX | LOCK_NB)) { // 尝试加锁（非阻塞）
            $try++;
            if ($try > 10) {
                fclose($fp);
                throw new RuntimeException("写文件加锁超时");
            }
            usleep(50000); // 50ms 后重试
        }

        fwrite($fp, $data);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}


// 判断是否为外部链接
if (!function_exists('is_external_url')) {
    function is_external_url($url): bool
    {
        if (empty($url) || $url === '#' || strpos($url, 'javascript:') === 0) {
            return false;
        }
        $urlDomain = parse_url($url, PHP_URL_HOST);
        $urlSite = parse_url(options('siteUrl'), PHP_URL_HOST);
        return $urlDomain && $urlDomain !== $urlSite;
    }
}

if (!function_exists('filter_pure_text')){
    function filter_pure_text($text) {

        // 去除 emoji 表情（常见 Unicode 表情区间）
        $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);

        // 去除连续的特殊符号，比如 --- 、*** 、### 、=== 等
        $text = preg_replace('/([-_*#=]{2,})/u', '', $text);

        // 保留中英文、数字、常见中英文标点、空格与换行
        $text = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-Z0-9\s\r\n，。？！：；、“”‘’《》,.!?;:()\-]/u', '', $text);

        // 合并多余空格（不破坏换行）
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // 清理多余空行（可选）
        $text = preg_replace("/[\r\n]{3,}/", "\n\n", $text);
        return trim($text);
    }
}

if (!function_exists('filter_content')){
    function filter_content($text) {

        // 1️⃣ 去除 emoji（常见 Unicode 表情区间）
        $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);
        return trim($text);
    }
}

if (!function_exists('text_excerpt')) {
    function text_excerpt($html, $limit = 150) {

        $text = markdown_to_text($html);
        return mb_substr($text, 0, $limit);
    }
}

function markdown_to_text($text) {
    return strip_tags(html_entity_decode($text));
}



