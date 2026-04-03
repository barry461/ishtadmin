<?php

class SpiderDetector
{
    /**
     * 常见蜘蛛 UA 关键字映射
     * key 为展示用名称，value 为若干 UA 关键字
     */
    protected static $spiders = [
        'Baidu'  => ['Baiduspider'],
        'Google' => ['Googlebot'],
        'Bing'   => ['bingbot', 'BingPreview'],
        '360'    => ['360Spider'],
        'Sogou'  => ['Sogou web spider', 'Sogou inst spider', 'Sogou News Spider', 'Sogou spider'],
        'Yahoo'  => ['Slurp'],
        'Yandex' => ['YandexBot'],
        'DuckDuckGo' => ['DuckDuckBot'],
        'OtherSpider' => ['Spider', 'bot'],
    ];

    /**
     * 根据 UA 判断是否为搜索引擎蜘蛛
     *
     * @param string|null $ua
     * @return array|null [spider_name, ua] 或 null
     */
    public static function detect(?string $ua): ?array
    {
        if (!$ua) {
            return null;
        }

        foreach (self::$spiders as $name => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword === '') {
                    continue;
                }
                if (stripos($ua, $keyword) !== false) {
                    return [$name, $ua];
                }
            }
        }

        return null;
    }
}

