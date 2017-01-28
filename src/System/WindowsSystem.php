<?php


namespace Litipk\JupyterPHP\System;


final class WindowsSystem extends System
{

    /** @return integer */
    public function getOperativeSystem()
    {
        return self::OS_WIN;
    }

    /** @return string */
    public function getCurrentUser()
    {
        if (function_exists('getenv') && false !== getenv('username')) {
            return getenv('username');
        } else {
            throw new \RuntimeException('Unable to obtain the current username.');
        }
    }

    /** @return string */
    public function getCurrentUserHome()
    {
        if (function_exists('getenv') && false !== getenv('HOMEDRIVE') && false !== getenv('HOMEPATH')) {
            return getenv("HOMEDRIVE") . getenv("HOMEPATH");
        } else {
            throw new \RuntimeException('Unable to obtain the current user home directory.');
        }
    }

    /**
     * @param string $cmdName
     * @return boolean
     */
    public function checkIfCommandExists($cmdName)
    {
        if (!function_exists('exec')) {
            return false;
        }

        $sysResponse = exec("where $cmdName > nul 2>&1 && echo true");

        return filter_var($sysResponse, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /** @return string */
    public function getAppDataDirectory()
    {
        return $this->getCurrentUserHome() . '/.jupyter-php';
    }

    /**
     * Returns true if the path is a "valid" path and is writable (event if the complete path does not yet exist).
     * @param string $path
     * @return boolean
     */
    public function validatePath($path)
    {
        $absPath = $this->getAbsolutePath($path);
        $absPathParts = explode(DIRECTORY_SEPARATOR, $absPath);
        $nSteps = count($absPathParts);

        $tmpPath = $absPathParts[0];
        $prevReadable = false;
        $prevWritable = false;

        for ($i = 1; $i < $nSteps; $i++) {
            $tmpPath .= DIRECTORY_SEPARATOR . $absPathParts[$i];

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
    public function ensurePath($path)
    {
        $absPath = $this->getAbsolutePath($path);

        if (!file_exists($absPath) && false === mkdir($absPath, 0755, true)) {
            throw new \RuntimeException('Unable to create the specified directory (' . $absPath . ').');
        }

        return $absPath;
    }

    /**
     * @param string $path
     * @return boolean
     */
    protected function isAbsolutePath($path)
    {
        return preg_match('/^[a-z]\:/i', $path) === 1;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath($path)
    {
        $path = $this->isAbsolutePath($path) ? $path : (getcwd() . DIRECTORY_SEPARATOR . $path);

        // Normalise directory separators
        $path = preg_replace('/[\/\\\\]/u', DIRECTORY_SEPARATOR, $path);

        return $path;
    }
}
