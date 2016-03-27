#!/usr/bin/env php
<?php


namespace Litipk\JupyterPHP;


require (__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');


use Litipk\JupyterPHP\System\System;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;


$system = System::getSystem();
$logger = new Logger('kernel');

$loggerActivationStrategy = new ErrorLevelActivationStrategy(LoggerSettings::getCrossFingersLevel());

if ('root' === $system->getCurrentUser()) {
    if (System::OS_LINUX === $system->getOperativeSystem()) {
        $logger->pushHandler(new FingersCrossedHandler(
            new SyslogHandler('jupyter-php'),
            $loggerActivationStrategy,
            128
        ));
    }
} else {
    $system->ensurePath($system->getAppDataDirectory().'/logs');
    $logger->pushHandler(new FingersCrossedHandler(
        new RotatingFileHandler($system->getAppDataDirectory().'/logs/error.log', 7),
        $loggerActivationStrategy,
        128
    ));
}


try {
    // Obtain settings
    $connectionSettings = ConnectionSettings::get();
    $connUris = ConnectionSettings::getConnectionUris($connectionSettings);

    $kernelCore = new KernelCore(
        new JupyterBroker(
            $connectionSettings['key'],
            $connectionSettings['signature_scheme'],
            Uuid::uuid4(),
            $logger->withName('JupyterBroker')
        ),
        $connUris,
        $logger->withName('KernelCore')
    );

    $kernelCore->run();
} catch (\Exception $e) {
    $logger->error('Unexpected error', ['exception' => $e]);
}
