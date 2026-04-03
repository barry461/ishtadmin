<?php

class ThemeOptions extends Mirages
{
    public function __construct(
        $array = [],
        $flags = 0,
        $iteratorClass = "ArrayIterator"
    ) {
        parent::__construct($array, $flags, $iteratorClass);
        $this->parseInit();
    }


    public function JavascriptLocalConst(): array
    {
        // YAC缓存JS常量，减少重复计算
        $result = yac()->fetch('theme:js_const', function () {
            $data = [
                'THEME_VERSION' => '7.10.0',
                'BUILD' => 1494,
                'BASE_SCRIPT_URL' => '/usr/themes/Mirages/',
                'IS_MOBILE' => Device::isMobile(),
                'IS_PHONE' => Device::isPhone(),
                'IS_TABLET' => Device::isTablet(),
                'HAS_LOGIN' => false,
                'IS_HTTPS' => IS_HTTPS,
                'ENABLE_PJAX' => false,
                'ENABLE_WEBP' => Device::canEnableWebP(),
                'SHOW_TOC' => false,
                'ENABLE_IMAGE_SIZE_OPTIMIZE' => false,
                'THEME_COLOR' => Utils::isHexColor($this->themeColor) ?:'#1abc9c',
                'DISQUS_SHORT_NAME' => $this->disqusShortName,
                'COMMENT_SYSTEM' => 0,
                'OWO_API' => $this->owoApi,
                'COMMENT_SYSTEM_DISQUS' => 1,
                'COMMENT_SYSTEM_DUOSHUO' => 2,
                'COMMENT_SYSTEM_EMBED' => 0,
                'PJAX_LOAD_STYLE' => $this->pjaxLoadStyle,
                'PJAX_LOAD_STYLE_SIMPLE' => 0,
                'PJAX_LOAD_STYLE_CIRCLE' => 1,
                'AUTO_NIGHT_SHIFT' => false,
                'USE_MIRAGES_DARK' => false,
                'PREFERS_DARK_MODE' => false,
                'LIGHT_THEME_CLASS' => 'theme-white',
                'TOC_AT_LEFT' => false,
                'SERIF_LOAD_NOTICE' => '加载 Serif 字体可能需要 10 秒钟左右，请耐心等待',
                'ROOT_FONT_SIZE' => '100',
                'BIAOQING_PAOPAO_PATH' => '',
                'BIAOQING_ARU_PATH' => '',
                'CDN_TYPE_OTHERS' => -1,
                'CDN_TYPE_QINIU' => 1,
                'CDN_TYPE_UPYUN' => 2,
                'CDN_TYPE_LOCAL' => 3,
                'CDN_TYPE_ALIYUN_OSS' => 4,
                'CDN_TYPE_QCLOUD_CI' => 5,
                'KEY_CDN_TYPE' => '',
                'UPYUN_SPLIT_TAG' => '!',
                'COMMENTS_ORDER' => 'DESC',
                'ENABLE_MATH_JAX' => false,
                'MATH_JAX_USE_DOLLAR' => false,
                'ENABLE_FLOW_CHART' => false,
                'ENABLE_MERMAID' => false,
                'HIDE_CODE_LINE_NUMBER' => false,
                'TRIM_LAST_LINE_BREAK_IN_CODE_BLOCK' => true,
            ];

            $data['BIAOQING_PAOPAO_PATH'] = '/usr/plugins/Mirages/biaoqing/paopao/';
            $data['BIAOQING_ARU_PATH'] = '/usr/plugins/Mirages/biaoqing/aru/';

            return $data;
        });
        
        // 确保返回数组类型，防止缓存问题导致类型错误
        if (!is_array($result)) {
            // 如果缓存返回非数组，重新执行回调函数获取正确的数组
            return lib_value(function () {
                $data = [
                    'THEME_VERSION' => '7.10.0',
                    'BUILD' => 1494,
                    'BASE_SCRIPT_URL' => '/usr/themes/Mirages/',
                    'IS_MOBILE' => Device::isMobile(),
                    'IS_PHONE' => Device::isPhone(),
                    'IS_TABLET' => Device::isTablet(),
                    'BIAOQING_PAOPAO_PATH' => '/usr/plugins/Mirages/biaoqing/paopao/',
                    'BIAOQING_ARU_PATH' => '/usr/plugins/Mirages/biaoqing/aru/',
                    'CDN_TYPE_OTHERS' => -1,
                    'CDN_TYPE_QINIU' => 1,
                    'CDN_TYPE_UPYUN' => 2,
                    'CDN_TYPE_LOCAL' => 3,
                    'CDN_TYPE_ALIYUN_OSS' => 4,
                    'CDN_TYPE_QCLOUD_CI' => 5,
                    'KEY_CDN_TYPE' => '',
                    'UPYUN_SPLIT_TAG' => '!',
                    'COMMENTS_ORDER' => 'DESC',
                    'ENABLE_MATH_JAX' => false,
                    'MATH_JAX_USE_DOLLAR' => false,
                    'ENABLE_FLOW_CHART' => false,
                    'ENABLE_MERMAID' => false,
                    'HIDE_CODE_LINE_NUMBER' => false,
                    'TRIM_LAST_LINE_BREAK_IN_CODE_BLOCK' => true,
                ];
                return $data;
            });
        }
        
        return $result;
    }

    protected function parseInit()
    {
        if (!$this->offsetExists('appCenterPopSize')) {
            $this->appCenterPopSize = '3*3';
        }
        $this->appCenterPopSizeXY = explode('*', trim($this->appCenterPopSize));
    }

    public static function getInstance()
    {
        return self::$options;
    }

    public function navbarLogo()
    {
        $logo = $this->navbarLogo ?: _mt('首页');
        return str_contains_list($logo  , '://')
            ? sprintf('<img src="%s" alt="Logo" height="40"/>' , $logo)
            : $logo;
    }

    public function get($key , $default)
    {
        return $this->$key ?? $default;
    }


    public static function mergePost(ContentsModel $contentsModel)
    {
        $that = self::getInstance();
        foreach ($contentsModel as $key => $value) {
            $that[$key] = $value;
        }
    }

    public static function mergeArray(array $array)
    {
        $that = self::getInstance();
        foreach ($array as $key => $value) {
            $that[$key] = $value;
        }
    }


    public function rootUrl()
    {
        return '/';
    }

    public function detectBodyClassForPJAX($needle, $element) {
        if (strpos($this->bodyClass, $needle) !== FALSE) {
            return <<<JS
            if (!{$element}.classList.contains('{$needle}')) {
                {$element}.classList.add('{$needle}');
            }
JS;
        } else {
            return <<<JS
            if ({$element}.classList.contains('{$needle}')) {
                {$element}.classList.remove('{$needle}');
            }
JS;
        }
    }

}