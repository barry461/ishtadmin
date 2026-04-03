<?php

define('TIMESTAMP', time());  // 全局时间戳
define('USER_IP', client_ip()); // 用户IP
defined('APP_MODULE') or define('APP_MODULE', 'api');
define('DEBUG', true);
const SHARE_REWARD = 259200;
const WITHDRAW_MIN_LIMIT = 500;
const _CLOSURE_SERIALIZABLE_APP_PATH_ = APP_PATH;
const DEFAULT_THUMB = '/new/upload/20221101/2022110111542975409.png';
const DEFAULT_COVER = '/new/upload/20221102/2022110221485585104.png';
const LAY_UI_STATIC = '/static/backend/';
define('HLS_KEY', config('web.hls_key'));
const HLS_URL = SNS_VIDEO_APP_CN;
const NOTIFY_URL = 'http://chg.we-cname.com';
define("VIA", config('pay.app_name'));

// 入口加解密
$request = new Yaf\Request\Simple();
if (!defined('API_CRYPT_KEY')) {
    $_k1 = '2acf7e91e9864673';
    $_s1 = '5589d41f92a597d016b037ac37db243d';
    $_iv = '1c29882d3ddfcfd6';
    $keyData = [
        'v0' => [
            'key' => $_k1,
            'sign' => $_s1,
            'iv' => $_iv,
        ],
        'v1' => [
            'key' => 'b82b97395366e9ce',
            'sign' => 'da86568be9c6208a644870e12d6a5ef4',
            'iv' => 'fcc60c3f632a15d7',
        ],
    ];
    $_ver = $_POST['_ver'] ?? 'v0';
    define('API_CRYPT_KEY', $keyData[$_ver]['key'] ?? $_k1);
    define('API_CRYPT_SIGN', $keyData[$_ver]['sign'] ?? $_s1);
    define('API_CRYPT_IV', $keyData[$_ver]['iv'] ?? $_iv);
}
/*if (empty($_POST)){
    $_POST = json_decode(file_get_contents('php://input'), true);
}*/
if (APP_MODULE === 'api') {
    if (isset($_POST["debug"]) && $_POST["debug"]=="fasdf4ed@1`!" && DEBUG == true){

    }else{
        $crypt = new LibCrypt();
        $_POST = $crypt->checkInputDataPwa($_POST);
    }
} elseif (APP_MODULE == 'merchant') {
    $data = json_decode(file_get_contents('php://input'), true);
    $crypt = new LibCrypt();
    $_POST = $crypt->checkInputData($data);
}
$_POST = my_addslashes($_POST);
if (isset($_POST['oauth_new_id']) && !empty($_POST['oauth_new_id'])
    && $_POST['oauth_new_id'] != '00000000-0000-0000-0000-000000000000') {
    $_POST['oauth_id'] = $_POST['oauth_new_id'];
}
const IM_URL = [
    // 'ws://im1.hitikapi.info:8080',
    'ws://im2.hitikapi.info:8080',
    'ws://im3.hitikapi.info:8080',
    'ws://im4.hitikapi.info:8080',
    'ws://im5.hitikapi.info:8080',
    'ws://im6.hitikapi.info:8080',
    'ws://im7.hitikapi.info:8080',
    'ws://im8.hitikapi.info:8080',
];

if (!defined('IP_POSITION')) {
    $position = \tools\IpLocation::getLocation(USER_IP);
    $position = !is_array($position) || empty($position) ? [] : $position;
    $position['country'] = $position['country'] ?? '国外';
    $position['city'] = $position['city'] ?? '火星';
    $position['province'] = $position['province'] ?? '火星';
    define("IP_POSITION", $position);
}

if (!defined('USER_COUNTRY')) {
    if (!isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $_SERVER['HTTP_CF_IPCOUNTRY'] = IP_POSITION['country'] == '中国' ? 'CN' : 'US';
    }
    define('USER_COUNTRY', strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']));
}

if (!defined('BASE_IMG_URL')) {
    // 处理图片基础路径
    if (APP_MODULE == 'staff' || APP_MODULE == 'adminv2') {
        $_img_url = TB_IMG_ADM_US;
    } elseif (APP_MODULE == 'api' || APP_MODULE == 'WAPI') {
        $_img_url = USER_COUNTRY == 'CN' ? SNS_IMG_APP_CN : SNS_IMG_APP_US;
    } else {
        //$_img_url = USER_COUNTRY == 'CN' ? TB_WEB_OSS_CN : TB_WEB_OSS_US;
        $_img_url = USER_COUNTRY == 'CN' ? SNS_IMG_PWA_CN : SNS_IMG_PWA_US;
    }
    define('BASE_IMG_URL', trim($_img_url, '/'));
}

const BAN_IPS_KEY = 'ban_member_ips';


const CDN_XHOST = SNS_IMG_WEB_CN;

const FMP_CLUSTER_SECRET = 'e10adc3949ba59abbe56e057f20f883e';
const FMP_CLUSTER_MAIN_IP = [
    '127.0.0.1', '192.168.1.106', '192.168.65.1', '172.26.45.183'
];
const FMP_CLUSTER_HOSTS = [
    'http://172.26.45.183/index.php',
];



