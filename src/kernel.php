#!/usr/bn/env php
<?php


namespace Litipk\JupyterPHP;


require (__DIR__.'/../vendor/autoload.php');


use Ramsey\Uuid\Uuid;


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
