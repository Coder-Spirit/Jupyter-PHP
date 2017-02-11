<?php


namespace Litipk\JupyterPHP\Actions;


interface Action
{
    public function call(array $header, array $content, $zmqId = null);
}
