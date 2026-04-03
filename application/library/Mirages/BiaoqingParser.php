<?php

namespace Mirages;

class BiaoqingParser
{
    protected static $rootPath = [
        'paopao' => '/static/bq/paopao/',
        'aru'    => '/static/bq/aru/',
    ];

    public static function parse(string $content): string
    {
        $content = preg_replace_callback('/#\[\s*(.*?)\s*]/u', [self::class, 'renderPaopao'], $content);
        $content = preg_replace_callback('/@\(\s*(.*?)\s*\)/u', [self::class, 'renderPaopao'], $content);
        $content = preg_replace_callback('/#\(\s*(.*?)\s*\)/u', [self::class, 'renderAru'], $content);

        return $content;
    }

    protected static function renderPaopao($match): string
    {
        $name = str_replace('%', '', urlencode($match[1]));
        $src  = self::$rootPath['paopao'] . "{$name}_2x.png";
        return "<img class=\"biaoqing newpaopao\" src=\"{$src}\" height=\"30\" width=\"30\" no-zoom />";
    }

    protected static function renderAru($match): string
    {
        $name = str_replace('%', '', urlencode($match[1]));
        $src  = self::$rootPath['aru'] . "{$name}_2x.png";
        return "<img class=\"biaoqing alu\" src=\"{$src}\" height=\"33\" width=\"33\" no-zoom />";
    }

    public static function setRootPath(string $type, string $path): void
    {
        if (isset(self::$rootPath[$type])) {
            self::$rootPath[$type] = rtrim($path, '/') . '/';
        }
    }

    public static function getRootPath(): array
    {
        return self::$rootPath;
    }
}