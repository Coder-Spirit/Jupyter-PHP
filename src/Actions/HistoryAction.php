<?php


namespace Litipk\JupyterPHP\Actions;


use Litipk\JupyterPHP\JupyterBroker;
use React\ZMQ\SocketWrapper;


final class HistoryAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $shellSocket;


    /**
     * ExecuteAction constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $shellSocket
     */
    public function __construct(JupyterBroker $broker, SocketWrapper $shellSocket)
    {
        $this->broker = $broker;
        $this->shellSocket = $shellSocket;
    }

    public function call(array $header, array $content)
    {
        $this->broker->send($this->shellSocket, 'history_reply', ['history' => []], $header);
    }
}
