<?php

namespace tools;

class LibMarkdown extends Markdown
{
    public static function parseContent($text,$isWeb = false,$rss = false)
    {
        $text = self::handleMarkdownSyntaxCompatibility($text);
        $content = Markdown::convert($text);
//        $content = preg_replace_callback("#<a([^>+]*)>#i" , function ($matches){
//            $tag = htmlspecialchars_decode($matches[1]);
//            $attrs = self::shortcodeParseAttrs($tag);
//            $router = $attr['router'] ?? self::parseUrlToRouterJson($attrs['href'] ?? '');
//            $class = $attr['className'] ?? '';
//            unset($attrs['href'], $attrs['router'], $attrs['className'], $attrs['class']);
//            $attrStr = '';
//            foreach ($attrs as $key=>$value){
//                $attrStr .= sprintf(' %s="%s"' , $key , $value);
//            }
//            return sprintf('<a class="%s" router="%s"%s>' , $class , $router, $attrStr);
//        }, $content);
        $content = self::replacePlayer($content, $isWeb,$rss);
        if (!$isWeb){
            $content = self::replaceTag('file', $content);
            $content = self::replaceTag('button', $content);
        }
        return $content;
    }

    public static function loadWebMarkdown($text, $rss = false, $title = '')
    {
        $text = str_replace('<!--markdown-->', '', $text);
        $text = self::parseShortcodes($text);
        $html = self::parseContent($text, true, $rss);
        $count = 1;
        $pattern = '#<img(.*?)src=[\'"]([^\\2]*?)"(.*?)>#i';
        return preg_replace_callback($pattern , function ($matches) use (&$count,$rss,$title){
            list($raw, $r1 , $src ,$r2) = $matches;
            $src = url_image($src);
            
            // 提取现有的alt属性
            $hasAlt = preg_match('/\balt\s*=\s*["\']([^"\']*)["\']/', $r1 . $r2, $altMatch);
            
            // 如果没有alt或alt为空，使用文章标题
            if (!$hasAlt || empty(trim($altMatch[1]))) {
                $altAttr = !empty($title) ? ' alt="' . htmlspecialchars($title, ENT_QUOTES) . '"' : '';
            } else {
                $altAttr = '';
            }
            
            if ($rss) {
                return sprintf('<img%ssrc="%s"%s%s>', $r1 , options('img_zwimg'), $altAttr, $r2);
            }
            return sprintf('<img%s class="lazy" src="%s" data-src="%s"%s%s>',
                 $r1 , options('img_zwimg'), $src, $altAttr, $r2);
        },$html);
    }

    public static function parseShortcodes(string $text): string
    {
        return preg_replace_callback('/\[button\s+href="(.*?)"\](.*?)\[\/button\]/is', function ($matches) {
            $href = htmlspecialchars($matches[1], ENT_QUOTES);
            $label = htmlspecialchars($matches[2], ENT_QUOTES);
            return "<a class=\"btn btn-primary\" href=\"{$href}\">{$label}</a>";
        }, $text);
    }

    public static function loadMarkdown($text, $title = ''){
        $text = str_replace('<!--markdown-->', '', $text);
        $html = self::parseContent($text);
        $i = 1;
        $html = preg_replace_callback('#<img(.*?)src=[\'"]([^\\2]*?)"(.*?)>#i',
            function ($match) use (&$i, $title) {
                $src = url_image($match[2]);
                $attr = \tools\LibMarkdown::shortcodeParseAttrs($match[0]);
                $append = '';
                if (isset($attr['alt']) && \Illuminate\Support\Str::contains($attr['alt'], '#@') ) {
                    list(, $rawRouter) = explode('#@', $attr['alt'], 2);
                    if (strpos($rawRouter, '^^') === false) {
                        $rawRouter .= '^^';
                    }
                    list($router, $uri) = explode('^^', $rawRouter, 2);
                    if (\Illuminate\Support\Str::contains($uri, ['http://', 'https://'])) {
                        $redirect_type = 2;
                    } else {
                        $redirect_type = 1;
                        $uri = \FlutterRouterModel::parseRouterUri($uri, $router);
                    }
                    $tmp = [
                        'url_str' => $uri,
                        'redirect_type' => $redirect_type,
                    ];
                    $append = sprintf('class="jump" router="%s"' , htmlentities(json_encode($tmp)));
                }
                
                // 如果没有alt属性或alt为空，添加文章标题作为alt
                $altAttr = '';
                if (!isset($attr['alt']) || empty(trim($attr['alt']))) {
                    if (!empty($title)) {
                        $altAttr = ' alt="' . htmlspecialchars($title, ENT_QUOTES) . '"';
                    }
                }
                
                $zwt = options('img_zwimg');
            //     return sprintf('<img %s src="%s" %s %s/> ', trim($match[1]), $src, $append,
            //         trim($match[3]));
            // }, $html);
            return sprintf('<img %s src="%s" data-xuid="%s" data-xkrkllgl="%s"%s %s>',
            trim($match[1]), $zwt, $i++, $src, $altAttr, trim($match[3]));
    }, $html);
        $html = preg_replace_callback('#<p><([a-z]+)#i',
            function ($match) use (&$i) {
                if (strcasecmp($match[1], 'br') === 0) {
                    return $match[0];
                }
                return sprintf("<p><br><%s" , $match[1]);
            }, $html);
//        $html = str_replace('</p><p><img', '<br><img', $html);
//        $html = str_replace('<p><img', '<p><br><img', $html);
//        $html = str_replace('/> </p><p><video', '/> <br><video', $html);
        return $html;
    }

    public static function getVideoFromHtml($html){
        $pattern = "/<video[^<>]*src=[\"]([^\"]+)[\"][^<>]*>/im";
        preg_match_all($pattern,$html,$match);

        return $match[1];
    }

