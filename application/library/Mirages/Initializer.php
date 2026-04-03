<?php

namespace Mirages;

use Device;
use Utils;

class Initializer
{
    protected $config;
    protected $archive;

    public function __construct(Config $config, array $archive = [])
    {
        $this->config = $config;
        $this->archive = $archive;
    }

    public function init()
    {
        $o = $this->config;

        // 重定向逻辑（简化）
        if (!empty($this->archive['redirect'])) {
            $redirects = explode("\n", $this->archive['redirect']);
            shuffle($redirects);
            $target = trim($redirects[0]);
            if (!empty($this->archive['wechat_jump']) && stripos($_SERVER['HTTP_USER_AGENT'], 'wechat') !== false) {
                header("Location: " . $this->archive['wechat_jump']);
                exit;
            }
            header("Location: $target");
            exit;
        }

        // 设置 banner
        $banner = isset($this->archive['is_index']) ? $o->get('defaultBg') : ($this->archive['banner'] ?? '');
        $banner = Banner::randomBanner(Utils::replaceStaticPath($banner));
        list($bannerUrl, $position) = Banner::getBannerPosition($banner);
        $o->set('banner', Utils::replaceCDNOptimizeLink($bannerUrl));
        $o->set('bannerPosition', $position);

        // 展示 banner 条件
        $o->set('showBanner', $o->get('headTitle__isTrue') || strlen($o->get('banner')) > 5 ||
            in_array($this->archive['type'] ?? '', ['page', 'about', 'links', 'category']));

        // 是否禁用 banner 图像
        $disableBanner = ($this->archive['type'] !== 'index' && Utils::isTrue($this->archive['disableBanner'] ?? false));
        $disableTitle  = $o->get('headTitle__isFalse') || Utils::isFalse($this->archive['headTitle'] ?? true);

        if ($disableBanner && $disableTitle) {
            $o->set('showBanner', false);
        }

        if (in_array($this->archive['type'] ?? '', ['404', 'category', 'tag'])) {
            $o->set('noBannerImage', true);
        }

        // 颜色 class
        $colorClass = Utils::isHexColor($o->get('themeColor')) ? 'color-custom' : 'color-default';
        if ($o->get('codeBlockOptions__codeDark')) {
            $colorClass .= ' code-dark';
        }
        $o->set('colorClass', $colorClass);

        // Banner 高度
        $o->set('bannerHeight', $this->archive['bannerHeight'] ?? $o->get('defaultBgHeight'));
        $o->set('mobileBannerHeight', $this->archive['mobileBannerHeight'] ?? $o->get('defaultMobileBgHeight'));

        define('FULL_BANNER_DISPLAY', ($o->get('bannerHeight') >= 100 || $o->get('mobileBannerHeight') >= 100));

        // 内容语言 class
        $lang = strtolower($this->archive['contentLang'] ?? $o->get('contentLang', ''));
        $langClass = '';
        if ($lang === 'en') {
            $langClass .= ' content-lang-en';
        } elseif ($lang === 'en_serif') {
            $langClass .= ' content-lang-en content-serif';
        }

        // 合成 body class
        $bodyClass = trim(
            THEME_CLASS .
            (USE_SERIF_FONTS ? ' serif-fonts' : '') .
            ' ' . $o->get('colorClass') .
            ($o->get('useCardView__isTrue') ? ' card' : '') .
            ($o->get('codeBlockOptions__codeWrapLine') ? ' wrap-code' : '') .
            ($o->get('greyBackground__isTrue') ? ' grey-background' : '') .
            ($o->get('contentTime__timeDiff') ? ' open' : '') .
            (($o->get('navbarStyle') == 1) ? ' use-navbar' : ' use-sidebar') .
            (!$o->get('showBanner') ? ' no-banner' : '') .
            ' ' . $langClass
        );

        $o->set('bodyClass', $bodyClass);
        $o->set('timeValid', time());

        return $o->export();
    }
}