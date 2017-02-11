<?php


namespace Litipk\JupyterPHP\Actions;

use Litipk\JupyterPHP\JupyterBroker;
use React\ZMQ\SocketWrapper;

final class KernelInfoAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $shellSocket;

    /** @var SocketWrapper */
    private $iopubSocket;

    /**
     * ExecuteAction constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $shellSocket
     * @param SocketWrapper $iopubSocket
     */
    public function __construct(JupyterBroker $broker, SocketWrapper $shellSocket, SocketWrapper $iopubSocket)
    {
        $this->broker = $broker;
        $this->shellSocket = $shellSocket;
        $this->iopubSocket = $iopubSocket;
    }

    public function call(array $header, array $content, $zmqId = null)
    {
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);

        $this->broker->send(
            $this->shellSocket,
            'kernel_info_reply',
            [
                'protocol_version' => '5.0',
                'implementation' => 'jupyter-php',
                'implementation_version' => '0.1.0',
                'banner' => 'Jupyter-PHP Kernel',
                'language_info' => [
                    'name' => 'PHP',
                    'version' => phpversion(),
                    'mimetype' => 'text/x-php',
                    'file_extension' => '.php',
                    'pygments_lexer' => 'PHP',
                ],
                'status' => 'ok',
            ],
            $header,
            [],
            $zmqId
        );

        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
