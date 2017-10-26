<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Litipk\JupyterPHP\Handlers;


use Monolog\Logger;


final class IOPubMessagesHandler
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($msg)
    {
        $this->logger->debug('Received message', ['processId' => \getmypid(), 'msg' => $msg]);
    }
}
