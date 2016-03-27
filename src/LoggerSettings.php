<?php


namespace Litipk\JupyterPHP;


use Monolog\Logger;


final class LoggerSettings
{
    /**
     * @return int
     */
    public static function getCrossFingersLevel()
    {
        global $argv;
        if (!isset($argv) || empty($argv)) {
            $argv = $_SERVER['argv'];
        }

        if (is_array($argv) && count($argv) > 2) {
            return ('debug' === trim(strtolower($argv[2]))) ? Logger::DEBUG : Logger::WARNING;
        } else {
            return Logger::WARNING;
        }
    }
}