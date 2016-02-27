<?php


namespace Litipk\JupyterPHP\Actions;


interface Action
{
    public function call($header, $content);
}
