<?php


namespace Litipk\JupyterPHP\Actions;


use Litipk\JupyterPHP\JupyterBroker;
use Psy\Exception\BreakException;
use Psy\Exception\ThrowUpException;
use Psy\ExecutionLoop\Loop;
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

    /** @var array */
    private $header;

    /** @var string */
    private $code;
    
    /** @var int */
    private $execCount;

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

    public function call(array $header, array $content, $zmqId = null)
    {
        $this->broker->send(
            $this->iopubSocket, 'status', ['execution_state' => 'busy'], $header
        );

        $this->header = $header;
        $this->execCount = isset($content->execution_count) ? $content->execution_count : 0;
        $this->code = $content['code'];

        $closure = $this->getClosure();
        $closure();
    }

    /**
     * @param string $message
     */
    public function notifyMessage($message)
    {
        $this->broker->send($this->shellSocket, 'execute_reply', ['status' => 'ok'], $this->header);
        $this->broker->send($this->iopubSocket, 'stream',  ['name' => 'stdout', 'data' => $message], $this->header);
        $this->broker->send(
            $this->iopubSocket,
            'execute_result',
            ['execution_count' => $this->execCount + 1, 'data' => $message, 'metadata' => new \stdClass],
            $this->header
        );
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $this->header);
    }

    /**
     * @return callable
     */
    private function getClosure()
    {
        $closure = function () {
            extract($this->shellSoul->getScopeVariables());

            try {
                $this->shellSoul->addCode($this->code);

                // evaluate the current code buffer
                ob_start(
                    [$this->shellSoul, 'writeStdout'],
                    version_compare(PHP_VERSION, '5.4', '>=') ? 1 : 2
                );

                set_error_handler([$this->shellSoul, 'handleError']);
                $_ = eval($this->shellSoul->flushCode() ?: Loop::NOOP_INPUT);
                restore_error_handler();

                ob_end_flush();

                $this->shellSoul->writeReturnValue($_);
            } catch (BreakException $_e) {
                restore_error_handler();
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $this->shellSoul->writeException($_e);

                return;
            } catch (ThrowUpException $_e) {
                restore_error_handler();
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $this->shellSoul->writeException($_e);

                throw $_e;
            } catch (\Exception $_e) {
                restore_error_handler();
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $this->shellSoul->writeException($_e);
            }

            $this->shellSoul->setScopeVariables(get_defined_vars());
        };

        return $closure;
    }
}
