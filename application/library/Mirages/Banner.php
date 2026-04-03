<?php

namespace Mirages;

use Utils;

class Banner
{
    /**
     * 根据配置或文章字段，随机选择 banner
     * @param string $banners 多行字符串
     * @return string
     */
    public static function randomBanner($banners)
    {
        $banners = trim($banners);
        if (empty($banners)) return '';
        $list = preg_split('/\r?\n/', $banners);
        $banner = trim($list[array_rand($list)]);

        if (function_exists('is_cdnimg') && is_cdnimg($banner)) {
            $path = my_parse_url($banner, PHP_URL_PATH);
            return defined('CDN_XHOST') ? CDN_XHOST . $path : $banner;
        }

        return $banner;
    }

    /**
     * 提取 banner 背景位置定义
     * @param string $banner 带位置参数的 banner，如 "[top,left]/image.jpg"
     * @return array [url, positionClass]
     */
    public static function getBannerPosition($banner)
    {
        if (strpos($banner, '[') === 0 && ($index = strpos($banner, ']')) !== false) {
            $position = substr($banner, 1, $index - 1);
            $banner = substr($banner, $index + 1);
            $position = implode(' ', array_unique(explode(',', $position)));
            return [trim($banner), strtoupper(trim($position))];
        }
        return [trim($banner), ''];
    }

    /**
     * 尝试从 archive 中读取 banner
     * @param array $archive
     * @param array $options
     * @return string
     */
    public static function loadArchiveBanner(array $archive, array $options)
    {
        if (!empty($archive['banner'])) {
            return $archive['banner'];
        }

        if (!empty($options['disableDefaultBannerInPost__isFalse']) && !empty($archive['cid'])) {
            if (!empty($options['enableLoadFirstImageFromArticle__isTrue'])) {
                $first = \Content::loadFirstImageFromArticle($archive['content'] ?? '');
                if (!empty($first)) return $first;
            }
            return \Content::loadDefaultThumbnailForArticle($archive['cid']);
        }

        return '';
    }
}
