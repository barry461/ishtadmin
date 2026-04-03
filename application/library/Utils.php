<?php

class Utils
{
    public static function hasValue($field)
    {
        if (is_numeric($field)) {
            return true;
        }
        return strlen($field) > 0;
    }

    public static function isTrue($field, $key = null)
    {
        if (is_array($field) && !empty($key)) {
            return in_array($key, $field);
        }

        return $field > 0 || strtolower($field) == 'true';
    }

    public static function isJumUrl($field, $key = null)
    {
        if (is_string($field)
            && preg_match("#^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+#i",
                $field)) {
            ob_clean();
            header("location: {$field}");
            exit();
        }
    }


    public static function isFalse($field, $key = null)
    {
        return !self::isTrue($field, $key);
    }

    public static function startsWith($haystack, $needle)
    {
        if (strlen($haystack) < strlen($needle)) {
            return false;
        } else {
            return !substr_compare($haystack, $needle, 0, strlen($needle));
        }
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public static function hex2RGBColor($hex, $alpha = 1): string
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        if ($alpha >= 1 || $alpha < 0) {
            return "rgb({$r}, {$g}, {$b})";
        }

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    public static function isHexColor($hex): bool
    {
        if (strlen($hex) != 7 && strlen($hex) != 4) {
            return false;
        }
        if (!preg_match('/^#[0-9a-fA-F]+$/i', $hex)) {
            return false;
        }

        return true;
    }

    public static function isPjax()
    {
        if (array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX']) {
            return true;
        }

        return false;
    }

    public static function fromExternalLinks(): bool
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referer)) {
            return true;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (empty($host)) {
            return true;
        }
        if (strpos($referer, $host) === false) {
            return true;
        }

        return false;
    }

    public static function timeDiff($time): bool
    {
        $time = intval($time);
        if ($time < time() - 86400 * 30) {
            return true;
        }

        return false;
    }

    public static function replaceStaticPath($html):string
    {
        if (!is_string($html)){
            return '';
        }
        return str_replace("{{%STATIC_PATH%}}", STATIC_PATH, $html);
    }

    public static function replaceCDNOptimizeLink($url)
    {
        if (empty($url)) {
            return $url;
        }
        if (is_cdnimg($url)) {
            $url = CDN_XHOST.my_parse_url($url, PHP_URL_PATH);
        }

        if (!(Mirages::$options->cdnDomain__hasValue
            && Mirages::$options->devMode__isFalse)) {
            return $url;
        }

        return preg_replace('/^'.preg_quote(rtrim(Mirages::$options->siteUrl,
                '/'), '/').'/', rtrim(Mirages::$options->cdnDomain, '/'), $url,
            1);
    }

    public static function mapStaticObject($object)
    {
        $arr = array();
        try {
            if (is_string($object) && strpos($object, "=") !== false) {
                $object = base64_decode($object);
            }
            if (class_exists($object)) {
                $ref = new ReflectionClass($object);
                $arr = $ref->getStaticProperties();
            }
        } catch (ReflectionException $e) {
        }

        return $arr;
    }

    public static function httpBuildUrl(array $parts): string
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '').
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '').
            (isset($parts['user']) ? "{$parts['user']}" : '').
            (isset($parts['pass']) ? ":{$parts['pass']}" : '').
            (isset($parts['user']) ? '@' : '').
            (isset($parts['host']) ? "{$parts['host']}" : '').
            (isset($parts['port']) ? ":{$parts['port']}" : '').
            (isset($parts['path']) ? "{$parts['path']}" : '').
            (isset($parts['query']) ? "?{$parts['query']}" : '').
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }

    public static function injectCustomCSS(): string
    {
        $dir = dirname(__DIR__)."/usr/css";
        if (!file_exists($dir)) {
            return "";
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);

        $customCSS = "";
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filename = $fileInfo->getFilename();
                if (self::endsWith(strtolower($filename), '.css')) {
                    $customCSS .= "<link rel=\"stylesheet\" href=\"".STATIC_PATH
                        ."usr/css/{$filename}?v=".STATIC_VERSION."\">\n";
                }
            }
        }

        return $customCSS;
    }

    public static function injectCustomJS(): string
    {
        $dir = dirname(__DIR__)."/usr/js";
        if (!file_exists($dir)) {
            return "";
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);

        $customJS = "";
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filename = $fileInfo->getFilename();
                if (self::endsWith(strtolower($filename), '.js')) {
                    $customJS .= "<script src=\"".STATIC_PATH
                        ."usr/js/{$filename}?v=".STATIC_VERSION
                        ."\" type=\"text/javascript\"></script>\n";
                }
            }
        }

        return $customJS;
    }

    public static function getThumbnailImageAddOn($cdnType, $width = 64)
    {
        if (Mirages::pluginAvailable(102)) {
            if ($cdnType == Mirages_Plugin::CDN_TYPE_OTHERS
                || $cdnType == Mirages_Plugin::CDN_TYPE_LOCAL) {
                return "";
            }
            if ($cdnType == Mirages_Plugin::CDN_TYPE_UPYUN
                && method_exists("Mirages_Plugin", "UPYunSplitTag")) {
                return Mirages_Plugin::UPYunSplitTag()."/max/{$width}";
            }
        }

        if (Mirages::pluginAvailable(103)) {
            if ($cdnType == Mirages_Plugin::CDN_TYPE_ALIYUN_OSS) {
                return "?x-oss-process=image/resize,w_{$width}/quality,q_75";
            }
        }

        return "?imageView2/2/w/{$width}/q/75";
    }

    public static function toCode($code)
    {
        return "<code>{$code}</code>";
    }

    public static function formatDate($time, $format)
    {
        $date = new Typecho_Date($time);

        if (strtoupper($format) == 'NATURAL') {
            return self::naturalDate($date->timeStamp);
        }

        return $date->format($format);
    }

    //    public static function naturalDate($from) {
    //        $now = new Typecho_Date();
    //        $now = $now->timeStamp;
    //        $between = $now - $from;
    //        if ($between > 31536000) {
    //            return date(I18n::dateFormat(), $from);
    //        } else if ($between > 0 && $between < 172800                                // 如果是昨天
    //            && (date('z', $from) + 1 == date('z', $now)                             // 在同一年的情况
    //                || date('z', $from) + 1 == date('L') + 365 + date('z', $now))) {    // 跨年的情况
    //            return _mt('昨天 %s', date('H:i', $from));
    //        } else if ($between == 0) {
    //            return _mt('刚刚');
    //        }
    //        $f = array(
    //            '31536000' => '%d 年前',
    //            '2592000' => '%d 个月前',
    //            '604800' => '%d 星期前',
    //            '86400' => '%d 天前',
    //            '3600' => '%d 小时前',
    //            '60' => '%d 分钟前',
    //            '1' => '%d 秒前',
    //        );
    //        foreach ($f as $k => $v) {
    //            if (0 != $c = floor($between / (int)$k)) {
    //                if ($c == 1) {
    //                    return _mt(sprintf($v, $c));
    //                }
    //                return _mt($v, $c);
    //            }
    //        }
    //        return "";
    //    }

    public static function naturalDate($from)
    {
        $now = new Typecho_Date();
        $now = $now->timeStamp;
        $between = $now - $from;
        if ($between > 86400 * 3) {
            return date(I18n::dateFormat(), $from);
        }

        $f = array(
            '86400' => '%d 天前',
            '3600'  => '%d 小时前',
            '60'    => '%d 分钟前',
            '1'     => '%d 秒前',
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($between / (int) $k)) {
                if ($c == 1) {
                    return _mt(sprintf($v, $c));
                }

                return _mt($v, $c);
            }
        }

        return "xxxx";
    }

    public static function postTitleClass($title)
    {
        $short = 8;
        $long = 25;
        if (preg_match('/[a-zA-Z0-9\-\s\|\(\)\[\]\{\}\/\.\,\?\!]+/i', $title)) {
            $short = 18;
            $long = 60;
        }
        if (mb_strlen($title) <= $short) {
            return " post-title-short";
        } else {
            if (mb_strlen($title) >= $long) {
                return " post-title-long";
            }
        }

        return "";
    }

    public static function isOutJump($link): bool
    {
        $out_jumps = ['15919', '15906'];
        $links = [];
        foreach ($out_jumps as $id) {
            $links[] = '/'.$id.'.html';
            $links[] = '/'.$id.'/';
            $links[] = '/'.$id;
        }
        foreach ($links as $k) {
            $len = strlen($k);
            if (substr($link, -$len) === $k) {
                return true;
            }
        }

        return false;
    }

    public static function randomBanner($banners): string
    {
        $banners = trim($banners);
        $banners = mb_split("\n", $banners);
        $banner = $banners[rand(0, count($banners) - 1)];
        $banner = trim($banner);
//        if (is_cdnimg($banner)){
//            $path = parse_url($banner,PHP_URL_PATH);
//            $banner = $path;
//        }
        return $banner;
    }

    public static function loadFirstImageFromArticle($content) {
        if (preg_match('/<img.*?data-src\=\"((http|https)\:\/\/[^>\"]+?\.(jpg|jpeg|bmp|webp|png))\"[^>]*>/i', $content, $matches)) {
            return $matches[1];
        }
        if (preg_match('/<img.*?src\=\"((http|https)\:\/\/[^>\"]+?\.(jpg|jpeg|bmp|webp|png))\"[^>]*>/i', $content, $matches)) {
            return $matches[1];
        }
        return false;
    }


    protected static function exportThumbnails() {
        $results = array();
        $thumbnails = theme_options()->defaultThumbnails;
        if (!empty($thumbnails)) {
            $thumbnails = mb_split("\n", $thumbnails);
            foreach ($thumbnails as $thumbnail) {
                $thumbnail = trim($thumbnail);
                if ($thumbnail == 'DEFAULT_THUMBS' || $thumbnail == 'DEFAULT_THUMBNAILS') {
                    //                    $defaults = self::exportDefaultThumbnails();
                    //                    $results = array_merge($results, $defaults);
                } else {
                    $thumbnail = str_replace('{THEME_ROOT}', THEME_MIRAGES_ROOT_DIR, $thumbnail);
                    $results[] = $thumbnail;
                }
            }
        } else {
            //            $results = self::exportDefaultThumbnails();
        }
        //        $usrDefaults = self::exportUsrDefaultThumbnails();
        //        $results = array_merge($results, $usrDefaults);
        //        $results = array_unique($results);
        return $results;
    }

    public static function loadDefaultThumbnailForArticle($cid) {
        $defaultThumbs = self::exportThumbnails();
        if (count($defaultThumbs) > 0) {
            $index = abs(intval($cid)) % count($defaultThumbs);
            $thumb = $defaultThumbs[$index];
        } else {
            $thumb = NULL;
        }
        return $thumb;
    }

    public static function randomBackgroundColor($cid) {
        $backgroundColors = array(
            array("#EB3349", "#F45C43"),
            array("#DD5E89", "#F7BB97"),
            array("#4CB8C4", "#3CD3AD"),
            array("#A6FFCB", "#12D8FA", "#1FA2FF"),
            array("#FF512F", "#F09819"),
            array("#1A2980", "#26D0CE"),
            //            array("#FF512F", "#DD2476"),
            array("#F09819", "#EDDE5D"),
            array("#403B4A", "#E7E9BB"),
            array("#003973", "#E5E5BE"),
            array("#348F50", "#56B4D3"),
            array("#EDE574", "#E1F5C4"),
            array("#16A085", "#F4D03F"),
            array("#314755", "#26a0da"),
            array("#e65c00", "#F9D423"),
            array("#2193b0", "#6dd5ed"),
            array("#ec008c", "#fc6767"),
            array("#1488CC", "#2B32B2"),
            array("#ffe259", "#ffa751"),
            array("#11998e", "#38ef7d"),
            array("#00b09b", "#96c93d"),
            array("#3C3B3F", "#605C3C"),
            array("#fc4a1a", "#f7b733"),
        );
        //        $total = 0;
        //        $md5Array = @unpack("c*", md5($title, true));
        //        if (is_array($md5Array) && !empty($md5Array)) {
        //            foreach ($md5Array as $char) {
        //                $total += $char;
        //            }
        //        }
        $cid = intval($cid);
        $index = abs($cid) % count($backgroundColors);
        $array =  @$backgroundColors[$index];
        //        if (count($array) == 2) {
        //            if ($cid % 2 == 0) {
        //                $array[0] = $array[1];
        //            } else {
        //                $array[1] = $array[0];
        //            }
        //        }
        return $array;
    }



    public static function getBannerPosition($banner) {
        if (Utils::startsWith($banner, "[")) {
            $index = strpos($banner, ']');
            if (false !== $index) {
                $position = substr($banner, 1, $index - 1);
                $banner = substr($banner, $index + 1);

                $position = explode(',', $position);
                $position = array_unique($position);
                $position = join(" ", $position);
                return array($banner, trim(strtoupper($position)));
            }
        }
        return [$banner, ""];
    }

    public static function gravatarUrl($mail, $size, $rating, $default, $isSecure = false): string
    {
        if (defined('__TYPECHO_GRAVATAR_PREFIX__')) {
            $url = __TYPECHO_GRAVATAR_PREFIX__;
        } else {
            $url = $isSecure ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';
            $url .= '/avatar/';
        }

        if (!empty($mail)) {
            //$url .= md5(strtolower(trim($mail)));
        }

        $url .= '?s=' . $size;
        $url .= '&amp;r=' . $rating;
        $url .= '&amp;d=' . $default;

        return $url;
    }

}