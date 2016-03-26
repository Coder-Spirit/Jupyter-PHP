<?php


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
        $this->logger->debug('Received message', ['processId' => getmypid(), 'msg' => $msg]);
    }
}
