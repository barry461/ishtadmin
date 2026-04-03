<?php

/*
 * 20170805 签名与数据加密机制
 */

class LibCrypt
{
    public $encryptKey;
    public $signKey;
    public $tokenKey;
    public $iv;
    public static $debug;

    public function __construct()
    {
        self::$debug = false;
        $this->setKey(API_CRYPT_SIGN , API_CRYPT_KEY );
        $this->setPwaIv(API_CRYPT_IV);
        $this->tokenKey = config('encrypt.token_key');
    }

    public function setPwaIv(string $iv){
        $this->iv = $iv;
    }

    public function setKey($signKey, $encryptKey): LibCrypt
    {
        $this->signKey = $signKey;
        $this->encryptKey = $encryptKey;
        return $this;
    }

    #签名
    public static function make_sign($array, $signKey): string
    {
        if (empty($array)) {
            return '';
        }
        ksort($array);
        //$string = http_build_query($array);

        $arr_temp = array();
        foreach ($array as $key => $val) {
            if ($key == 'data') {
                $valTemp = str_replace(' ', '+', $val);
                $arr_temp[] = $key.'='.$valTemp;
            } else {
                $arr_temp[] = $key.'='.$val;
            }
        }
        $string = implode('&', $arr_temp);

        $string = $string.$signKey;

        #先sha256签名 再md5签名
        $sign_str = md5(hash('sha256', $string));

        // trigger_error('string:'.$string);

        return $sign_str;
    }

    #验证
    public static function check_sign($array, $signKey): bool
    {
        if (!isset($array['sign']) || $array['sign'] == '') {
            return false;
        }
        $sign = $array['sign'];

        unset($array['sign']);
        $msg = "我方计算签名，".self::make_sign($array, $signKey);
        self::debug($msg);
        self::debug("对方签名，$sign");

        return self::make_sign($array, $signKey) === $sign;
    }

    #@todo AES加解密
    #加密
    public static function encrypt($input, $cryptKey): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cfb'));
        $key_iv = self::EVPBytesToKey($cryptKey);
        $data = openssl_encrypt($input, 'aes-256-cfb', $key_iv[0],
            OPENSSL_RAW_DATA, $iv);
        $data = $iv.$data;
        $data = strtoupper(bin2hex($data));

