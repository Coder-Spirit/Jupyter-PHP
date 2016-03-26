<?php


namespace Litipk\JupyterPHP;


use Litipk\JupyterPHP\Handlers\HbErrorHandler;
use Litipk\JupyterPHP\Handlers\HbMessagesHandler;
use Litipk\JupyterPHP\Handlers\IOPubMessagesHandler;
use Litipk\JupyterPHP\Handlers\ShellMessagesHandler;

use Monolog\Logger;
use Psy\Shell;
use React\EventLoop\Factory as ReactFactory;
use React\ZMQ\Context as ReactZmqContext;
use React\ZMQ\SocketWrapper;


/**
 * Class KernelCore (no pun intended)
 * @package Litipk\JupyterPHP
 */
final class KernelCore
{
    /** @var JupyterBroker */
    private $broker;

    /** @var \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop */
    private $reactLoop;

    /** @var Logger */
    private $logger;

    /** @var SocketWrapper|\ZMQSocket */
    private $hbSocket;
    /** @var SocketWrapper|\ZMQSocket */
    private $iopubSocket;
    /** @var SocketWrapper|\ZMQSocket */
    private $controlSocket;
    /** @var SocketWrapper|\ZMQSocket */
    private $stdinSocket;
    /** @var SocketWrapper|\ZMQSocket */
    private $shellSocket;

    /** @var Shell */
    private $shellSoul;

    /**
     * KernelCore constructor.
     * @param JupyterBroker $jupyterBroker
     * @param array $connUris [string]string $connUris
     * @param Logger $logger
     */
    public function __construct(JupyterBroker $jupyterBroker, array $connUris, Logger $logger)
    {
        $this->broker = $jupyterBroker;
        $this->logger = $logger;

        $this->shellSoul = new Shell();

        $this->initSockets($connUris);
        $this->registerHandlers();
    }

    /**
     *
     */
    public function run()
    {
        $this->reactLoop->run();
    }

    /**
     * @param array[string]string $connUris
     */
    private function initSockets(array $connUris)
    {
        // Create context
        $this->reactLoop = ReactFactory::create();

        /** @var ReactZmqContext|\ZMQContext $reactZmqContext */
        $reactZmqContext = new ReactZmqContext($this->reactLoop);

        $this->hbSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_REP);
        $this->hbSocket->bind($connUris['hb']);

        $this->iopubSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_PUB);
        $this->iopubSocket->bind($connUris['iopub']);

        $this->controlSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
        $this->controlSocket->bind($connUris['control']);

        $this->stdinSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
        $this->stdinSocket->bind($connUris['stdin']);

        $this->shellSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
        $this->shellSocket->bind($connUris['shell']);
    }

    /**
     *
     */
    private function registerHandlers()
    {
        $this->hbSocket->on(
            'error',
            new HbErrorHandler($this->logger->withName('HbErrorHandler'))
        );
        $this->hbSocket->on(
            'messages',
            new HbMessagesHandler($this->logger->withName('HbMessagesHandler'))
        );
        $this->iopubSocket->on(
            'messages',
            new IOPubMessagesHandler($this->logger->withName('IOPubMessagesHandler'))
        );
        $this->shellSocket->on(
            'messages',
            new ShellMessagesHandler(
                $this->broker,
                $this->iopubSocket,
                $this->shellSocket,
                $this->shellSoul,
                $this->logger->withName('ShellMessagesHandler')
            )
        );
    }
}
