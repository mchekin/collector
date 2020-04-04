<?php


namespace Collector\Util;


use Collector\IO\IOInterface;

/** Temporary mock implementation */
class ProcessExecutor
{

    public function __construct(IOInterface $io = null)
    {
    }

    /**
     * runs a process on the commandline
     *
     * @param  string $command the command to execute
     * @param  mixed  $output  the output will be written into this var if passed by ref
     *                         if a callable is passed it will be used as output handler
     * @param  string $cwd     the working directory
     * @return int             status code
     */
    public function execute($command, &$output = null, $cwd = null)
    {
        return 1;
    }
}