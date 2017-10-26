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

final class LinuxSystem extends UnixSystem
{
    public function getOperativeSystem(): int
    {
        return self::OS_LINUX;
    }
}
