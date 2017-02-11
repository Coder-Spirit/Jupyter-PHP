<?php


namespace Litipk\JupyterPHP\Actions;

use Litipk\JupyterPHP\JupyterBroker;
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

    public function call(array $header, array $content, $zmqId = null)
    {
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);

        $replyContent = ['restart' => $content['restart']];
        $this->broker->send($this->shellSocket, 'shutdown_reply', $replyContent, $header, [], $zmqId);

        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
