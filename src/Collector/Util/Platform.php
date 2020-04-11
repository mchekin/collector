<?php


namespace Collector\Util;

/** TODO: Temporary mock implementation. To be implemented ... */
class Platform
{
    /**
     * @return bool Whether the host machine is running a Windows OS
     */
    public static function isWindows()
    {
        return defined('PHP_WINDOWS_VERSION_BUILD');
    }
}