        return $data;
    }

    //解密
    public static function decrypt($input, $cryptKey)
    {
        $input = @hex2bin($input);
        if ($input === false){
            return false;
        }
        $iv_len = openssl_cipher_iv_length('aes-256-cfb');
        $iv = substr($input, 0, $iv_len);
        $input = substr($input, $iv_len);
        $key_iv = self::EVPBytesToKey($cryptKey);
        $decrypted = openssl_decrypt($input, 'aes-256-cfb', $key_iv[0],  OPENSSL_RAW_DATA, $iv);

        return $decrypted;
    }


    public static function EVPBytesToKey(
        $password,
        $key_len = '32',
        $iv_len = '16'
    ): array {
        $cache_key = "$password:$key_len:$iv_len";

        $m = array();
        $i = 0;
        $count = 0;
        while ($count < $key_len + $iv_len) {
            $data = $password;
            if ($i > 0) {
                $data = $m[$i - 1].$password;
            }
            $d = md5($data, true);
            $m[] = $d;
            $count += strlen($d);
            $i += 1;
        }
        $ms = join('', $m);
        $key = substr($ms, 0, $key_len);
        $iv = substr($ms, $key_len, $key_len + $iv_len);

        return array($key, $iv);
    }



    public static function encryptPwa($input, $cryptKey, $iv)
    {
        // $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cfb'));
        // $key_iv = self::EVPBytesToKey($cryptKey);
        // $data = openssl_encrypt($input, 'aes-128-cbc', $cryptKey, 0, $iv);
        // $data = $iv.$data;
        // $data = strtoupper(bin2hex($data));
        return openssl_encrypt($input, 'aes-128-cbc', $cryptKey, 0, $iv);
    }


    //解密
    public static function decryptPwa($input, $cryptKey, $iv)
    {
        // $input   = hex2bin($input);
        // $iv_len = openssl_cipher_iv_length('aes-256-cfb');
        // $iv     = substr($iv,0,$iv_len);
        // $input   = substr($input,$iv_len);
        // $key_iv = self::EVPBytesToKey($cryptKey);
        if (is_array($input)){
            debug_print_backtrace();
            exit();
        }
        $input = str_replace(' ', '+', $input);

        return openssl_decrypt($input, 'aes-128-cbc', $cryptKey, 0, $iv);
    }


    /**
     * @param string|array $data
     * @param int $errcode
     *
     * @return false|string
     */
    public function replyData($data, int $errcode = 0)
    {
        $return['errcode'] = (int)$errcode;
        $return['timestamp'] = time();
        $return['data'] = $data;

        self::debug("我方，返回数据：".print_r($return, true));
        if (!empty($return['data'])) {
            $str = json_encode($return['data'], JSON_UNESCAPED_UNICODE);
            $return['data'] = self::encrypt($str, $this->encryptKey);
            $return['sign'] = self::make_sign($return, $this->signKey);
            return json_encode($return, JSON_UNESCAPED_UNICODE);
        }
        return '';
    }

    public function checkInputData($data)
    {
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? '';
        $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_HOST'] ?? '';
        self::debug("请求url:：".($_SERVER['HTTP_HOST'] ?? '').($_SERVER['REQUEST_URI'] ?? ''));
        self::debug("我方收到未验证的数据：".print_r($data, true));
        if (!self::check_sign($data, $this->signKey)) {
            self::debug("我方收到，数据--签名验证失败");
            return false;
        } else {
            $json = self::decrypt($data['data'], $this->encryptKey);
            self::debug("我方收到，解密后的 json 数据--\n".print_r($json, true));
            $tmpData = json_decode($json, true);
            self::debug("我方收到，解密后的 json_decode 数据--\n".print_r($tmpData, true));
            return $tmpData;
        }
    }


    /**
     * @param string|array $data
     * @param int $errcode
     *
     * @return false|string
     */
    public function replyDataPwa($data, $errcode = 0)
    {
        $return['errcode'] = (int)$errcode;
        $return['timestamp'] = time();
        $return['data'] = $data;

        self::debug("我方，返回数据：".print_r($return, true));
        if (!empty($return['data'])) {
            $str = json_encode($return['data'], JSON_UNESCAPED_UNICODE);
            $return['data'] = self::encryptPwa($str, $this->encryptKey , $this->iv);
            $return['sign'] = self::make_sign($return, $this->signKey);
            return json_encode($return, JSON_UNESCAPED_UNICODE);
        }

        return '';
    }

    public function checkInputDataPwa($data)
    {
        self::debug("我方收到未验证的数据：".print_r($data, true));
        if (!self::check_sign($data, $this->signKey)) {
            self::debug('我方收到，数据--签名验证失败');
            return false;
        } else {
            $json = self::decryptPwa($data['data'], $this->encryptKey, $this->iv);
            self::debug("我方收到，解密后的 json 数据--\n".print_r($json, true));
            $json = str_replace(["\\\\:", "\\\\@", "\\\\_", "\\\\"], [":","@", "_", ""], $json);
            $tmpData = json_decode($json, true);
            self::debug("我方收到，解密后的 json_decode 数据--\n".print_r($tmpData, true));

            return $tmpData;
        }
    }


    public function encryptToken($aff, $oauthId, $oauthType): string
    {
        $token = self::encrypt(serialize([$aff, $oauthId, $oauthType, time()]),  $this->tokenKey);
        redis()->hSet('user:token', $aff, $token);

        return $token;
    }

    public function decryptToken($token)
    {
        if (empty($token) || !isset($token[10])){
            return false;
        }
        try{
            $tokenInfo = self::decrypt($token, $this->tokenKey);
            if (empty($tokenInfo)){
                return false;
            }
            list($aff , $oauthId , $oauthType) = $ary = unserialize($tokenInfo);
            $existToken = redis()->hGet('user:token', $aff);
            if ($token != $existToken) {
                return false;
            }
            return $ary;
        }catch (\Throwable $e){
            return false;
        }
    }

    public static function encryptImToken($uuid, $expireTime, $loginTokenIv)
    {
        return self::encrypt(serialize([$uuid, TIMESTAMP + $expireTime]),  $loginTokenIv);
    }


    protected static function debug($string)
    {
        if (self::$debug) {
            trigger_log($string);
        }
    }

}
