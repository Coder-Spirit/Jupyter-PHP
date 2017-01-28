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


abstract class UnixSystem extends System
{
    /**
     * @return string
     */
    public function getCurrentUser(): string
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $pwuData = posix_getpwuid(posix_geteuid());
            return $pwuData['name'];
        } elseif ($this->checkIfCommandExists('whoami')) {
            return exec('whoami');
        } else {
            throw new \RuntimeException('Unable to obtain the current username.');
        }
    }

    /**
     * @return string
     */
    public function getCurrentUserHome(): string
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $pwuData = posix_getpwuid(posix_geteuid());
            return $pwuData['dir'];
        } elseif (function_exists('getenv') && false !== getenv('HOME')) {
            return getenv('HOME');
        } else {
            throw new \RuntimeException('Unable to obtain the current user home directory.');
        }
    }

    /**
     * @param string $cmdName
     * @return boolean
     */
    public function checkIfCommandExists(string $cmdName): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $sysResponse = exec(
            'PATH='.getenv('PATH').'; '.
            "if command -v ".$cmdName." >/dev/null 2>&1; then echo \"true\"; else echo \"false\"; fi;"
        );

        return filter_var($sysResponse, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    public function getAppDataDirectory(): string
    {
        return $this->getCurrentUserHome().'/.jupyter-php';
    }

    /**
     * Returns true if the path is a "valid" path and is writable (even if the complete path does not yet exist).
     * @param string $path
     * @return boolean
     */
    public function validatePath(string $path): bool
    {
        $absPath = $this->getAbsolutePath($path);
        $absPathParts = preg_split('/\//', preg_replace('/(^\/|\/$)/', '', $absPath));
        $nSteps = count($absPathParts);

        $tmpPath = '';
        $prevReadable = false;
        $prevWritable = false;

        for ($i=0; $i<$nSteps; $i++) {
            $tmpPath .= '/' . $absPathParts[$i];

            if (file_exists($tmpPath)) {
                if (!is_dir($tmpPath)) {
                    if (is_link($tmpPath)) {
                        $linkPath = readlink($tmpPath);
                        if (false === $linkPath || !is_dir($linkPath)) {
                            return false;
                        }
                        $tmpPath = $linkPath;
                    } else {
                        return false;
                    }
                }

                $prevReadable = is_readable($tmpPath);
                $prevWritable = is_writable($tmpPath);
            } else {
                return ($prevReadable && $prevWritable);
            }
        }

        return true;
    }

    /**
     * @param string $path
     * @return string The "absolute path" version of $path.
     */
    public function ensurePath(string $path): string
    {
        $absPath = $this->getAbsolutePath($path);

        if (!file_exists($absPath) && false === mkdir($absPath, 0755, true)) {
            throw new \RuntimeException('Unable to create the specified directory ('.$absPath.').');
        }

        return $absPath;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function isAbsolutePath(string $path): bool
    {
        return (1 === preg_match('#^/#', $path));
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath(string $path): string
    {
        return $this->isAbsolutePath($path)
            ? $path
            : (getcwd() . DIRECTORY_SEPARATOR . $path);
    }
}
