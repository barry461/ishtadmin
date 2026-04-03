<?php
namespace tools;

/**
 * Class HttpCurl
 * @package tools
 * HttpCurl::post($url, array('a'=>'post','b'=>'2'));
 * HttpCurl::get($url, array('a'=>'post','b'=>'2'));
 * HttpCurl::get($url);
 */
class HttpCurl
{

    private $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";

    public static function postRaw($url , $data , $header = []){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HEADER, $header);
        }else{
            curl_setopt($curl, CURLOPT_HEADER, false);
        }
        $result = curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);
        if ($error) {
            return false;
        }
        return $result;
    }


    /**
     * post
     * @param $url
     * @param string $data array|string
     * header = array (
     * "Content-Type:application/json",
     * "Content-Type:x-www-form-urlencoded",
     * "Content-type: text/xml",
     * "Content-Type:multipart/form-data"
     * )
     * @return bool|string|void
     */
    public static function post($url, $data = '', $header = [])
    {
        $data = htmlspecialchars_decode(is_array($data) ? http_build_query($data ,'' ,'&') : $data);
        return self::postRaw($url, $data, $header);
    }
    //可以 以传统的 url 带参数?a=b&c=d的形式,也可以是数组形式
    public static function get($url,$params=array()) {
        $ch = curl_init();
        // 设置 curl 相应属性
        if(!empty($params)){
            curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
        }else{
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        if (str_starts_with($url , 'https')){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $returnTransfer = curl_exec($ch);
        curl_close($ch);
        return $returnTransfer;
    }
    /**
     * 设置http请求的参数,get或post
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->httpParams = $params;
        return $this;
    }
    public function putFile($url, $data, &$errMsg = null)
    {
        $post = [];
        // dd($data);
        foreach ($data as $key => $datum) {
            if ($datum[0] != '@') {
                $post[$key] = $datum;
            } else {
                $file = substr($datum, 1);
                $post[$key] = curl_file_create($file, mime_content_type($file), basename($datum));
            }
        }
        $ch = curl_init();
        $this->ifHttpsSetSSLFalse($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20000);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'deflate, gzip');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        $content = curl_exec($ch);
        if ($content === false) {
            $errMsg = curl_error($ch);
        }
        curl_close($ch);
        return $content;
    }
    protected function ifHttpsSetSSLFalse($url)
    {
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
    }

    public function remoteGet($url, $params = array())
    {

        $ch = curl_init();
        // 设置 curl 相应属性
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);    // 模拟用户使用的浏览器
        if (empty($params)) {
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $returnTransfer = curl_exec($ch);
        curl_close($ch);
        return $returnTransfer;
    }


}
