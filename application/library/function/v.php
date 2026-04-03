<?php

if (!function_exists('v_cls')){
    /**
     * 模拟 vue的 :class
     * @param  array  $map
     * @param  string  $prepend
     *
     * @return string
     */
    function v_clz(array $map, string $prepend = ''): string
    {
        $classList = array_keys(array_filter($map));
        if ($prepend) {
            $classList[] = $prepend;
        }
        $classList = array_filter($classList); // 防止空项
        if (empty($classList)) {
            return '';
        }
        return htmlspecialchars(implode(' ', $classList));
    }
}

if (!function_exists('post_title_class')){
    function post_title_class($title): string
    {
        $short = 8;
        $long = 25;
        if (preg_match('/[a-zA-Z0-9\-\s\|\(\)\[\]\{\}\/\.\,\?\!]+/i', $title)) {
            $short = 18;
            $long = 60;
        }
        if (mb_strlen($title) <= $short) {
            return " post-title-short";
        } else if (mb_strlen($title) >= $long) {
            return " post-title-long";
        }
        return "";
    }
}


if (!function_exists('v_import')){

}

if (!function_exists('v_script')){

}