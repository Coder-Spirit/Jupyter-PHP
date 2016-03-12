#!/usr/bn/env php
<?php


namespace Litipk\JupyterPHP;


require (__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');


use Litipk\JupyterPHP\System\System;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;


$system = System::getSystem();

$logger = new Logger('main');

if ('root' === $system->getCurrentUser()) {
    $logger->pushHandler(new FingersCrossedHandler(new SyslogHandler('jupyter-php'), null, 128));
} else {
    $system->ensurePath($system->getAppDataDirectory().'/logs');
    $logger->pushHandler(new FingersCrossedHandler(
        new RotatingFileHandler($system->getAppDataDirectory().'/logs/error.log', 7), null, 128
    ));
}


try {
    // Obtain settings
    $connectionSettings = ConnectionSettings::get();
    $connUris = ConnectionSettings::getConnectionUris($connectionSettings);

    $kernelCore = new KernelCore(
        new JupyterBroker(
            $connectionSettings['key'], $connectionSettings['signature_scheme'], Uuid::uuid4()
        ),
        $connUris
    );

    $kernelCore->run();
} catch (\Exception $e) {
    $logger->error('Unexpected error', ['exception' => $e]);
}