    public static function getWordFromHtml($str, $num = 50)
    {
        if (!$str) {
            return $str;
        }

        //把一些预定义的 HTML 实体转换为字符
        $html_string = htmlspecialchars_decode($str);
        //将空格替换成空
        $content = str_replace(" ", "", $html_string);
        //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        $contents = strip_tags($content);

        $text = [];
        $lines = explode("\n", $contents);
        foreach ($lines as $v) {
            $text[] = trim($v, "\n");
        }
        $text = array_filter($text);
        $contents = implode("。", $text);

        //返回字符串中的前$num字符串长度的字符
        return mb_strlen($contents, 'utf-8') > $num ? mb_substr($contents, 0, $num, "utf-8") . '....' : mb_substr($contents, 0, $num, "utf-8");
    }

    public static function getImgFromHtml($html){
        $pattern = "/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$html,$match);

        return $match[1];
    }

    public static function getCoversFromHtml($html){
        $pattern = '/pic="([^"]+)/';
        preg_match_all($pattern,$html,$match);

        return $match[1];
    }


    private static function parsePlayerPath($url)
    {
        //不是r2上传的MP4文件
        if (!str_contains($url, \PostMediaModel::getR2Mp4PlayUrl())){
            if (str_contains($url ,'{{')){
                $url = str_replace(['https://', 'http://'], '', $url);
                $url = str_replace('{{video-cdn}}' , 'https://c.cc' , $url);
            }elseif (!str_contains($url , '://')){
                $url = 'https://c.cc/' . trim($url , '/');
            }
            $url = parse_url($url , PHP_URL_PATH);
            $url = url_video_sns($url, '2');
        }
        return $url;

    }


    protected static function replacePlayer($text , $isWeb = false,$rss = false){
        $pattern = self::getShortcodeRegex(['dplayer']);
        return preg_replace_callback("/$pattern/", function ($matches) use ($isWeb,$rss){
            if ($rss){
                return '';
            }
            $tag = htmlspecialchars_decode($matches[3]);
            $attrs = self::shortcodeParseAttrs($tag);
            $url = self::parsePlayerPath(trim($attrs['url'] ?? ''));
            //兼容pic 和 cover
            $pic = $attrs['pic'] ?? '';
            $cover = $attrs['cover'] ?? '';
            $pic = $cover ?: $pic;
            if ($pic) {
                $pic = str_replace('{{img}}', '', $pic);
            }
            $pic = url_image($pic ? : DEFAULT_COVER);
            $append = '';
            if (data_get($attrs, 'width') && data_get($attrs, 'height')) {
                $append = sprintf(' width="%s" height="%s"' , $attrs['width'] , $attrs['height']);
            }
            if ($isWeb){
                $config = [
                    "live" => false,
                    "autoplay" => false,
                    "theme" => "#FADFA3",
                    "loop" => false,
                    "screenshot" => false,
                    "hotkey" => true,
                    "preload" => "metadata",
                    "lang" => "zh-cn",
                    "logo" => null,
                    "volume" => 0.7,
                    "mutex" => true,
                    "video_ads_url" => "",
                    "ads_jump_url" => "",
                    "ads_jump_time" => -1,
                    "video" => [
                        "url" => $url,
                        "type" => "hls",
                        "thumbnails" => null,
                    ],
                    "open_danmaku" => "1",
                ];
                $json = json_encode($config);
                return '<div class="dplayer" data-config=\''.$json.'\'></div>';
            }else{
                return <<<HTML
<video src="{$url}" pic="{$pic}"$append></video>
HTML;
            }
}, $text);
    }

    protected static function replaceTag($tag , $text){
        $pattern = self::getShortcodeRegex([$tag]);
        $text = preg_replace_callback("/$pattern/",function ($matches){
            $tag = htmlspecialchars_decode($matches[3]);
            $attr = self::shortcodeParseAttrs($tag);
            $router = $attr['router'] ?? self::parseUrlToRouterJson($attr['href'] ?? '');
            $class = $attr['className'] ?? 'button';
            unset($attr['href'], $attr['router'], $attr['className'], $attr['class']);
            $attrStr = '';
            foreach ($attr as $key=>$value){
                $attrStr .= sprintf(' %s="%s"' , $key , $value);
            }
            return sprintf('<span class="%s" router="%s" %s>%s</span>', $class, $router, $attrStr, $matches[5]);
        }, $text);
        return $text;
    }

    protected static function parseUrlToRouterJson($href)
    {
        $router = ['url_str' => ''];
        if (strpos($href, '://') !== false) {
            $router['url_str'] = $href;
            $router['redirect_type'] = 0;
        } elseif (strpos($href, '/archives/') !== false) {
            $cid = str_replace('/archives/', '', $href);
            $router['url_str'] = 'homecontentdetailpage??cid='.intval($cid);
        } elseif (strpos($href, '/archives-') !== false) {
            $router['url_str'] = "https://{share.chgweb}/?path=$href";
            $router['redirect_type'] = 0;
        }
        if (!isset($router['redirect_type'])){
            $router['redirect_type'] = 1;
        }
        return htmlentities(json_encode($router));
    }


    public static function shortcodeParseAttrs($text)
    {
        $atts = array();
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }

            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    public static function getShortcodeRegex($tagnames = null): string
    {
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }


    private static function handleMarkdownSyntaxCompatibility($markdown)
    {
        $markdown = str_replace("```objective-c", "```objectivec", $markdown);
        $markdown = str_replace("```c++", "```cpp", $markdown);
        $markdown = str_replace("```c#", "```csharp", $markdown);
        $markdown = str_replace("```f#", "```fsharp", $markdown);
        //        $markdown = self::escapeTexBlock($markdown);
        //$markdown = self::escapeAllMarkdown($markdown);

        return str_replace("```F#", "```fsharp", $markdown);
    }

}


