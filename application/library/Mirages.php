<?php

use tools\Markdown;

/**
 * @property-read $enableSerifFonts;
 * @property mixed|null $devMode
 * @property mixed|null $webFont
 * @property mixed|null $language
 * @property mixed|null $siteUrl
 * @property mixed|null $rootUrl
 * @property mixed|null $themeUrl
 * @property mixed|null $cdnDomain
 * @property mixed|null $devMode__isFalse
 * @property mixed|true|null $cdnEnabled
 * @property mixed|null $baseTheme
 * @property mixed|null $disableAutoNightTheme
 * @property mixed|true|null $miragesInited
 * @property string $rootFontSize
 * @property string $rootFontSizeStyle
 * @property string $themeColor
 * @property string $disqusShortName
 * @property string $pjaxLoadStyle
 * @property string $showTOCAtLeft
 * @property string $commentsOrder
 * @property string $texOptions__useDollarForInline
 * @property string $codeBlockOptions
 * @property bool $disableTrimLastLineBreakInCodeBlock__isFalse
 * @property bool $devMode__isTrue
 * @property bool $showLog__isTrue
 * @property int $hideReadSettings
 * @property bool|mixed|null $enableMathJax
 * @property bool|mixed|null $enableFlowChat
 * @property bool|mixed|null $enableMermaid
 * @property bool|int|mixed|null $timeValid
 * @property bool|mixed|null $useCardView__isTrue
 * @property bool|mixed|null $defaultBgHeight
 * @property bool|mixed|null $defaultMobileBgHeight
 * @property bool|mixed|null $bannerHeight
 * @property bool|mixed|null $mobileBannerHeight
 * @property bool|mixed|null $navbarLogo
 * @property bool|mixed|null $title
 */
class Mirages extends ArrayObject
{

    public static $version = "7.10.0";
    public static $versionTag = "7.10.0";
    private static $canParseBiaoqing = -1;
    private static $pluginVersion = -1;

    /** @var Mirages */
    public static $options = null;


    public function __construct(
        $array = [],
        $flags = 0,
        $iteratorClass = "ArrayIterator"
    )
    {
        parent::__construct($array, $flags, $iteratorClass);
        self::$options = $this;
    }

    public static function instance($options)
    {
        if (self::$options === null) {
            self::$options = new static($options);
        }
        return self::$options;
    }


