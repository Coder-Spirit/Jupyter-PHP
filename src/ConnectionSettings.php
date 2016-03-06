<?php


namespace Litipk\JupyterPHP;


final class ConnectionSettings
{
    /**
     * @return array[string]mixed
     */
    public static function get()
    {
        global $argv;
        if (!isset($argv) || empty($argv)) {
            $argv = $_SERVER['argv'];
        }

        if (is_array($argv) && count($argv) > 1) {
            $connectionFileContents = file_get_contents($argv[1], null, null, 0, 2048);

            if (false === $connectionFileContents) {
                throw new \RuntimeException('Connection Settings: Unable to open the connection file.');
            }

            $connectionSettings = json_decode($connectionFileContents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Connection Settings: Corrupted connection file.');
            }

            return $connectionSettings;
        } else {
            throw new \RuntimeException('Connection Settings: Not specified.');
        }
    }

    /**
     * @param null|array $connectionSettings
     * @return array[string]string
     */
    public static function getConnectionUris(array $connectionSettings = null)
    {
        if (null === $connectionSettings) {
            $connectionSettings = self::get();
        }

        $connectionUri = $connectionSettings['transport'].'://'.$connectionSettings['ip'].':';

        return [
            'stdin'   => $connectionUri.$connectionSettings['stdin_port'],
            'control' => $connectionUri.$connectionSettings['control_port'],
            'hb'      => $connectionUri.$connectionSettings['hb_port'],
            'shell'   => $connectionUri.$connectionSettings['shell_port'],
            'iopub'   => $connectionUri.$connectionSettings['iopub_port']
        ];
    }
}
