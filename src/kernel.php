#!/usr/bn/env php
<?php


namespace Litipk\JupyterPHP;


require (__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');


use Ramsey\Uuid\Uuid;


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
    if (
        'linux' === strtolower(PHP_OS) &&
        preg_match('#^(/home/([a-zA-Z0-9_]+)/\.jupyter-php)/pkgs#', __DIR__, $execDirMatch) > 0
    ) {
        if (!file_exists($execDirMatch[1])) {
            mkdir($execDirMatch[1].'/logs', 0755, true);
        }

        file_put_contents(
            $execDirMatch[1].'/logs/error.log',
            $e->getFile().' : '.$e->getLine().' :: '.$e->getMessage()."\n\t".
            $e->getTraceAsString()
        );
    }
}
