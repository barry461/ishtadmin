<?php

const APP_MODULE = 'debug';

$global = [
    'oauth_type' => 'android',
    'oauth_id' => 'tbr.me',
    'version' => '2.1.0',
    'token' => '',
    'build_id' => ''
];

try {
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    define("APP_PATH", realpath(dirname(__FILE__) . '/../')); // public 上级目录
    define('APP_ENVIRON', ini_get("yaf.environ"));
    $app = new Yaf\Application(APP_PATH . "/conf/app.ini");
    $app->bootstrap();
} catch (\Throwable $e) {
    throw $e;
}

parse_input_post();




if ($_POST['env']??'' == 'prod'){
    define("SERVER_IP", '172.104.60.250');
    define("SERVER_URL", 'http://172.104.60.250');
    define("SERVER_HOST", 'kkapi3.hyys.info');
}else{
    define("SERVER_IP", 'nginx');
    define("SERVER_URL", 'http://127.0.0.1');
    define("SERVER_HOST", 'local.zpcv2.com');
}


$uri = str_replace(basename(__FILE__), "/api.php", ltrim($_SERVER['REQUEST_URI'], '/'));
$url = SERVER_URL . $uri;


$_POST = array_merge($global, $_POST);
$res = (new bbsdk())->api($url, $_POST);
if (!is_string($res)) {
    header('Content-type: application/json');
    $res = json_encode($res);
}

echo $res;


/**
 * Class  数据交互加密与签名机制
 * Date 20170805
 */
class bbsdk
{

    const DEBUG = true;


    #使用post的传输
    public function api($url, $post)
    {
        $data = array(
            'timestamp' => time(),
        );
        $data['data'] = $post;

        if ($data) {
            $this->debug('接口调用：' . $url, http_build_query($data));
        } else {
            $this->debug('接口调用：' . $url, '');
        }

        $this->debug('加密前字符串', print_r($data, true));
        $data['data'] = LibCrypt::encryptPwa(json_encode($post), API_CRYPT_KEY, API_CRYPT_IV);
        $data['sign'] = LibCrypt::make_sign($data, API_CRYPT_SIGN);
        $this->debug('加密后字符串', print_r($data, true));

        $result = $this->CURL($url, $data);
        $this->debug('result--', print_r($result, true));
        if ($result != '') {
            if (isset($result['data'])) {
                $this->debug('解密前字符串', $result['data']);
                $result['data'] = LibCrypt::decryptPwa($result['data'], API_CRYPT_KEY, API_CRYPT_IV);
                $this->debug('解密后字符串', $result['data']);
                $result['data'] = json_decode($result['data'], true);
                if (is_array($result['data'])) {
                    $this->debug('JSON转数组成功', print_r($result['data'], true));
                } else {
                    $this->debug('JSON转数组成功', '失败');
                }
            }
        }
        return $result;
    }

    #使用post的传输
    public function CURL($url, $data)
    {

        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 30000);
        //忽略证书
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $host = parse_url($url, PHP_URL_HOST);
        if (!empty(SERVER_IP)) {
            $url = str_replace($host, SERVER_IP, $url);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        foreach ($_SERVER as $key => $item) {
            if ($key == 'HTTP_CONTENT_LENGTH' || $key == 'HTTP_HOST') {
                continue;
            }
            if (substr($key, 0, 5) === 'HTTP_') {
                $header[] = sprintf("%s: %s", substr($key, 5), $item);
            }
        }
        $header[] = sprintf("Host: " . SERVER_HOST);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//        curl_setopt($ch, CURLOPT_HEADER,0);//是否需要头部信息（否）
        // 执行操作
        $result = curl_exec($ch);
        $this->debug('接口返回json数据', $result);
        $curlInfo = curl_getinfo($ch);
        $errStr = curl_error($ch);
        curl_close($ch);
        if ($result !== false) {

            #将返回json转换为数组
            $arr_result = json_decode($result, true);
            $error = json_last_error();

            http_response_code($curlInfo['http_code']);

            if ($error === 0 && !is_array($arr_result)) {
                $arr_result['errcode'] = 1;
                $arr_result['msg'] = '服务器返回非数组数据';
                $arr_result['data'] = $result;
                $this->debug('服务器返回数据格式错误', $result);
            } elseif ($error !== 0) {
                return $result;
            }
        } else {
            $arr_result['errcode'] = 1;
            $arr_result['msg'] = '服务器无返回值';
            $this->debug('服务器无响应', $errStr);
        }
        #返回数据
        return $arr_result;
    }

    #日志记录
    public function debug($tempType, $tempStr)
    {
        if (self::DEBUG) {
            $log_name = APP_PATH . '/storage/log.txt';
            if (is_array($tempStr)){
                $tempStr = json_encode($tempStr);
            }
            $tempStr = date('Y-m-d H:i:s') . ' ' . $tempType . "\r\n" . $tempStr . "\r\n\r\n";
            $myfile = fopen($log_name, "a");
            fwrite($myfile, $tempStr);
            fclose($myfile);
        }
    }
}
