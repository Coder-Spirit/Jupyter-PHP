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
     * Returns true if the path is a "valid" path and is writable (event if the complete path does not yet exist).
     * @param string $path
     * @return boolean
     */
    public function validatePath($path)
    {
        // TODO: Implement validatePath() method.
    }

    /**
     * @param string $path
     * @return string The "absolute path" version of $path.
     */
    public function ensurePath($path)
    {
        // TODO: Implement ensurePath() method.
    }

    /**
     * @param string $path
     * @return boolean
     */
    protected function isAbsolutePath($path)
    {
        // TODO: Implement isAbsolutePath() method.
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath($path)
    {
        // TODO: Implement getAbsolutePath() method.
    }
}
