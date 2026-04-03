<?php

namespace plugins;


class PluginUtils
{
    protected $options = null;

    public function __construct($handle)
    {
        $this->options = options('plugins');
    }

    public function handle($handle): PluginHandle
    {
        return new PluginHandle($this->options['handles'] ??[] , $handle);
    }

    public function pluginHandle($handle): PluginHandle
    {
       return $this->handle($handle);
    }


}