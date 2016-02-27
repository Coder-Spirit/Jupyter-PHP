<?php


namespace Litipk\JupyterPHP\Handlers;


use Litipk\JupyterPHP\Actions\ExecuteAction;
use Litipk\JupyterPHP\Actions\HistoryAction;
use Litipk\JupyterPHP\Actions\KernelInfoAction;
use Litipk\JupyterPHP\Actions\ShutdownAction;
use Litipk\JupyterPHP\JupyterBroker;

use React\ZMQ\SocketWrapper;


final class ShellMessagesHandler
{
    /** @var ExecuteAction */
    private $executeAction;

    /** @var HistoryAction */
    private $historyAction;

    /** @var KernelInfoAction */
    private $kernelInfoAction;

    /** @var ShutdownAction */
    private $shutdownAction;

    /**
     * ShellMessagesHandler constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $iopubSocket
     * @param SocketWrapper $shellSocket
     */
    public function __construct(JupyterBroker $broker, SocketWrapper $iopubSocket, SocketWrapper $shellSocket)
    {
        $this->executeAction = new ExecuteAction($broker, $iopubSocket, $shellSocket);
        $this->historyAction = new HistoryAction($broker, $shellSocket);
        $this->kernelInfoAction = new KernelInfoAction($broker, $shellSocket);
        $this->shutdownAction = new ShutdownAction($broker, $shellSocket);
    }

    /**
     * @param $msg
     */
    public function __invoke(array $msg)
    {
        list($zmqId, $delim, $hmac, $header, $parentHeader, $metadata, $content) = $msg;

        $header = json_decode($header);
        $content = json_decode($content);

        if ('kernel_info_request' === $header->msg_type) {
            $this->kernelInfoAction->call($header, $content);
        } elseif ('execute_request' === $header->msg_type) {
            $this->executeAction->call($header, $content);
        } elseif ('history_request' === $header->msg_type) {
            $this->historyAction->call($header, $content);
        } elseif ('shutdown_request' === $header->msg_type) {
            $this->shutdownAction->call($header, $content);
        } else {
            // TODO: Add logger!
        }
    }
}