    public function __get($name)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }
        if (!str_contains($name, '__')) {
            return null;
        }
        list($name, $needle) = explode('__', $name, 2);

        $val = $this->__get($name);
        if (method_exists(Utils::class, $needle)) {
            return call_user_func([Utils::class, $needle], $val);
        }
        if (is_array($val)) {
            if (empty($needle)) {
                return false;
            }
            return in_array($needle, $val);
        }
        return null;
    }

    public function __set($name, $value)
    {
        self::$options->offsetSet($name, $value);
    }

    public function toolbarItems()
    {
        $s = self::$options['toolbarItems'] ?? '';
        $items = mb_split("\n", $s);
        $toolbarItemsOutput = '';
        $hideRssBarItem = false;
        $hideNightShiftBarItem = false;
        foreach ($items as $toolbarItem) {
            $item = mb_split(":", trim($toolbarItem), 2);
            if (count($item) !== 2) {
                continue;
            }
            $itemName = strtolower(trim($item[0]));
            $itemLink = trim($item[1]);
            if ($itemName === 'rss' && strtoupper($itemLink) === 'HIDE') {
                $hideRssBarItem = true;
                continue;
            }
            if ($itemName === 'read-settings' && strtoupper($itemLink) === 'HIDE') {
                $hideNightShiftBarItem = true;
                $this->hideReadSettings = 1;
                continue;
            }
            $itemNameArr = explode(' ', $itemName);
            $itemClass = count($itemNameArr) > 1 ? $itemName : "fa fa-" . $itemName;
            $toolbarItemsOutput .= '<li><a id="nav-side-toolbar-' . $itemName . '" href="' . $itemLink . '" title="' . ucfirst($itemName) . '" target="_blank"><i class="' . $itemClass . '"></i></a></li>';

        }

        return [$hideRssBarItem, $hideNightShiftBarItem, $toolbarItemsOutput];
    }

    public function renderFooter(): string
    {
        // $footerData = options("plugin:FootMenu");

        // $footMenu = json_decode($footerData['foot_menu'], true) ?? [];
        // $footDesc = json_decode($footerData['foot_desc'], true) ?? [];
        // $footLink = json_decode($footerData['foot_link'], true) ?? [];
        // $contactLink = json_decode($footerData['contact_link'], true) ?? [];

        $optionser = new service\OptionService();

        $foot_desc = $optionser->getSubKey('plugin:FootMenu', 'foot_desc');
        $footDesc = json_decode($this->chkDesc($foot_desc), true) ?? [];
        $footMenu = json_decode($optionser->getSubKey('plugin:FootMenu', 'foot_menu'), true) ?? [];
        // $footDesc = json_decode($optionser->getSubKey('plugin:FootMenu', 'foot_desc'),true) ?? [];
        $footLink = json_decode($optionser->getSubKey('plugin:FootMenu', 'foot_link'), true) ?? [];
        $contactLink = json_decode($optionser->getSubKey('plugin:FootMenu', 'contact_link'), true) ?? [];

        $output = '<footer id="foot-menu">';
        $output .= '<div class="container line-container"></div>';
        $output .= '<div class="container">';

        // 菜单图标
        $output .= '<div class="foot-menu">';
        foreach ($footMenu as $index => $item) {
            $output .= "<h3>";
            switch ($item['target']) {
                case "_blank":
                    $output .= '<a href="' . $item['link'] . '" target="' . $item['target'] . '">';
                    break;
                case "_self":
                    $output .= '<a href="' . $item['link'] . '" target="' . $item['target'] . '">';
                    break;
            }
//            $output .= '<a href="'. $item['link'] .'" target="'. $item['target'] .'">';
//            $output .= '<img class="mode-dark" src="' . options('img_zwimg') . '" alt="' . $item['name'] . '" id="foot-menu-icon-' . $index . '">';
//            $output .= '<img class="mode-white" src="' . options('img_zwimg') . '" alt="' . $item['name'] . '" id="foot-menu-icon2-' . $index . '">';
            if (empty($item['class'])) {
                $output .= '<span>' . $item['name'] . '</span></a>';
            } else {
                $output .= '<i class="' . $item['class'] . '"></i><span>' . $item['name'] . '</span></a>';
            }
            $output .= '</h3>';
//            $output .= '<script type="text/javascript">';
//            $output .= 'loadImage("' . url_image($item['icon']) . '", "foot-menu-icon-' . $index . '");';
//            $output .= 'loadImage("' . url_image($item['icon2']) . '", "foot-menu-icon2-' . $index . '");';
//            $output .= '</script>';
        }
        $output .= '</div>';

        // 文字描述
        $output .= '<div class="footer-desc">';
        $output .= '<b>' . ($footDesc['name'] ?? '') . '</b>';
        $output .= '<p>' . ($footDesc['desc'] ?? '') . '</p>';
        $output .= '</div>';

        // 友情链接
        foreach ($footLink as $item) {
            if (is_external_url($item['link'])) {
                $output .= '<div class="footer-link"><a href="' . $item['link'] . '" target="' . $item['target'] . '"><span style="font-size: 1.17em; font-weight: bold; ">' . $item['name'] . '</span></a></div>';
            } else {
                $output .= '<div class="footer-link"><a href="' . $item['link'] . '" target="' . $item['target'] . '"><h3><span>' . $item['name'] . '</span></h3></a></div>';
            }
        }

        // 1204wst 友情推荐 start
        $friendLinks = json_decode($optionser->getSubKey('plugin:FootMenu', 'friend_links'), true) ?? [];
        if (!empty($friendLinks) && is_array($friendLinks)) {
            $output .= '        <div class="friend-link-box">' . "\n";
            $output .= '          <div class="friend-title-box">' . "\n";
            $output .= '            <div class="friend-title-icon"></div>' . "\n";
            $output .= '            <div class="friend-title">友情推荐</div>' . "\n";
            $output .= '          </div>' . "\n";
            $output .= '          <div class="friend-links">' . "\n";
            foreach ($friendLinks as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $link = $item['link'] ?? '';
                $name = $item['name'] ?? '';
                if (empty($link) || empty($name)) {
                    continue;
                }
                $target = $item['target'] ?? '_blank';
                $rel = isset($item['rel']) && !empty($item['rel']) ? ' rel="' . htmlspecialchars($item['rel']) . '"' : '';
                
                // 如果是外部链接，使用 Urlredirect（与原版保持一致）
                if (is_external_url($link)) {
                    $link = '/Urlredirect?url=' . urlencode($link);
                }
                
                $output .= '            <a href="' . htmlspecialchars($link) . '" target="' . htmlspecialchars($target) . '"' . $rel . '>' . "\n";
                $output .= '              <span class="friend-link-item">' . htmlspecialchars($name) . '</span>' . "\n";
                $output .= '            </a>' . "\n";
            }
            $output .= '          </div>' . "\n";
            $output .= '        </div>' . "\n";
        }
        // 1204wst 友情推荐 end

        // 法律声明链接
        $legalLinks = json_decode($optionser->getSubKey('plugin:FootMenu', 'legal_links'), true) ?? [];
        if (!empty($legalLinks)) {
            $output .= '<div class="footer-legal-links">';
            foreach ($legalLinks as $item) {
                $output .= '<div class="footer-link"><a href="' . $item['link'] . '" target="' . $item['target'] . '"><span>' . $item['name'] . '</span></a></div>';
            }
            $output .= '</div>';
        }

// 底部联系方式
        $output .= '<div class="contact-link">';
        foreach ($contactLink as $index => $item) {
            $output .= '<a href="' . $item['link'] . '" target="' . $item['target'] . '">';
            $output .= '<img class="mode-dark" alt="' . $item['name'] . '" src="' . options('img_zwimg') . '" id="foot-contact-icon-' . $index . '" />';
            $output .= '<img class="mode-white" alt="' . $item['name'] . '" src="' . options('img_zwimg') . '" id="foot-contact-icon2-' . $index . '" />';
            $output .= '</a>';
            $output .= '<script type="text/javascript">';
            $output .= 'loadImage("' . url_image($item['icon']) . '", "foot-contact-icon-' . $index . '");';
            $output .= 'loadImage("' . url_image($item['icon2']) . '", "foot-contact-icon2-' . $index . '");';
            $output .= '</script>';
        }
        $output .= '</div>';

        $output .= '</div>';
        $output .= '</footer>';

        return $output;
    }

    /**
     * 教程图
     * @return string
     */
    public function renderTechIos(): string
    {
        $return = '';
        $techs = options("plugin:TechPhoto");
        if (!empty($techs['ios'])) {
            foreach ($techs['ios'] as $k => $v) {
                if (!empty($v)) {
                    $promptText = isset($techs['ios_prompt'][$k]) ? $techs['ios_prompt'][$k] : '';
                    $descText = isset($techs['ios_txt'][$k]) ? $techs['ios_txt'][$k] : '';

                    $return .= '<div class="swiper-slide">';
                    $return .= '<img src="' . $v . '" alt="' . $techs['ios_txt'][$k] . '"><p> ' . $descText . ' </p>';
                    if (!empty($promptText)) {
                        $promptText = str_replace('{SITE_NAME}', options('title'), $promptText);
                        $return .= '<span ">' . htmlspecialchars($promptText) . '</span>';
                    }
                    $return .= '</div>';
                }
            }
        }

        return $return;
    }

    public function renderTechAnd(): string
    {
        $return = '';
        $techs = options("plugin:TechPhoto");
        if (!empty($techs['android'])) {
            foreach ($techs['android'] as $k => $v) {
                if (!empty($v)) {
                    $promptText = isset($techs['android_prompt'][$k]) ? $techs['android_prompt'][$k] : '';
                    $descText = isset($techs['android_txt'][$k]) ? $techs['android_txt'][$k] : '';

                    $return .= '<div class="swiper-slide">';
                    $return .= '<img src="' . $v . '" alt="' . $techs['android_txt'][$k] . '"><p> ' . $descText . ' </p>';
                    if (!empty($promptText)) {
                        $promptText = str_replace('{SITE_NAME}', options('title'), $promptText);
                        $return .= '<span>' . htmlspecialchars($promptText) . '</span>';
                    }
                    $return .= '</div>';
                }
            }
        }

        return $return;
    }

    public static function init($options = [])
    {
        $that = self::instance($options);
        $tmp = $_COOKIE['MIRAGES_USE_SERIF_FONTS'] ?? null;
        if (defined('USE_SERIF_FONTS')) {
            return $that;
        }

        define("USE_SERIF_FONTS", $that->enableSerifFonts || $tmp == 1);

        define("STATIC_VERSION", $that->devMode == 1 ? (Mirages::$version . "." . time()) : Mirages::$version);
        define("USE_EMBED_FONTS", $that->webFont == 0);
        define("USE_GOOGLE_FONTS", $that->webFont == 1);

        if (strtoupper($that->language) != "AUTO") {
            //I18n::setLang($options->language);
        }

        // define('THEME_MIRAGES_ROOT_DIR', rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        define("TEST_STATIC_PATH", rtrim(preg_replace('/^' . preg_quote(rtrim($that->siteUrl, '/'), '/') . '/', rtrim($that->rootUrl, '/'), $that->themeUrl, 1), '/') . '/');
        if (strlen(trim($that->cdnDomain)) > 0 && $that->devMode__isFalse) {
            $STATIC_PATH = rtrim(preg_replace('/^' . preg_quote(rtrim($that->siteUrl, '/'), '/') . '/', rtrim($that->cdnDomain, '/'), $that->themeUrl, 1), '/') . '/';
            $that->cdnEnabled = true;
        } else {
            $STATIC_PATH = TEST_STATIC_PATH;
            $that->cdnEnabled = false;
        }
        if ($pos = strpos($STATIC_PATH, '://')) {
            if (CDN_CSS) {
                $pos = strpos($STATIC_PATH, '/', $pos + 3);
                $STATIC_PATH = rtrim(CDN_CSS, '/') . substr($STATIC_PATH, $pos);
            }
        }
        define("STATIC_PATH", $STATIC_PATH);

        if ($that->baseTheme == MiragesConst::THEME_MIRAGES) {
            define("LIGHT_THEME_CLASS", "");
        } else {
            define("LIGHT_THEME_CLASS", "theme-white");
        }

        $nightShift = $_COOKIE['MIRAGES_NIGHT_SHIFT_MODE'] ?? '';
        $themeClass = "";
        $nightShiftBtnClass = "";
        if (strtoupper($nightShift) == "NIGHT") {
            $themeClass = "theme-dark dark-mode";
            $nightShiftBtnClass = "night-mode";
        } elseif (strtoupper($nightShift) == "DAY") {
            $themeClass = LIGHT_THEME_CLASS;
            $nightShiftBtnClass = "day-mode";
        } elseif (strtoupper($nightShift) == "SUNSET") {
            $themeClass = LIGHT_THEME_CLASS . " theme-sunset";
            $nightShiftBtnClass = "sunset-mode";
        } elseif (strtoupper($nightShift) == "AUTO") {
            $themeClass = LIGHT_THEME_CLASS;
            $nightShiftBtnClass = "auto-mode";
        } elseif ($that->disableAutoNightTheme <= 0) {
            if ($that->baseTheme == MiragesConst::THEME_MIRAGES) {
                $nightShiftBtnClass = "auto-mode";
            } elseif ($that->baseTheme == MiragesConst::THEME_MIRAGES_WHITE) {
                $themeClass = "theme-white";
                $nightShiftBtnClass = "auto-mode";
            } elseif ($that->baseTheme == MiragesConst::THEME_MIRAGES_DARK) {
                $themeClass = "theme-dark dark-mode";
                $nightShiftBtnClass = "night-mode";
            }
        } elseif ($that->baseTheme == MiragesConst::THEME_MIRAGES) {
            $nightShiftBtnClass = "day-mode";
        } elseif ($that->baseTheme == MiragesConst::THEME_MIRAGES_WHITE) {
            $themeClass = "theme-white";
            $nightShiftBtnClass = "day-mode";
        } elseif ($that->baseTheme == MiragesConst::THEME_MIRAGES_DARK) {
            $themeClass = "theme-dark dark-mode";
            $nightShiftBtnClass = "night-mode";
        }

//
//        $options->enableMathJax = ($options->texOptions__showJax || ($options->useCardView__isTrue && Utils::isTrue($archive->fields->enableMathJax)));
//        $options->enableFlowChat = ($options->flowChartOptions__showFlowChart || ($options->useCardView__isTrue && Utils::isTrue($archive->fields->enableFlowChat)));
//        $options->enableMermaid = ($options->mermaidOptions__showMermaid || ($options->useCardView__isTrue && Utils::isTrue($archive->fields->enableMermaid)));
//

        $bgHeight = $that->defaultBgHeight;
        $mobileBGHeight = $that->defaultMobileBgHeight;
        if (isset($archive)) {
            if ($archive->is('single') && Utils::hasValue($archive->fields->bannerHeight)) {
                $bgHeight = $archive->fields->bannerHeight;
            }
            if ($archive->is('single') && Utils::hasValue($archive->fields->mobileBannerHeight)) {
                $mobileBGHeight = $archive->fields->mobileBannerHeight;
            }
        }


        $that->bannerHeight = $bgHeight;
        $that->mobileBannerHeight = $mobileBGHeight;
        $that->enableMathJax = false;
        $that->enableFlowChat = false;
        $that->enableMermaid = false;
        $that->title = $that->navbarLogo;
        $that->timeValid = time();

        define('IS_HTTPS', $_SERVER['HTTPS'] ?? null);
        define("THEME_CLASS", $themeClass);
        define("NIGHT_SHIFT_BTN_CLASS", $nightShiftBtnClass);
        define("COMMENT_SYSTEM", 0);
        define("FULL_BANNER_DISPLAY", (intval($bgHeight) >= 100 || intval($mobileBGHeight) >= 100));

        return $that;

    }


    public static function getDeviceClass(): string
    {
        $bodyClass = Device::isMobile() ? ' mobile' : ' desktop';
        $bodyClass .= Device::isWindows() ? ' windows' : '';
        $bodyClass .= Device::isWindowsBlowWin8() ? ' windows-le-7' : '';
        $bodyClass .= Device::isMacOSX() ? ' macOS' : '';
        $bodyClass .= Device::isELCapitanOrAbove() ? ' macOS-ge-10-11' : '';
        $bodyClass .= Device::isSierraOrAbove() ? ' macOS-ge-10-12' : '';
        $bodyClass .= (Device::is('Chrome', 'Edge') || Device::is(array('Chrome', 'OPR'))) ? ' chrome' : '';
        $bodyClass .= Device::isPhone() ? ' phone' : '';
        $bodyClass .= Device::is("iPad") ? ' ipad' : '';
        $bodyClass .= Device::isSafari() ? ' safari' : ' not-safari';
        $bodyClass .= Device::is('Android') ? ' android' : '';
        $bodyClass .= Device::is('Edge') ? ' edge' : '';
        $bodyClass .= (Device::isSpider() && Device::isMobile()) ? ' windows wrap-code' : '';
        return $bodyClass;
    }

    public function bodyClass(): string
    {
        $options = $this;

        $bodyClass = THEME_CLASS;
        $bodyClass .= (USE_SERIF_FONTS ? " serif-fonts" : "");
        $bodyClass .= " " . $this->colorClass;
        $bodyClass .= $options->useCardView__isTrue ? ' card ' : '';
        $bodyClass .= $options->codeBlockOptions__codeWrapLine ? ' wrap-code' : '';
        $bodyClass .= $options->greyBackground ? ' grey-background' : '';
        $bodyClass .= $options->contentTime__timeDiff ? ' open' : '';
        $bodyClass .= ($options->navbarStyle == 1) ? ' use-navbar' : ' use-sidebar';
        $bodyClass .= (!$options->showBanner) ? ' no-banner' : '';


        if ('en' == strtolower($options->contentLang)) {
            $bodyClass .= ' content-lang-en';
        } elseif ('en_serif' == strtolower($options->contentLang)) {
            $bodyClass .= ' content-lang-en content-serif';
        }

        return $bodyClass . self::getDeviceClass();
    }

    /**
     * 管理后台 首页底部内容 支持回车换行符
     *
     * @param $desc
     * @return array|string|string[]|null
     */
    public function chkDesc( $desc )
    {
        if(preg_match('/"desc"\s*:\s*"(.*?)"/isU', $desc, $fd_out)){
            $ft_new = preg_replace('/\n/s', '<br>', $fd_out[1]);
            $desc = str_replace($fd_out[1], $ft_new, $desc);
        }

        $desc = preg_replace('/\[\[\[(.*?)\]\]\]/s', '$1', $desc);
        return $desc;
    }
}