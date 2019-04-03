<?php

/**
 * Author: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 */

use JupyterPHP\zPHP;

class HTML
{
    /** @var JupyterPHP\zPHP */
    private $zPHP;

    public function __construct(zPHP $zPHP)
    {
        $this->zPHP = $zPHP;
    }
    public function __invoke($htmlData)
    {
        $this->zPHP->send(
            'display_data',
            [
                'data'=>[
                    'text/html'=>$htmlData
                ],
                'metadata'=>[]
            ]
        );
    }


}
