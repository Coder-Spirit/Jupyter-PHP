<?php


namespace Litipk\JupyterPHP\Actions;


use Litipk\JupyterPHP\JupyterBroker;
use Psy\Shell;
use React\ZMQ\SocketWrapper;


final class ExecuteAction implements Action
{
    /** @var JupyterBroker */
    private $broker;

    /** @var SocketWrapper */
    private $iopubSocket;

    /** @var SocketWrapper */
    private $shellSocket;

    /** @var Shell */
    private $shellSoul;


    /**
     * ExecuteAction constructor.
     * @param JupyterBroker $broker
     * @param SocketWrapper $iopubSocket
     * @param SocketWrapper $shellSocket
     * @param Shell $shellSoul
     */
    public function __construct(
        JupyterBroker $broker, SocketWrapper $iopubSocket, SocketWrapper $shellSocket, Shell $shellSoul
    )
    {
        $this->broker = $broker;
        $this->iopubSocket = $iopubSocket;
        $this->shellSocket = $shellSocket;
        $this->shellSoul = $shellSoul;
    }

    public function call(array $header, array $content)
    {
        $this->broker->send(
            $this->iopubSocket, 'status', ['execution_state' => 'busy'], $header
        );

        $execCount = isset($content->execution_count) ? $content->execution_count : 0;

        $this->shellSoul->addCode($content['code']);
        $this->shellSoul->flushCode();

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
            ['execution_count' => $execCount + 1, 'data' => $result, 'metadata' => new \stdClass],
            $header
        );
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
    }
}
