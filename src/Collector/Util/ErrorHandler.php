<?php

namespace Collector\Util;

use Collector\IO\IOInterface;

/**
 * Convert PHP errors into exceptions
 */
class ErrorHandler
{
    /**
     * @var IOInterface
     */
    private static $io;

    /**
     * Register error handler.
     *
     * @param IOInterface|null $io
     */
    public static function register(IOInterface $io = null)
    {
        set_error_handler(array(__CLASS__, 'handle'));

        error_reporting(E_ALL | E_STRICT);

        self::$io = $io;
    }

    /**
     * Error handler
     *
     * @param int $level Level of the error raised
     * @param string $message Error message
     * @param string $file Filename that the error was raised in
     * @param int $line Line number the error was raised at
     *
     * @static
     * @throws \ErrorException
     * @return bool
     */
    public static function handle($level, $message, $file, $line)
    {
        // error code is not included in error_reporting
        if (!(error_reporting() & $level)) {
            return true;
        }

        if (filter_var(ini_get('xdebug.scream'), FILTER_VALIDATE_BOOLEAN)) {
            $message .= "\n\nWarning: You have xdebug.scream enabled, the warning above may be" .
                "\na legitimately suppressed error that you were not supposed to see.";
        }

        if ($level !== E_DEPRECATED && $level !== E_USER_DEPRECATED) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        if (self::$io) {

            self::$io->writeError('<warning>Deprecation Notice: ' . $message . ' in ' . $file . ':' . $line . '</warning>');

            if (self::$io->isVerbose()) {
                self::$io->writeError('<warning>Stack trace:</warning>');
                self::$io->writeError(self::filterBacktrace(debug_backtrace()));
            }
        }

        return true;
    }

    /**
     * @param array $debugBacktrace
     *
     * @return array
     */
    private static function filterBacktrace(array $debugBacktrace)
    {
        $entries = array_slice($debugBacktrace, 2);

        return array_filter(array_map(function ($entry) {

            if (isset($entry['line'], $entry['file'])) {
                return '<warning> ' . $entry['file'] . ':' . $entry['line'] . '</warning>';
            }

            return null;
        }, $entries));
    }
}