/**
 * Markdown解析
 *
 * @package Markdown
 * @copyright Copyright (c) 2014 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Markdown
{
    /**
     * convert
     *
     * @param string $text
     *
     * @return string
     */
    public static function convert($text)
    {
        static $parser;

        if (empty($parser)) {
            $parser = new HyperDown();

            $parser->hook('afterParseCode', function ($html) {
                return preg_replace("/<code class=\"([_a-z0-9-]+)\">/i",
                    "<code class=\"lang-\\1\">", $html);
            });

            $parser->hook('beforeParseInline', function ($html) use ($parser) {
                return preg_replace_callback("/^\s*<!\-\-\s*more\s*\-\->\s*$/s",
                    function ($matches) use ($parser) {
                        return $parser->makeHolder('<!--more-->');
                    }, $html);
            });

            $parser->enableHtml(true);
            $parser->_commonWhiteList .= '|img|cite|embed|iframe';
            $parser->_specialWhiteList = array_merge($parser->_specialWhiteList,
                array(
                    'ol' => 'ol|li',
                    'ul' => 'ul|li',
                    'blockquote' => 'blockquote',
                    'pre' => 'pre|code',
                ));
        }

        return str_replace('<p><!--more--></p>', '<!--more-->',
            $parser->makeHtml($text));
    }

    /**
     * transerCodeClass
     *
     * @param string $html
     *
     * @return string
     */
    public static function transerCodeClass($html)
    {
        return preg_replace("/<code class=\"([_a-z0-9-]+)\">/i",
            "<code class=\"lang-\\1\">", $html);
    }

    /**
     * @param $html
     *
     * @return mixed
     */
    public static function transerComment($html)
    {
        return preg_replace_callback("/<!\-\-(.+?)\-\->/s",
            array('Markdown', 'transerCommentCallback'), $html);
    }

    /**
     * @param $matches
     *
     * @return string
     */
    public static function transerCommentCallback($matches)
    {
        return self::$parser->makeHolder($matches[0]);
    }
}

/**
 * Parser
 *
 * @copyright Copyright (c) 2012 SegmentFault Team. (http://segmentfault.com)
 * @author Joyqi <joyqi@segmentfault.com>
 * @license BSD License
 */
class HyperDown
{
    /**
     * _whiteList
     *
     * @var string
     */
    public $_commonWhiteList = 'kbd|b|i|strong|em|sup|sub|br|code|del|a|hr|small';

    /**
     * _specialWhiteList
     *
     * @var mixed
     * @access private
     */
    public $_specialWhiteList
        = array(
            'table' => 'table|tbody|thead|tfoot|tr|td|th',
        );

    /**
     * _footnotes
     *
     * @var array
     */
    public $_footnotes;

    /**
     * _blocks
     *
     * @var array
     */
    private $_blocks;

    /**
     * _current
     *
     * @var string
     */
    private $_current;

    /**
     * _pos
     *
     * @var int
     */
    private $_pos;

    /**
     * _definitions
     *
     * @var array
     */
    public $_definitions;

    /**
     * @var array
     */
    private $_hooks = array();

    /**
     * @var array
     */
    private $_holders;

    /**
     * @var string
     */
    private $_uniqid;

    /**
     * @var int
     */
    private $_id;

    /**
     * @var bool
     */
    private $_html = false;

    /**
     * makeHtml
     *
     * @param mixed $text
     *
     * @return string
     */
    public function makeHtml($text)
    {
        $this->_footnotes = array();
        $this->_definitions = array();
        $this->_holders = array();
        $this->_uniqid = md5(uniqid());
        $this->_id = 0;

        $text = $this->initText($text);
        $html = $this->parse($text);
        $html = $this->makeFootnotes($html);

        return $this->call('makeHtml', $html);
    }

    /**
     * @param $html
     */
    public function enableHtml($html = true)
    {
        $this->_html = $html;
    }

    /**
     * @param $type
     * @param $callback
     */
    public function hook($type, $callback)
    {
        $this->_hooks[$type][] = $callback;
    }

