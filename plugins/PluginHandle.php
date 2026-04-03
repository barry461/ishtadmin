<?php

namespace plugins;

/*
 * @method Widget_Abstract_Contents:contentEx()
 * @method Widget_Abstract_Contents:excerptEx()
 * @method admin/write-post.php:bottom()
 * @method admin/write-page.php:bottom()
 * @method Widget_Contents_Post_Edit:write()
 * @method Widget_Contents_Page_Edit:write()
 * @method Widget_Abstract_Contents:content()
 * @method Widget_Abstract_Contents:excerpt()
 * @method Widget_Archive:header()
 * @method Widget_Archive:footer()
 * @method Widget_Upload:upload()
 * @method Widget_Feedback:comment()
 * @method Mirages_Plugin:content2()
 * @method Widget_Contents_Page_Edit:finishPublish()
 * @method Widget_Contents_Post_Edit:finishPublish()
 * @method Widget_Archive:handleInit()
 * @method Widget_Archive:categoryHandle()
 * @method index.php:begin()
 * @method index.php:end()
 * @method Mirages_Widget_Comments_Archive:listComments()
 * @method Widget_Comments_Edit:mark()
 * @method Widget_Comments_Edit:finishDelete()
 * @method Widget_Archive:beforeRender()
 * @method admin/menu.php:navBar()
 */

/**
 * * @method contentEx()
 * * @method excerptEx()
 * * @method bottom()
 * * @method write()
 * * @method content()
 * * @method excerpt()
 * * @method header()
 * * @method footer()
 * * @method upload()
 * * @method comment()
 * * @method content2()
 * * @method finishPublish()
 * * @method handleInit()
 * * @method categoryHandle()
 * * @method begin()
 * * @method end()
 * * @method listComments()
 * * @method mark()
 * * @method finishDelete()
 * * @method beforeRender()
 * * @method navBar()
 */
class PluginHandle
{
    protected static $options;
    protected $handle;

    public function __construct($handle)
    {
        $this->options();
        $this->handle = $handle;
    }

    protected function options()
    {
        if (self::$options === null) {
            self::$options = options('plugins')['handles'] ?? [];
        }
    }

    public function __call($name, $arguments)
    {

      
        $key = sprintf("%s:%s", $this->handle, $name);
        $handles = self::$options[$key] ?? [];
        $result = [];
        foreach ($handles as $handle) {
            list($class, $method) = $handle;
            if (str_contains($class, '_Plugin')) {
                $class = "\\plugins\\".str_replace("_", "\\", $class);
            }
            if (!class_exists($class)){
                continue;
            }
            $tmp = call_user_func_array([$class, $method], $arguments);
            if ($tmp !== null){
                $result[] = $tmp;
            }
        }
        return $result;
    }

}