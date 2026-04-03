<?php

namespace plugins;

use MyArrayObject;

abstract class PluginInterface
{

    /**
     * 启用插件方法,如果启用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    abstract public static function activate();

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    abstract public static function deactivate();

    /**
     * 获取插件配置面板
     *
     * @static
     * @access public
     * @param $form 配置面板
     * @return void
     */
    abstract public static function config($form);

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param $form
     * @return void
     */
    abstract public static function personalConfig($form);

    public  static function options(): MyArrayObject
    {
        $class = static::class;
        $class = str_replace('\\' , '/' , $class);
        $class = dirname($class);
        $name = basename($class);
        $data = options("plugin:{$name}");
        return new MyArrayObject($data);
    }

}