<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Litipk\JupyterPHP\System;


abstract class System
{
    const OS_UNKNOWN = 0;
    const OS_LINUX   = 1;
    const OS_OSX     = 2;
    const OS_BSD     = 3;
    const OS_WIN     = 4;


    public static function getSystem(): System
    {
        $phpOs = self::guessOperativeSystem();

        if (self::OS_LINUX === $phpOs) {
            return new LinuxSystem();
        } elseif (self::OS_BSD === $phpOs) {
            return new BsdSystem();
        } elseif (self::OS_OSX === $phpOs) {
            return new MacSystem();
        } elseif (self::OS_WIN === $phpOs) {
            return new WindowsSystem();
        } else {
            throw new \RuntimeException('This platform is unknown for the installer');
        }
    }

    public abstract function getOperativeSystem(): int;

    public abstract function getCurrentUser(): string;

    public abstract function getCurrentUserHome(): string;

    public abstract function checkIfCommandExists(string $cmdName): bool;

    public abstract function getAppDataDirectory(): string;

    /**
     * Returns true if the path is a "valid" path and is writable (even if the complete path does not yet exist).
     * @param string $path
     * @return bool
     */
    public abstract function validatePath(string $path): bool;

    /**
     * Ensures that the specified path exists.
     * @param string $path
     * @return string The "absolute path" version of $path.
     */
    public abstract function ensurePath(string $path): string;

    protected abstract function isAbsolutePath(string $path): bool;

    protected abstract function getAbsolutePath(string $path): string;

    private static function guessOperativeSystem(): int
    {
        $phpOS = strtolower(PHP_OS);

        if ('linux' === $phpOS) {
            return self::OS_LINUX;
        } elseif ('darwin' === $phpOS) {
            return self::OS_OSX;
        } elseif (in_array($phpOS, ['windows', 'winnt', 'win32'])) {
            return self::OS_WIN;
        } elseif (in_array($phpOS, ['freebsd', 'netbsd', 'openbsd'])) {
            return self::OS_BSD;
        } else {
            return self::OS_UNKNOWN;
        }
    }
}
