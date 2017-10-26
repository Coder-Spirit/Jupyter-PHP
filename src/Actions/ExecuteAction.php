<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /** @var bool */
    private $silent;

    /** @var int */
    private $execCount = 0;


    public function __construct(
        JupyterBroker $broker,
        SocketWrapper $iopubSocket,
        SocketWrapper $shellSocket,
        Shell $shellSoul
    ) {
        $this->broker = $broker;
        $this->iopubSocket = $iopubSocket;
        $this->shellSocket = $shellSocket;
        $this->shellSoul = $shellSoul;
    }

    public function call(array $header, array $content, $zmqIds = [])
    {
        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);

        $this->header = $header;
        $this->code = $content['code'];
        $this->silent = $content['silent'];

        if (!$this->silent) {
            $this->execCount = $this->execCount + 1;

            $this->broker->send(
                $this->iopubSocket,
                'execute_input',
                ['code' => $this->code, 'execution_count' => $this->execCount],
                $this->header
            );
        }

        ($this->getClosure())();

        $replyContent = [
            'status' => 'ok',
            'execution_count' => $this->execCount,
            'payload' => [],
            'user_expressions' => new \stdClass
        ];

        $this->broker->send($this->shellSocket, 'execute_reply', $replyContent, $this->header, [], $zmqIds);

        $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $this->header);
    }

    public function notifyMessage(string $message)
    {
        $this->broker->send(
            $this->iopubSocket,
            'execute_result',
            ['execution_count' => $this->execCount, 'data' => ['text/plain' => $message], 'metadata' => new \stdClass],
            $this->header
        );
    }

    private function getClosure(): callable
    {
        $closure = function () {
            \extract($this->shellSoul->getScopeVariables());

            try {
                $this->shellSoul->addCode($this->code);

                // evaluate the current code buffer
                \ob_start([$this->shellSoul, 'writeStdout'], 1);

                \set_error_handler([$this->shellSoul, 'handleError']);
                $_ = eval($this->shellSoul->flushCode() ?: Loop::NOOP_INPUT);
                \restore_error_handler();

                \ob_end_flush();

                $this->shellSoul->writeReturnValue($_);
            } catch (BreakException $_e) {
                $this->handleEvalException($_e);
                return;
            } catch (ThrowUpException $_e) {
                $this->handleEvalException($_e);
                throw $_e;
            } catch (\Error $_e) {
                $this->handleEvalException(new \ErrorException(
                    $_e->getMessage(),
                    $_e->getCode(),
                    1,
                    $_e->getFile(),
                    $_e->getLine(),
                    $_e->getPrevious()
                ));
            } catch (\Exception $_e) {
                $this->handleEvalException($_e);
            }

            $this->shellSoul->setScopeVariables(\get_defined_vars());
        };

        return $closure;
    }

    private function handleEvalException(\Exception $_e)
    {
        \restore_error_handler();
        if (\ob_get_level() > 0) {
            \ob_end_clean();
        }
        $this->shellSoul->writeException($_e);
    }
}
