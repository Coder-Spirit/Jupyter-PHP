<?php


namespace Litipk\JupyterPHP\System;


final class MacSystem extends UnixSystem
{
    /** @return integer */
    public function getOperativeSystem()
    {
        return self::OS_OSX;
    }

    /** @return string */
    public function getAppDataDirectory()
    {
        return $this->getCurrentUserHome().'/Library/jupyter-php';
    }
}
