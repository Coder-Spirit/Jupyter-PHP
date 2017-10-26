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

final class KernelInfoAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $shellSocket;

    /** @var SocketWrapper */
    private $iopubSocket;


    public function __construct(JupyterBroker $broker, SocketWrapper $shellSocket, SocketWrapper $iopubSocket)
    {
        $this->broker = $broker;
        $this->shellSocket = $shellSocket;
        $this->iopubSocket = $iopubSocket;
    }

    public function call(array $header, array $content, $zmqIds = [])
    {
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);

        $this->broker->send(
            $this->shellSocket,
            'kernel_info_reply',
            [
                'protocol_version' => '5.0',
                'implementation' => 'jupyter-php',
                'implementation_version' => '0.1.0',
                'banner' => 'Jupyter-PHP Kernel',
                'language_info' => [
                    'name' => 'PHP',
                    'version' => \phpversion(),
                    'mimetype' => 'text/x-php',
                    'file_extension' => '.php',
                    'pygments_lexer' => 'PHP',
                ],
                'status' => 'ok',
            ],
            $header,
            [],
            $zmqIds
        );

        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
