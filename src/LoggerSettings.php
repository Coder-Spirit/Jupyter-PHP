<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2016 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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