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

final class HistoryAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $shellSocket;


    public function __construct(JupyterBroker $broker, SocketWrapper $shellSocket)
    {
        $this->broker = $broker;
        $this->shellSocket = $shellSocket;
    }

    public function call(array $header, array $content, $zmqIds = [])
    {
        $this->broker->send($this->shellSocket, 'history_reply', ['history' => []], $header, [], $zmqIds);
    }
}
