<?php

namespace Mirages;

use Utils;
use Device;

class Mirages
{
    private static $instance = null;
    private static $config;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$config = new \ArrayObject(options('theme:Mirages'));
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取 Vue 风格 class 映射数组（用于 :class 或服务端 class="...")
     */
    public function bodyClassMap()
    {
        $o = self::$config;

        return array_filter(array_merge([
            'serif-fonts'          => $o['enableSerifFonts'] || ($_COOKIE['MIRAGES_USE_SERIF_FONTS'] ?? '') == 1,
            $this->getThemeClass() => true,
            $o['colorClass'] ?? '' => true,
            'card'                 => !empty($o['useCardView__isTrue']),
            'wrap-code'            => !empty($o['codeBlockOptions__codeWrapLine']),
            'grey-background'      => !empty($o['greyBackground__isTrue']),
            'open'                 => !empty($o['contentTime__timeDiff']),
            'use-navbar'           => ($o['navbarStyle'] == 1),
            'use-sidebar'          => ($o['navbarStyle'] != 1),
            'no-banner'            => empty($o['showBanner']),
            'content-lang-en'      => strtolower($o['contentLang'] ?? '') === 'en',
            'content-serif'        => strtolower($o['contentLang'] ??'') === 'en_serif',
        ], $this->deviceClassMap()));
    }

    /**
     * 获取设备相关 class
     */
    protected function deviceClassMap()
    {
        return array_filter([
            'mobile'         => Device::isMobile(),
            'desktop'        => !Device::isMobile(),
            'windows'        => Device::isWindows(),
            'windows-le-7'   => Device::isWindowsBlowWin8(),
            'macOS'          => Device::isMacOSX(),
            'macOS-ge-10-11' => Device::isELCapitanOrAbove(),
            'macOS-ge-10-12' => Device::isSierraOrAbove(),
            'chrome'         => Device::is('Chrome', 'Edge') || Device::is(array('Chrome', 'OPR')),
            'phone'          => Device::isPhone(),
            'ipad'           => Device::is('iPad'),
            'safari'         => Device::isSafari(),
            'not-safari'     => !Device::isSafari(),
            'android'        => Device::is('Android'),
            'edge'           => Device::is('Edge'),
        ]);
    }

    /**
     * 输出 HTML class="..." 字符串
     */
    public function renderBodyClass($existingClass = null)
    {
        if (!empty($existingClass)) {
            return ''; // 已有 class 则不输出
        }
        $map = $this->bodyClassMap();
        $classList = array_keys(array_filter($map));
        if (empty($classList)) {
            return '';
        }
        return 'class="' . htmlspecialchars(implode(' ', $classList)) . '"';
    }

    protected function getThemeClass()
    {
        $theme = self::$config['baseTheme'] ?? '';
        switch ($theme) {
            case 'mirages-white':
                return 'theme-white';
            case 'mirages-dark':
                return 'theme-dark dark-mode';
            default:
                return '';
        }
    }
}
