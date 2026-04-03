<?php

namespace tools;

/**
 * 过滤html，只保留白名单里的标签和属性
 */
class LibSafelist
{
    protected $tags = [];


    /**
     * 添加白名单
     * @param string $tag 白名单的标签
     * @param array $attr 白名单的标签属性
     *
     * @return $this
     */
    public function addTags(string $tag, array $attr = []): self
    {
        $tag = strtolower($tag);
        $this->tags[$tag] = array_map('strtolower',$attr);
        return $this;
    }

    /**
     * 转换
     * @param $html
     *
     * @return string
     */
    public function convert($html): string
    {
        $str = str_replace(['{%', '%}'], ['<', '>'], $html);
        $str = $this->filterTags($str);
        $str = $this->filterSingleTag($str);
        $str = strip_tags($str);
        $str = str_replace(['<', '>'], ['&lt;', '&gt;'], $str);
        return str_replace(['{%', '%}'], ['<', '>'], $str);
    }


    /**
     * 过滤 成对出现的标签
     * @param $html
     *
     * @return array|string|string[]|null
     */
    private function filterTags($html)
    {
        return preg_replace_callback("#<([a-z]+)\s*([^>]*)>(.*)</\\1>#i",
            function ($var) {
                list($o, $tag, $attr, $content) = $var;
                $tag = strtolower($tag);
                if (!isset($this->tags[$tag])){
                    return '';
                }
                $attr = $this->filterAttr($attr , $this->tags[$tag] ?? '');
                return "{%$tag$attr%}".$this->filterTags($content)
                    ."{%/$tag%}";
            }, $html);
    }

    /**
     * 过滤单独标签
     * @param $html
     *
     * @return array|string|string[]|null
     */
    private function filterSingleTag($html)
    {
        return preg_replace_callback("#<(br|ht|img|input|param|meta|link|css+)\s*([^>]*)(/?)>#i",
            function ($var) {
                list($o, $tag, $attr) = $var;
                if (!isset($this->tags[$tag])){
                    return '';
                }
                $attr = $this->filterAttr($attr, $this->tags[$tag] ?? '');
                return "{%$tag$attr/%}";
            }, $html);
    }


    private function filterAttr($attrStr , $whiteAttr): string
    {
        $attrAry = $this->parseAttr($attrStr);
        if (!is_array($whiteAttr)) {
            return '';
        }
        $newAttrStr = '';
        foreach ($attrAry as $attrName => $attrValue) {
            if (in_array($attrName, $whiteAttr)) {
                if ($attrValue){
                    $attrValue = str_replace('"', "'", $attrValue);
                    $newAttrStr .= " $attrName=\"$attrValue\" ";
                }else{
                    $newAttrStr .= " $attrName ";
                }
            }
        }
        $newAttrStr = trim($newAttrStr);
        if (!empty($newAttrStr)) {
            $newAttrStr = ' '.$newAttrStr;
        }
        return $newAttrStr;
    }

    /**
     * 解析属性
     * @param string $attr
     *
     * @return array
     */
    private function parseAttr(string $attr): array
    {
        if (empty($attr)) {
            return [];
        }
        $attr = trim($attr);
        $len = strlen($attr);
        $tmp = '';
        $values = $keys = [];
        $pos = -1;
        $outChar = '';
        $char = null;
        for ($i = 0; $i < $len; $i++) {
            $prevChar = $char;
            $char = $attr[$i];
            if ($outChar === '') {
                if ($char === '=') {
                    if ($tmp) {
                        $pos++;
                        $keys[$pos] = $tmp;
                        $values[$pos] = null;
                        $tmp = '';
                    }
                    continue;
                } elseif (trim($char) == "") {
                    continue;
                } elseif ($char == "'" || $char == '"' || $prevChar == '=') {
                    $outChar = $char;
                    continue;
                }
            } elseif ($char == $outChar && $prevChar !== '\\') {
                $outChar = '';
                $values[$pos] = $tmp;
                $tmp = '';
                continue;
            }
            $tmp .= $char;
        }
        $attr = [];
        foreach ($keys as $k => $key) {
            $key = strtolower($key);
            $attr[$key] = $values[$k];
        }

        return $attr;
    }
}
