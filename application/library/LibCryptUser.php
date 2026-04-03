<?php

/*
 * 20170805 签名与数据加密机制
 */

class LibCryptUser
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

    public function setKey($signKey, $encryptKey): LibCryptUser
    {
        $this->signKey = $signKey;
        $this->encryptKey = $encryptKey;
        return $this;
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

    public function encryptToken($aff, $oauthId, $oauthType): string
    {
        $token = self::encrypt(serialize([$aff, $oauthId, $oauthType, time()]),  $this->tokenKey);
        redis()->hSet('sns:user:token', $aff, $token);

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
            $existToken = redis()->hGet('sns:user:token', $aff);
            if ($token != $existToken) {
                return false;
            }
            return $ary;
        }catch (\Throwable $e){
            return false;
        }
    }


    protected static function debug($string)
    {
        if (self::$debug) {
            trigger_log($string);
        }
    }

}
