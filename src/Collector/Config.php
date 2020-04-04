<?php


namespace Collector;


/** Temporary mock implementation */
class Config
{
    public function get($name)
    {
        return array();
    }

    public function removeConfigSetting($name)
    {
    }

    public function addConfigSetting($name, $value)
    {
    }

    public function getAuthConfigSourceName()
    {
        return '';
    }
}