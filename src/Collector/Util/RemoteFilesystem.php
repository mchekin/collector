<?php


namespace Collector\Util;


/** TODO: Temporary mock implementation. To be implemented ... */
class RemoteFilesystem
{
    /**
     * Get the content.
     *
     * @param string $originUrl The origin URL
     * @param string $fileUrl   The file URL
     * @param bool   $progress  Display the progression
     * @param array  $options   Additional context options
     *
     * @return bool|string The content
     */
    public function getContents($originUrl, $fileUrl, $progress = true, $options = array())
    {
        return '{}';
    }
}