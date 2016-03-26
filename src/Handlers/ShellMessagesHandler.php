<?php


namespace Litipk\JupyterPHP\Handlers;


use Litipk\JupyterPHP\Actions\ExecuteAction;
use Litipk\JupyterPHP\Actions\HistoryAction;
use Litipk\JupyterPHP\Actions\KernelInfoAction;
use Litipk\JupyterPHP\Actions\ShutdownAction;
use Litipk\JupyterPHP\JupyterBroker;

use Monolog\Logger;
use Psy\Shell;
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

    /** @var Shell */
    private $shellSoul;

    /** @var Logger */
    private $logger;

    /**
     * ShellMessagesHandler constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $iopubSocket
     * @param SocketWrapper $shellSocket
     * @param Shell $shellSoul
     * @param Logger $logger
     */
    public function __construct(
        JupyterBroker $broker, SocketWrapper $iopubSocket, SocketWrapper $shellSocket, Shell $shellSoul, Logger $logger
    )
    {
        $this->executeAction = new ExecuteAction($broker, $iopubSocket, $shellSocket, $shellSoul);
        $this->historyAction = new HistoryAction($broker, $shellSocket);
        $this->kernelInfoAction = new KernelInfoAction($broker, $shellSocket, $iopubSocket);
        $this->shutdownAction = new ShutdownAction($broker, $shellSocket);

        $this->shellSoul = $shellSoul;
        $this->logger = $logger;

        $broker->send(
            $iopubSocket, 'status', ['execution_state' => 'starting'], []
        );
    }

    /**
     * @param $msg
     */
    public function __invoke(array $msg)
    {
        list($zmqId, $delim, $hmac, $header, $parentHeader, $metadata, $content) = $msg;

        $header = json_decode($header, true);
        $content = json_decode($content, true);

        $this->logger->debug('Received message', [
            'processId'    => getmypid(),
            'zmqId'        => $zmqId,
            'delim'        => $delim,
            'hmac'         => $hmac,
            'header'       => $header,
            'parentHeader' => $parentHeader,
            'metadata'     => $metadata,
            'content'      => $content
        ]);

        if ('kernel_info_request' === $header['msg_type']) {
            $this->kernelInfoAction->call($header, $content);
        } elseif ('execute_request' === $header['msg_type']) {
            $this->executeAction->call($header, $content);
        } elseif ('history_request' === $header['msg_type']) {
            $this->historyAction->call($header, $content);
        } elseif ('shutdown_request' === $header['msg_type']) {
            $this->shutdownAction->call($header, $content);
        } else {
            $this->logger->error('Unknown message type', ['processId' => getmypid(), 'header' => $header]);
        }
    }
}