    /**
     * @param $str
     *
     * @return string
     */
    public function makeHolder($str)
    {
        $key = "\r".$this->_uniqid.$this->_id."\r";
        $this->_id++;
        $this->_holders[$key] = $str;

        return $key;
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    private function initText($text)
    {
        $text = str_replace(array("\t", "\r"), array('    ', ''), $text);

        return $text;
    }

    /**
     * @param $html
     *
     * @return string
     */
    private function makeFootnotes($html)
    {
        if (count($this->_footnotes) > 0) {
            $html .= '<div class="footnotes"><hr><ol>';
            $index = 1;

            while ($val = array_shift($this->_footnotes)) {
                if (is_string($val)) {
                    $val .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                } else {
                    $val[count($val) - 1]
                        .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                    $val = count($val) > 1 ? $this->parse(implode("\n", $val))
                        : $this->parseInline($val[0]);
                }

                $html .= "<li id=\"fn-{$index}\">{$val}</li>";
                $index++;
            }

            $html .= '</ol></div>';
        }

        return $html;
    }

    /**
     * parse
     *
     * @param string $text
     * @param bool $inline
     *
     * @return string
     */
    private function parse($text, $inline = false)
    {
        $blocks = $this->parseBlock($text, $lines);
        $html = '';

        foreach ($blocks as $block) {
            list ($type, $start, $end, $value) = $block;
            $extract = array_slice($lines, $start, $end - $start + 1);
            $method = 'parse'.ucfirst($type);

            $extract = $this->call('before'.ucfirst($method), $extract, $value);
            $result = $this->{$method}($extract, $value);
            $result = $this->call('after'.ucfirst($method), $result, $value);

            $html .= $result;
        }

        // inline mode for single normal block
        if ($inline && count($blocks) == 1 && $blocks[0][0] == 'normal') {
            // remove p tag
            $html = preg_replace("/^\s*<p>(.*)<\/p>\s*$/", "\\1", $html);
        }

        return $html;
    }

    /**
     * @param $text
     * @param $clearHolders
     *
     * @return string
     */
    private function releaseHolder($text, $clearHolders = true)
    {
        $deep = 0;
        while (strpos($text, "\r") !== false && $deep < 10) {
            $text = str_replace(array_keys($this->_holders),
                array_values($this->_holders), $text);
            $deep++;
        }

        if ($clearHolders) {
            $this->_holders = array();
        }

        return $text;
    }

    /**
     * @param $type
     * @param $value
     *
     * @return mixed
     */
    public function call($type, $value)
    {
        if (empty($this->_hooks[$type])) {
            return $value;
        }

        $args = func_get_args();
        $args = array_slice($args, 1);

        foreach ($this->_hooks[$type] as $callback) {
            $value = call_user_func_array($callback, $args);
            $args[0] = $value;
        }

        return $value;
    }

    /**
     * parseInline
     *
     * @param string $text
     * @param string $whiteList
     * @param bool $clearHolders
     * @param bool $enableAutoLink
     *
     * @return string
     */
    public function parseInline(
        $text,
        $whiteList = '',
        $clearHolders = true,
        $enableAutoLink = true
    ) {
        $self = $this;
        $text = $this->call('beforeParseInline', $text);

        // code
        $text = preg_replace_callback(
            "/(^|[^\\\])(`+)(.+?)\\2/",
            function ($matches) use ($self) {
                return $matches[1].$self->makeHolder(
                        '<code>'.htmlspecialchars($matches[3]).'</code>'
                    );
            },
            $text
        );

        // mathjax
        $text = preg_replace_callback(
            "/(^|[^\\\])(\\$+)(.+?)\\2/",
            function ($matches) use ($self) {
                return $matches[1].$self->makeHolder(
                        $matches[2].htmlspecialchars($matches[3]).$matches[2]
                    );
            },
            $text
        );

        // escape
        $text = preg_replace_callback(
            "/\\\(.)/u",
            function ($matches) use ($self) {
                $escaped = htmlspecialchars($matches[1]);
                $escaped = str_replace('$', '&dollar;', $escaped);

                return $self->makeHolder($escaped);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/<(https?:\/\/.+)>/i",
            function ($matches) use ($self) {
                $url = $self->cleanUrl($matches[1]);
                $link = $self->call('parseLink', $matches[1]);

                return $self->makeHolder(
                    "<a href=\"{$url}\">{$link}</a>"
                );
            },
            $text
        );

        // encode unsafe tags
        $text = preg_replace_callback(
            "/<(\/?)([a-z0-9-]+)(\s+[^>]*)?>/i",
            function ($matches) use ($self, $whiteList) {
                if (false !== stripos(
                        '|'.$self->_commonWhiteList.'|'.$whiteList.'|',
                        '|'.$matches[2].'|'
                    )
                ) {
                    return $self->makeHolder($matches[0]);
                } else {
                    return htmlspecialchars($matches[0]);
                }
            },
            $text
        );

        $text = str_replace(array('<', '>'), array('&lt;', '&gt;'), $text);

        // footnote
        $text = preg_replace_callback(
            "/\[\^((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self) {
                $id = array_search($matches[1], $self->_footnotes);

                if (false === $id) {
                    $id = count($self->_footnotes) + 1;
                    $self->_footnotes[$id] = $self->parseInline($matches[1], '',
                        false);
                }

                return $self->makeHolder(
                    "<sup id=\"fnref-{$id}\"><a href=\"#fn-{$id}\" class=\"footnote-ref\">{$id}</a></sup>"
                );
            },
            $text
        );

        // image
        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
            function ($matches) use ($self) {
                $escaped = htmlspecialchars($self->escapeBracket($matches[1]));
                $url = $self->escapeBracket($matches[2]);
                $url = $self->cleanUrl($url);

                return $self->makeHolder(
                    "<img src=\"{$url}\" alt=\"{$escaped}\" title=\"{$escaped}\">"
                );
            },
            $text
        );

        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self) {
                $escaped = htmlspecialchars($self->escapeBracket($matches[1]));

                $result = isset($self->_definitions[$matches[2]]) ?
                    "<img src=\"{$self->_definitions[$matches[2]]}\" alt=\"{$escaped}\" title=\"{$escaped}\">"
                    : $escaped;

                return $self->makeHolder($result);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
            function ($matches) use ($self) {
                $escaped = $self->parseInline(
                    $self->escapeBracket($matches[1]), '', false, false
                );
                $url = $self->escapeBracket($matches[2]);
                $url = $self->cleanUrl($url);

                return $self->makeHolder("<a href=\"{$url}\">{$escaped}</a>");
            },
            $text
        );

        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self) {
                $escaped = $self->parseInline(
                    $self->escapeBracket($matches[1]), '', false
                );
                $result = isset($self->_definitions[$matches[2]]) ?
                    "<a href=\"{$self->_definitions[$matches[2]]}\">{$escaped}</a>"
                    : $escaped;

                return $self->makeHolder($result);
            },
            $text
        );

        // strong and em and some fuck
        $text = $this->parseInlineCallback($text);
        $text = preg_replace(
            "/<([_a-z0-9-\.\+]+@[^@]+\.[a-z]{2,})>/i",
            "<a href=\"mailto:\\1\">\\1</a>",
            $text
        );

        // autolink url
        if ($enableAutoLink) {
            $text = preg_replace_callback(
                "/(^|[^\"])((https?):[\p{L}_0-9-\.\/%#!@\?\+=~\|\,&\(\)]+)($|[^\"])/iu",
                function ($matches) use ($self) {
                    $link = $self->call('parseLink', $matches[2]);

                    return "{$matches[1]}<a href=\"{$matches[2]}\">{$link}</a>{$matches[4]}";
                },
                $text
            );
        }

        $text = $this->call('afterParseInlineBeforeRelease', $text);
        $text = $this->releaseHolder($text, $clearHolders);

        $text = $this->call('afterParseInline', $text);

        return $text;
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public function parseInlineCallback($text)
    {
        $self = $this;

        $text = preg_replace_callback(
            "/(\*{3})(.+?)\\1/",
            function ($matches) use ($self) {
                return '<strong><em>'.
                    $self->parseInlineCallback($matches[2]).
                    '</em></strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*{2})(.+?)\\1/",
            function ($matches) use ($self) {
                return '<strong>'.
                    $self->parseInlineCallback($matches[2]).
                    '</strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*)(.+?)\\1/",
            function ($matches) use ($self) {
                return '<em>'.
                    $self->parseInlineCallback($matches[2]).
                    '</em>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{3})(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return $matches[1].'<strong><em>'.
                    $self->parseInlineCallback($matches[3]).
                    '</em></strong>'.$matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{2})(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return $matches[1].'<strong>'.
                    $self->parseInlineCallback($matches[3]).
                    '</strong>'.$matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_)(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return $matches[1].'<em>'.
                    $self->parseInlineCallback($matches[3]).
                    '</em>'.$matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(~{2})(.+?)\\1/",
            function ($matches) use ($self) {
                return '<del>'.
                    $self->parseInlineCallback($matches[2]).
                    '</del>';
            },
            $text
        );

        return $text;
    }

    /**
     * parseBlock
     *
     * @param string $text
     * @param array $lines
     *
     * @return array
     */
    private function parseBlock($text, &$lines)
    {
        $lines = explode("\n", $text);
        $this->_blocks = array();
        $this->_current = 'normal';
        $this->_pos = -1;
        $special = implode("|", array_keys($this->_specialWhiteList));
        $emptyCount = 0;

        // analyze by line
        foreach ($lines as $key => $line) {
            $block = $this->getBlock();

            // code block is special
            if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line,
                $matches)
            ) {
                if ($this->isBlock('code')) {
                    $isAfterList = $block[3][2];

                    if ($isAfterList) {
                        $this->combineBlock()
                            ->setBlock($key);
                    } else {
                        $this->setBlock($key)
                            ->endBlock();
                    }
                } else {
                    $isAfterList = false;

                    if ($this->isBlock('list')) {
                        $space = $block[3];

                        $isAfterList = ($space > 0
                                && strlen($matches[1]) >= $space)
                            || strlen($matches[1]) > $space;
                    }

                    $this->startBlock('code', $key, array(
                        $matches[1],
                        $matches[3],
                        $isAfterList,
                    ));
                }

                continue;
            } else {
                if ($this->isBlock('code')) {
                    $this->setBlock($key);
                    continue;
                }
            }

            // super html mode
            if ($this->_html) {
                if (preg_match("/^(\s*)!!!(\s*)$/", $line, $matches)) {
                    if ($this->isBlock('shtml')) {
                        $this->setBlock($key)->endBlock();
                    } else {
                        $this->startBlock('shtml', $key);
                    }

                    continue;
                } else {
                    if ($this->isBlock('shtml')) {
                        $this->setBlock($key);
                        continue;
                    }
                }
            }

            // mathjax mode
            if (preg_match("/^(\s*)\\$\\$(\s*)$/", $line, $matches)) {
                if ($this->isBlock('math')) {
                    $this->setBlock($key)->endBlock();
                } else {
                    $this->startBlock('math', $key);
                }

                continue;
            } else {
                if ($this->isBlock('math')) {
                    $this->setBlock($key);
                    continue;
                }
            }

            // html block is special too
            if (preg_match("/^\s*<({$special})(\s+[^>]*)?>/i", $line,
                $matches)
            ) {
                $tag = strtolower($matches[1]);
                if (!$this->isBlock('html', $tag) && !$this->isBlock('pre')) {
                    $this->startBlock('html', $key, $tag);
                }

                continue;
            } else {
                if (preg_match("/<\/({$special})>\s*$/i", $line, $matches)) {
                    $tag = strtolower($matches[1]);

                    if ($this->isBlock('html', $tag)) {
                        $this->setBlock($key)
                            ->endBlock();
                    }

                    continue;
                } else {
                    if ($this->isBlock('html')) {
                        $this->setBlock($key);
                        continue;
                    }
                }
            }

            switch (true) {
                // pre block
                case preg_match("/^ {4}/", $line):
                    $emptyCount = 0;

                    if ($this->isBlock('pre') || $this->isBlock('list')) {
                        $this->setBlock($key);
                    } else {
                        if ($this->isBlock('normal')) {
                            $this->startBlock('pre', $key);
                        }
                    }
                    break;

                // list
                case preg_match("/^(\s*)((?:[0-9a-z]+\.)|\-|\+|\*)\s+/", $line,
                    $matches):
                    $space = strlen($matches[1]);
                    $emptyCount = 0;

                    // opened
                    if ($this->isBlock('list')) {
                        $this->setBlock($key, $space);
                    } else {
                        $this->startBlock('list', $key, $space);
                    }
                    break;

                // footnote
                case preg_match("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", $line,
                    $matches):
                    $space = strlen($matches[0]) - 1;
                    $this->startBlock('footnote', $key, array(
                        $space,
                        $matches[1],
                    ));
                    break;

                // definition
                case preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/",
                    $line, $matches):
                    $this->_definitions[$matches[1]]
                        = $this->cleanUrl($matches[2]);
                    $this->startBlock('definition', $key)
                        ->endBlock();
                    break;

                // block quote
                case preg_match("/^\s*>/", $line):
                    if ($this->isBlock('quote')) {
                        $this->setBlock($key);
                    } else {
                        $this->startBlock('quote', $key);
                    }
                    break;

                // table
                case preg_match("/^((?:(?:(?:[ :]*\-[ :]*)+(?:\||\+))|(?:(?:\||\+)(?:[ :]*\-[ :]*)+)|(?:(?:[ :]*\-[ :]*)+(?:\||\+)(?:[ :]*\-[ :]*)+))+)$/",
                    $line, $matches):
                    if ($this->isBlock('table')) {
                        $block[3][0][] = $block[3][2];
                        $block[3][2]++;
                        $this->setBlock($key, $block[3]);
                    } else {
                        $head = 0;

                        if (empty($block) || $block[0] != 'normal'
                            || preg_match("/^\s*$/", $lines[$block[2]])
                        ) {
                            $this->startBlock('table', $key);
                        } else {
                            $head = 1;
                            $this->backBlock(1, 'table');
                        }

                        if ($matches[1][0] == '|') {
                            $matches[1] = substr($matches[1], 1);

                            if ($matches[1][strlen($matches[1]) - 1] == '|') {
                                $matches[1] = substr($matches[1], 0, -1);
                            }
                        }

                        $rows = preg_split("/(\+|\|)/", $matches[1]);
                        $aligns = array();
                        foreach ($rows as $row) {
                            $align = 'none';

                            if (preg_match("/^\s*(:?)\-+(:?)\s*$/", $row,
                                $matches)
                            ) {
                                if (!empty($matches[1])
                                    && !empty($matches[2])
                                ) {
                                    $align = 'center';
                                } else {
                                    if (!empty($matches[1])) {
                                        $align = 'left';
                                    } else {
                                        if (!empty($matches[2])) {
                                            $align = 'right';
                                        }
                                    }
                                }
                            }

                            $aligns[] = $align;
                        }

                        $this->setBlock($key,
                            array(array($head), $aligns, $head + 1));
                    }
                    break;

                // single heading
                case preg_match("/^(#+)(.*)$/", $line, $matches):
                    $num = min(strlen($matches[1]), 6);
                    $this->startBlock('sh', $key, $num)
                        ->endBlock();
                    break;

                // multi heading
                case preg_match("/^\s*((=|-){2,})\s*$/", $line, $matches)
                    && ($block && $block[0] == "normal"
                        && !preg_match("/^\s*$/",
                            $lines[$block[2]])):    // check if last line isn't empty
                    if ($this->isBlock('normal')) {
                        $this->backBlock(1, 'mh', $matches[1][0] == '=' ? 1 : 2)
                            ->setBlock($key)
                            ->endBlock();
                    } else {
                        $this->startBlock('normal', $key);
                    }
                    break;

                // hr
                case preg_match("/^[-\*]{3,}\s*$/", $line):
                    $this->startBlock('hr', $key)
                        ->endBlock();
                    break;

                // normal
                default:
                    if ($this->isBlock('list')) {
                        if (preg_match("/^(\s*)/", $line)) { // empty line
                            if ($emptyCount > 0) {
                                $this->startBlock('normal', $key);
                            } else {
                                $this->setBlock($key);
                            }

                            $emptyCount++;
                        } else {
                            if ($emptyCount == 0) {
                                $this->setBlock($key);
                            } else {
                                $this->startBlock('normal', $key);
                            }
                        }
                    } else {
                        if ($this->isBlock('footnote')) {
                            preg_match("/^(\s*)/", $line, $matches);
                            if (strlen($matches[1]) >= $block[3][0]) {
                                $this->setBlock($key);
                            } else {
                                $this->startBlock('normal', $key);
                            }
                        } else {
                            if ($this->isBlock('table')) {
                                if (false !== strpos($line, '|')) {
                                    $block[3][2]++;
                                    $this->setBlock($key, $block[3]);
                                } else {
                                    $this->startBlock('normal', $key);
                                }
                            } else {
                                if ($this->isBlock('pre')) {
                                    if (preg_match("/^\s*$/", $line)) {
                                        if ($emptyCount > 0) {
                                            $this->startBlock('normal', $key);
                                        } else {
                                            $this->setBlock($key);
                                        }

                                        $emptyCount++;
                                    } else {
                                        $this->startBlock('normal', $key);
                                    }
                                } else {
                                    if ($this->isBlock('quote')) {
                                        if (preg_match("/^(\s*)/",
                                            $line)
                                        ) { // empty line
                                            if ($emptyCount > 0) {
                                                $this->startBlock('normal',
                                                    $key);
                                            } else {
                                                $this->setBlock($key);
                                            }

                                            $emptyCount++;
                                        } else {
                                            if ($emptyCount == 0) {
                                                $this->setBlock($key);
                                            } else {
                                                $this->startBlock('normal',
                                                    $key);
                                            }
                                        }
                                    } else {
                                        if (empty($block)
                                            || $block[0] != 'normal'
                                        ) {
                                            $this->startBlock('normal', $key);
                                        } else {
                                            $this->setBlock($key);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        return $this->optimizeBlocks($this->_blocks, $lines);
    }

    /**
     * @param array $blocks
     * @param array $lines
     *
     * @return array
     */
    private function optimizeBlocks(array $blocks, array $lines)
    {
        $blocks = $this->call('beforeOptimizeBlocks', $blocks, $lines);

        $key = 0;
        while (isset($blocks[$key])) {
            $moved = false;

            $block = &$blocks[$key];
            $prevBlock = isset($blocks[$key - 1]) ? $blocks[$key - 1] : null;
            $nextBlock = isset($blocks[$key + 1]) ? $blocks[$key + 1] : null;

            list ($type, $from, $to) = $block;

            if ('pre' == $type) {
                $isEmpty = array_reduce($lines, function ($result, $line) {
                    return preg_match("/^\s*$/", $line) && $result;
                }, true);

                if ($isEmpty) {
                    $block[0] = $type = 'normal';
                }
            }

            if ('normal' == $type) {
                // combine two blocks
                $types = array('list', 'quote');

                if ($from == $to && preg_match("/^\s*$/", $lines[$from])
                    && !empty($prevBlock)
                    && !empty($nextBlock)
                ) {
                    if ($prevBlock[0] == $nextBlock[0]
                        && in_array($prevBlock[0], $types)
                    ) {
                        // combine 3 blocks
                        $blocks[$key - 1] = array(
                            $prevBlock[0],
                            $prevBlock[1],
                            $nextBlock[2],
                            null,
                        );
                        array_splice($blocks, $key, 2);

                        // do not move
                        $moved = true;
                    }
                }
            }

            if (!$moved) {
                $key++;
            }
        }

        return $this->call('afterOptimizeBlocks', $blocks, $lines);
    }

    /**
     * parseCode
     *
     * @param array $lines
     * @param array $parts
     *
     * @return string
     */
    private function parseCode(array $lines, array $parts)
    {
        list ($blank, $lang) = $parts;
        $lang = trim($lang);
        $count = strlen($blank);

        if (!preg_match("/^[_a-z0-9-\+\#\:\.]+$/i", $lang)) {
            $lang = null;
        } else {
            $parts = explode(':', $lang);
            if (count($parts) > 1) {
                list ($lang, $rel) = $parts;
                $lang = trim($lang);
                $rel = trim($rel);
            }
        }

        $lines = array_map(function ($line) use ($count) {
            return preg_replace("/^[ ]{{$count}}/", '', $line);
        }, array_slice($lines, 1, -1));
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str)
            ? ''
            :
            '<pre><code'.(!empty($lang) ? " class=\"{$lang}\"" : '')
            .(!empty($rel) ? " rel=\"{$rel}\"" : '').'>'
            .htmlspecialchars($str).'</code></pre>';
    }

    /**
     * parsePre
     *
     * @param array $lines
     *
     * @return string
     */
    private function parsePre(array $lines)
    {
        foreach ($lines as &$line) {
            $line = htmlspecialchars(substr($line, 4));
        }
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? ''
            : '<pre><code>'.$str.'</code></pre>';
    }

    /**
     * parseShtml
     *
     * @param array $lines
     *
     * @return string
     */
    private function parseShtml(array $lines)
    {
        return trim(implode("\n", array_slice($lines, 1, -1)));
    }

    /**
     * parseMath
     *
     * @param array $lines
     *
     * @return string
     */
    private function parseMath(array $lines)
    {
        return '<p>'.htmlspecialchars(implode("\n", $lines)).'</p>';
    }

    /**
     * parseSh
     *
     * @param array $lines
     * @param int $num
     *
     * @return string
     */
    private function parseSh(array $lines, $num)
    {
        $line = $this->parseInline(trim($lines[0], '# '));

        return preg_match("/^\s*$/", $line) ? '' : "<h{$num}>{$line}</h{$num}>";
    }

    /**
     * parseMh
     *
     * @param array $lines
     * @param int $num
     *
     * @return string
     */
    private function parseMh(array $lines, $num)
    {
        return $this->parseSh($lines, $num);
    }

    /**
     * parseQuote
     *
     * @param array $lines
     *
     * @return string
     */
    private function parseQuote(array $lines)
    {
        foreach ($lines as &$line) {
            $line = preg_replace("/^\s*> ?/", '', $line);
        }
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? ''
            : '<blockquote>'.$this->parse($str).'</blockquote>';
    }

    /**
     * parseList
     *
     * @param array $lines
     *
     * @return string
     */
    private function parseList(array $lines)
    {
        $html = '';
        $minSpace = 99999;
        $rows = array();

        // count levels
        foreach ($lines as $key => $line) {
            if (preg_match("/^(\s*)((?:[0-9a-z]+\.?)|\-|\+|\*)(\s+)(.*)$/",
                $line, $matches)
            ) {
                $space = strlen($matches[1]);
                $type = false !== strpos('+-*', $matches[2]) ? 'ul' : 'ol';
                $minSpace = min($space, $minSpace);

                $rows[] = array($space, $type, $line, $matches[4]);
            } else {
                $rows[] = $line;
            }
        }

        $found = false;
        $secondMinSpace = 99999;
        foreach ($rows as $row) {
            if (is_array($row) && $row[0] != $minSpace) {
                $secondMinSpace = min($secondMinSpace, $row[0]);
                $found = true;
            }
        }
        $secondMinSpace = $found ? $secondMinSpace : $minSpace;

        $lastType = '';
        $leftLines = array();

        foreach ($rows as $row) {
            if (is_array($row)) {
                list ($space, $type, $line, $text) = $row;

                if ($space != $minSpace) {
                    $leftLines[] = preg_replace("/^\s{".$secondMinSpace."}/",
                        '', $line);
                } else {
                    if (!empty($leftLines)) {
                        $html .= "<li>".$this->parse(implode("\n", $leftLines),
                                true)."</li>";
                    }

                    if ($lastType != $type) {
                        if (!empty($lastType)) {
                            $html .= "</{$lastType}>";
                        }

                        $html .= "<{$type}>";
                    }

                    $leftLines = array($text);
                    $lastType = $type;
                }
            } else {
                $leftLines[] = preg_replace("/^\s{".$secondMinSpace."}/", '',
                    $row);
            }
        }

        if (!empty($leftLines)) {
            $html .= "<li>".$this->parse(implode("\n", $leftLines), true)
                ."</li></{$lastType}>";
        }

        return $html;
    }

    /**
     * @param array $lines
     * @param array $value
     *
     * @return string
     */
    private function parseTable(array $lines, array $value)
    {
        list ($ignores, $aligns) = $value;
        $head = count($ignores) > 0 && array_sum($ignores) > 0;

        $html = '<table>';
        $body = $head ? null : true;
        $output = false;

        foreach ($lines as $key => $line) {
            if (in_array($key, $ignores)) {
                if ($head && $output) {
                    $head = false;
                    $body = true;
                }
                continue;
            }

            $line = trim($line);
            $output = true;

            if ($line[0] == '|') {
                $line = substr($line, 1);

                if ($line[strlen($line) - 1] == '|') {
                    $line = substr($line, 0, -1);
                }
            }


            $rows = array_map(function ($row) {
                if (preg_match("/^\s+$/", $row)) {
                    return ' ';
                } else {
                    return trim($row);
                }
            }, explode('|', $line));
            $columns = array();
            $last = -1;

            foreach ($rows as $row) {
                if (strlen($row) > 0) {
                    $last++;
                    $columns[$last] = array(
                        isset($columns[$last]) ? $columns[$last][0] + 1 : 1,
                        $row,
                    );
                } else {
                    if (isset($columns[$last])) {
                        $columns[$last][0]++;
                    } else {
                        $columns[0] = array(1, $row);
                    }
                }
            }

            if ($head) {
                $html .= '<thead>';
            } else {
                if ($body) {
                    $html .= '<tbody>';
                }
            }

            $html .= '<tr>';

            foreach ($columns as $key => $column) {
                list ($num, $text) = $column;
                $tag = $head ? 'th' : 'td';

                $html .= "<{$tag}";
                if ($num > 1) {
                    $html .= " colspan=\"{$num}\"";
                }

                if (isset($aligns[$key]) && $aligns[$key] != 'none') {
                    $html .= " align=\"{$aligns[$key]}\"";
                }

                $html .= '>'.$this->parseInline($text)."</{$tag}>";
            }

            $html .= '</tr>';

            if ($head) {
                $html .= '</thead>';
            } else {
                if ($body) {
                    $body = false;
                }
            }
        }

        if ($body !== null) {
            $html .= '</tbody>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * parseHr
     *
     * @return string
     */
    private function parseHr()
    {
        return '<hr>';
    }

    /**
     * parseNormal
     *
     * @param array $lines
     *
     * @return string
     */
    private function parseNormal(array $lines)
    {
        foreach ($lines as &$line) {
            $line = $this->parseInline($line);
        }

        $str = trim(implode("\n", $lines));
        $str = preg_replace("/(\n\s*){2,}/", "</p><p>", $str);
        $str = preg_replace("/\n/", "<br>", $str);

        return preg_match("/^\s*$/", $str) ? '' : "<p>{$str}</p>";
    }

    /**
     * parseFootnote
     *
     * @param array $lines
     * @param array $value
     *
     * @return string
     */
    private function parseFootnote(array $lines, array $value)
    {
        list($space, $note) = $value;
        $index = array_search($note, $this->_footnotes);

        if (false !== $index) {
            $lines[0] = preg_replace("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", '',
                $lines[0]);
            $this->_footnotes[$index] = $lines;
        }

        return '';
    }

    /**
     * parseDefine
     *
     * @return string
     */
    private function parseDefinition()
    {
        return '';
    }

    /**
     * parseHtml
     *
     * @param array $lines
     * @param string $type
     *
     * @return string
     */
    private function parseHtml(array $lines, $type)
    {
        foreach ($lines as &$line) {
            $line = $this->parseInline($line,
                isset($this->_specialWhiteList[$type])
                    ? $this->_specialWhiteList[$type] : '');
        }

        return implode("\n", $lines);
    }

    /**
     * @param $url
     *
     * @return string
     */
    public function cleanUrl($url)
    {
        if (preg_match("/^\s*((http|https|ftp|mailto):[x80-xff_a-z0-9-\.\/%#!@\?\+=~\|\,&\(\)]+)/i",
            $url, $matches)
        ) {
            return $matches[1];
        } else {
            if (preg_match("/^\s*([x80-xff_a-z0-9-\.\/%#!@\?\+=~\|\,&]+)/i",
                $url, $matches)
            ) {
                return $matches[1];
            } else {
                return '#';
            }
        }
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    public function escapeBracket($str)
    {
        return str_replace(
            array('\[', '\]', '\(', '\)'), array('[', ']', '(', ')'), $str
        );
    }

    /**
     * startBlock
     *
     * @param mixed $type
     * @param mixed $start
     * @param mixed $value
     *
     * @return $this
     */
    private function startBlock($type, $start, $value = null)
    {
        $this->_pos++;
        $this->_current = $type;

        $this->_blocks[$this->_pos] = array($type, $start, $start, $value);

        return $this;
    }

    /**
     * endBlock
     *
     * @return $this
     */
    private function endBlock()
    {
        $this->_current = 'normal';

        return $this;
    }

    /**
     * isBlock
     *
     * @param mixed $type
     * @param mixed $value
     *
     * @return bool
     */
    private function isBlock($type, $value = null)
    {
        return $this->_current == $type
            && (null === $value ? true
                : $this->_blocks[$this->_pos][3] == $value);
    }

    /**
     * getBlock
     *
     * @return array
     */
    private function getBlock()
    {
        return isset($this->_blocks[$this->_pos]) ? $this->_blocks[$this->_pos]
            : null;
    }

    /**
     * setBlock
     *
     * @param mixed $to
     * @param mixed $value
     *
     * @return $this
     */
    private function setBlock($to = null, $value = null)
    {
        if (null !== $to) {
            $this->_blocks[$this->_pos][2] = $to;
        }

        if (null !== $value) {
            $this->_blocks[$this->_pos][3] = $value;
        }

        return $this;
    }

    /**
     * backBlock
     *
     * @param mixed $step
     * @param mixed $type
     * @param mixed $value
     *
     * @return $this
     */
    private function backBlock($step, $type, $value = null)
    {
        if ($this->_pos < 0) {
            return $this->startBlock($type, 0, $value);
        }

        $last = $this->_blocks[$this->_pos][2];
        $this->_blocks[$this->_pos][2] = $last - $step;

        if ($this->_blocks[$this->_pos][1] <= $this->_blocks[$this->_pos][2]) {
            $this->_pos++;
        }

        $this->_current = $type;
        $this->_blocks[$this->_pos] = array(
            $type,
            $last - $step + 1,
            $last,
            $value,
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function combineBlock()
    {
        if ($this->_pos < 1) {
            return $this;
        }

        $prev = $this->_blocks[$this->_pos - 1];
        $current = $this->_blocks[$this->_pos];

        $prev[2] = $current[2];
        $this->_blocks[$this->_pos - 1] = $prev;
        $this->_current = $prev[0];
        unset($this->_blocks[$this->_pos]);
        $this->_pos--;

        return $this;
    }
}

