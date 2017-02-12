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

use Litipk\JupyterPHP\JupyterBroker;
use Monolog\Logger;
use React\ZMQ\SocketWrapper;

final class HbMessagesHandler
{
    /** @var Logger */
    private $logger;

    /** @var SocketWrapper */
    private $hbSocket;

    public function __construct(SocketWrapper $hbSocket, Logger $logger)
    {
        $this->logger = $logger;
        $this->hbSocket = $hbSocket;
    }

    public function __invoke($msg)
    {
        $this->logger->debug('Received message', ['processId' => getmypid(), 'msg' => $msg]);

        if (['ping'] === $msg) {
            $this->hbSocket->send($msg);
        } else {
            $this->logger->error('Unknown message', ['processId' => getmypid(), 'msg' => $msg]);
        }
    }
}
