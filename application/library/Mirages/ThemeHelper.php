<?php

namespace Mirages;

class ThemeHelper
{
    /**
     * 初始化主题配置 + banner + class
     * @param array $rawConfig 来自数据库或配置文件
     * @param array $context 来自控制器，比如 page/post 的内容结构
     * @return array 渲染模板所需的全部配置（带 banner/class）
     */
    public static function prepare(array $rawConfig, array $context = []): array
    {
        $cfg = new Config($rawConfig);
        $init = new Initializer($cfg, $context);
        return $init->init();
    }

    /**
     * 输出 <body> 标签中的 class="..."
     * @param string|null $existingClass 如模板已有 class，可传入避免重复输出
     */
    public static function renderBodyClass(array $config, $existingClass = null): string
    {
        if (!empty($existingClass)) {
            return ''; // 外部已有 class 属性
        }
        $class = $config['bodyClass'] ?? '';
        $device = DeviceHelper::getDeviceClassString();
        $final = trim($class . ' ' . $device);
        return $final ? 'class="' . htmlspecialchars($final) . '"' : '';
    }

    /**
     * 输出 banner 背景图（如果存在）
     */
    public static function renderBanner(array $config): string
    {
        if (empty($config['showBanner']) || empty($config['banner'])) {
            return '';
        }
        $style = $config['bannerPosition'] ? ' style="background-position:' . $config['bannerPosition'] . ';"' : '';
        return '<div class="banner"' . $style . '><img src="' . htmlspecialchars($config['banner']) . '" alt="banner"></div>';
    }
}
