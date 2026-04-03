<?php


namespace tools;


use Yaf\Registry;

class CommonService
{
    private static $config = false;

    /**
     * 生成签名
     * @param array $data
     * @return string
     */
    public static function sign(array $data)
    {
        if (empty($data)) {
            return '';
        }
        ksort($data);
        $string = [];
        foreach ($data as $key => $datum) {
            $string[] = "{$key}={$datum}";
        }
        $config = self::getConfig();

//        $string = implode('&', $string) . $config->upload->img->key;
//        $string = implode('&', $string) . config('upload.mp4_key');
        $string = implode('&', $string) . config('mp4.slice_key');
        return md5(hash('sha256', $string));
    }

    /**
     * 获取配置
     * @return bool|mixed
     */
    public static function getConfig()
    {
        if (!self::$config) {
            self::$config = Registry::get('config');
        }
        return self::$config;
    }
}