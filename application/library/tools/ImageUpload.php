<?php
namespace tools;

use Yaf\Registry;

/**
 * 公共上传图片
 * Class ImageUpload
 * @package tools
 * ImageUpload::uploadImg(obj $image, 'ads');
 */
class ImageUpload
{
    /**
     * @param $images
     * @param string $position 图片上传目录
     * @param string $id 图片名称
     * @return bool
     */
    public static function uploadImg($images, $position = 'xiao', $id= '')
    {
        $img = new \CURLFile($images['tmp_name']);
        $img->setMimeType($images['type']);
        $id = $id == '' ? date('YmdHis') . mt_rand(1, 999) : $id;
        $data = [
            'id' => $id,
            'position' => $position,
        ];
        $data['sign'] = CommonService::sign($data);
        $data['cover'] = $img;

        $result = HttpCurl::post(self::getBashURL(), $data);
        $result = json_decode($result, true);

        if (!$result or $result == null or (isset($result['code']) and $result['code'] == 0)) {
            return false;
        }
        return $result['msg'];
    }

    /**
     * 请求地址
     * @return string
     */
    private static function getBashURL():string
    {
        $config = Registry::get('config');
        return $config->upload->img_upload . 'imgUpload.php';
    }
}