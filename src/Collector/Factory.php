<?php


namespace Collector;


use Collector\IO\IOInterface;
use Collector\Util\RemoteFilesystem;

/** TODO: Temporary mock implementation. To be implemented ... */
class Factory
{
    /**
     * @param  IOInterface      $io      IO instance
     * @param  Config           $config  Config instance
     * @param  array            $options Array of options passed directly to RemoteFilesystem constructor
     * @return RemoteFilesystem
     */
    public static function createRemoteFilesystem(IOInterface $io, Config $config = null, $options = array())
    {
        return new RemoteFilesystem();
    }
}