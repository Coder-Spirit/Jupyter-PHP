<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JupyterPHP\Actions;

use JupyterPHP\JupyterBroker;
use React\ZMQ\SocketWrapper;

final class ShutdownAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $shellSocket;

    /** @var SocketWrapper */
    private $iopubSocket;

    public function __construct(JupyterBroker $broker, SocketWrapper $iopubSocket, SocketWrapper $shellSocket)
    {
        $this->broker = $broker;
        $this->iopubSocket = $iopubSocket;
        $this->shellSocket = $shellSocket;
    }

    public function call(array $header, array $content, $zmqIds = [])
    {
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);

        $replyContent = ['restart' => $content['restart']];
        $this->broker->send($this->shellSocket, 'shutdown_reply', $replyContent, $header, [], $zmqIds);

        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
