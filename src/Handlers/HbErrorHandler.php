<?php

namespace Litipk\JupyterPHP\Handlers;


use Monolog\Logger;


class HbErrorHandler
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($e)
    {
        $this->logger->debug('Received message', ['processId' => getmypid(), 'error' => $e]);
    }
}