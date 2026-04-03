<?php

namespace Mirages;

use Device;

class DeviceHelper
{
    public static function getDeviceClassMap(): array
    {
        return array_filter([
            'mobile'         => Device::isMobile(),
            'desktop'        => !Device::isMobile(),
            'windows'        => Device::isWindows(),
            'windows-le-7'   => Device::isWindowsBlowWin8(),
            'macOS'          => Device::isMacOSX(),
            'macOS-ge-10-11' => Device::isELCapitanOrAbove(),
            'macOS-ge-10-12' => Device::isSierraOrAbove(),
            'chrome'         => Device::is('Chrome', 'Edge') || Device::is(['Chrome', 'OPR']),
            'phone'          => Device::isPhone(),
            'ipad'           => Device::is('iPad'),
            'safari'         => Device::isSafari(),
            'not-safari'     => !Device::isSafari(),
            'android'        => Device::is('Android'),
            'edge'           => Device::is('Edge'),
            'wrap-code'      => Device::isSpider() && Device::isMobile(),
        ]);
    }

    public static function getDeviceClassString(): string
    {
        $map = self::getDeviceClassMap();
        return implode(' ', array_keys($map));
    }
}
