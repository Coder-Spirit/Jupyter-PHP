<?php


namespace Litipk\JupyterPHP;


use Litipk\JupyterPHP\Actions\ExecuteAction;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;


final class KernelOutput implements OutputInterface
{
    /** @var ExecuteAction */
    private $executeAction;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var OutputFormatterInterface */
    private $formatter;

    /**
     * KernelOutput constructor.
     * @param ExecuteAction $executeAction
     * @param LoggerInterface $logger
     */
    public function __construct(ExecuteAction $executeAction, LoggerInterface $logger)
    {
        $this->executeAction = $executeAction;
        $this->logger = $logger;
        
        $this->formatter = new OutputFormatter();
        $this->formatter->setDecorated(true);

        $this->initFormatters();
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $this->logger->debug('Write operation inside KernelOutput');

        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;
        
        if (is_string($messages)) {
            if ("<aside>⏎</aside>" === $messages) {
                return;
            }

            $preparedMessage = $messages . ($newline ? '' : "\n");
            switch ($type) {
                case OutputInterface::OUTPUT_NORMAL:
                    $preparedMessage = $this->formatter->format($messages) . ($newline ? '' : "\n");
                    break;
                case OutputInterface::OUTPUT_RAW:
                    break;
                case OutputInterface::OUTPUT_PLAIN:
                    $preparedMessage = strip_tags($this->formatter->format($messages)) . ($newline ? '' : "\n");
                    break;
            }
        } elseif (is_array($messages)) {
            $preparedMessage = implode("\n", $messages) . ($newline ? '' : "\n");
        } else {
            return; // TODO: Throw an error?
        }

        $this->executeAction->notifyMessage($preparedMessage);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, $options = 0)
    {
        $this->write($messages, true, $options);
    }

    /**
     * Sets the verbosity of the output.
     *
     * @param int $level The level of verbosity (one of the VERBOSITY constants)
     */
    public function setVerbosity($level)
    {
        // TODO: Implement setVerbosity() method.
    }

    /**
     * Gets the current verbosity of the output.
     *
     * @return int The current level of verbosity (one of the VERBOSITY constants)
     */
    public function getVerbosity()
    {
        return self::VERBOSITY_NORMAL;
    }

    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool true if verbosity is set to VERBOSITY_QUIET, false otherwise
     */
    public function isQuiet()
    {
        return false;
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERY_VERBOSE, false otherwise
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool true if verbosity is set to VERBOSITY_DEBUG, false otherwise
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * Sets the decorated flag.
     * @param bool $decorated Whether to decorate the messages
     */
    public function setDecorated($decorated)
    {
        // Interface compliance
    }

    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated()
    {
        return true;
    }

    /**
     * Sets output formatter.
     * @param OutputFormatterInterface $formatter
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // Interface compliance
    }

    /**
     * Returns current output formatter instance.
     * @return OutputFormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Initialize output formatter styles.
     */
    private function initFormatters()
    {
        $formatter = $this->getFormatter();

        $formatter->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('warning', new OutputFormatterStyle('yellow', null, ['bold']));
        $formatter->setStyle('aside',   new OutputFormatterStyle('blue'));
        $formatter->setStyle('strong',  new OutputFormatterStyle(null, null, array('bold')));
        $formatter->setStyle('return',  new OutputFormatterStyle('cyan'));
        $formatter->setStyle('urgent',  new OutputFormatterStyle('red'));
        $formatter->setStyle('hidden',  new OutputFormatterStyle('white'));

        // Types
        $formatter->setStyle('number',   new OutputFormatterStyle('magenta'));
        $formatter->setStyle('string',   new OutputFormatterStyle('green'));
        $formatter->setStyle('bool',     new OutputFormatterStyle('cyan'));
        $formatter->setStyle('keyword',  new OutputFormatterStyle('yellow'));
        $formatter->setStyle('comment',  new OutputFormatterStyle('blue'));
        $formatter->setStyle('object',   new OutputFormatterStyle('blue'));
        $formatter->setStyle('resource', new OutputFormatterStyle('yellow'));
    }
}