<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JupyterPHP\System;

final class MacSystem extends UnixSystem
{
    public function getOperativeSystem(): int
    {
        return self::OS_OSX;
    }

    public function getAppDataDirectory(): string
    {
        return $this->getCurrentUserHome().'/Library/jupyter-php';
    }
}
