<?php


namespace Litipk\JupyterPHP\Handlers;


final class ShellMessagesHandler
{
    public function __invoke($msg)
    {
        list($zmqId, $delim, $hmac, $header, $parent_header, $metadata, $content) = $msg;

        $header = json_decode($header);
        $content = json_decode($content);

        if ('kernel_info_request' === $header->msg_type) {

        } elseif ('execute_request' === $header->msg_type) {

        } elseif ('history_request' === $header->msg_type) {

        } elseif ('shutdown_request' === $header->msg_type) {

        } else {
            // TODO: Add logger!
        }
    }
}
