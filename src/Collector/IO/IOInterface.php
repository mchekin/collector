<?php


namespace Collector\IO;

interface IOInterface
{
    const QUIET = 1;
    const NORMAL = 2;
    const VERBOSE = 4;
    const VERY_VERBOSE = 8;
    const DEBUG = 16;

    /**
     * Set the authentication information for the repository.
     *
     * @param string $repositoryName The unique name of repository
     * @param string $username       The username
     * @param string $password       The password
     */
    public function setAuthentication($repositoryName, $username, $password = null);


    /**
     * Writes a message to the error output.
     *
     * @param string|array $messages  The message as an array of lines or a single string
     * @param bool         $newline   Whether to add a newline or not
     * @param int          $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function writeError($messages, $newline = true, $verbosity = self::NORMAL);

    /**
     * Asks a question to the user and hide the answer.
     *
     * @param string $question The question to ask
     *
     * @return string The answer
     */
    public function askAndHideAnswer($question);
}