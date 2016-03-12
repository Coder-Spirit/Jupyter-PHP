<?php


namespace Litipk\JupyterPHP\System;


final class LinuxSystem extends UnixSystem
{
    /** @return integer */
    public function getOperativeSystem()
    {
        return self::OS_LINUX;
    }

    /** @return string */
    public function getAppDataDirectory()
    {
        return $this->getCurrentUserHome().'/.jupyter-php';
    }
}
