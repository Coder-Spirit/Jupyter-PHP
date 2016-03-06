<?php


namespace Litipk\JupyterPHP\Actions;


use Litipk\JupyterPHP\JupyterBroker;
use React\ZMQ\SocketWrapper;


final class ExecuteAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $iopubSocket;

    /** @var SocketWrapper */
    private $shellSocket;


    /**
     * ExecuteAction constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $iopubSocket
     * @param SocketWrapper $shellSocket
     */
    public function __construct(JupyterBroker $broker, SocketWrapper $iopubSocket, SocketWrapper $shellSocket)
    {
        $this->broker = $broker;
        $this->iopubSocket = $iopubSocket;
        $this->shellSocket = $shellSocket;
    }

    public function call(array $header, array $content)
    {
        $this->broker->send(
            $this->iopubSocket, 'status', ['execution_state' => 'busy'], $header
        );

        $execCount = isset($content->execution_count) ? $content->execution_count : 0;

        //  TODO: Here is where PsySH goes
        $vars_before = get_defined_vars();
        ob_start();
        $result = eval($content['code']);
        $stdOut = ob_get_contents();
        ob_end_clean();
        $vars_after = get_defined_vars();
        // TODO

        $this->broker->send($this->shellSocket, 'execute_reply', ['status' => 'ok'], $header);
        $this->broker->send($this->iopubSocket, 'stream',  ['name' => 'stdout', 'data' => $stdOut], $header);
        $this->broker->send(
            $this->iopubSocket,
            'execute_result',
            ['execution_count' => $execCount + 1, 'data' => $result, 'metadata' => []],
            $header
        );
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
