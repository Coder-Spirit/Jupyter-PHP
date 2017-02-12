<?php

/*
 * This file is part of Jupyter-PHP.
 *
 * (c) 2015-2017 Litipk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Litipk\JupyterPHP;


use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use React\ZMQ\SocketWrapper;


final class JupyterBroker
{
    /** @var string */
    private $key;

    /** @var string */
    private $signatureScheme;

    /** @var string */
    private $hashAlgorithm;

    /** @var UuidInterface */
    private $sessionId;

    /** @var null|LoggerInterface */
    private $logger;


    public function __construct($key, $signatureScheme, UuidInterface $sessionId, LoggerInterface $logger = null)
    {
        $this->key = $key;
        $this->signatureScheme = $signatureScheme;
        $this->hashAlgorithm = preg_split('/-/', $signatureScheme)[1];
        $this->sessionId = $sessionId;
        $this->logger = $logger;
    }

    public function send(
        SocketWrapper $stream,
        $msgType,
        array $content = [],
        array $parentHeader = [],
        array $metadata = [],
        $zmqId = null
    ) {
        $header = $this->createHeader($msgType);

        $msgDef = [
            json_encode(empty($header) ? new \stdClass : $header),
            json_encode(empty($parentHeader) ? new \stdClass : $parentHeader),
            json_encode(empty($metadata) ? new \stdClass : $metadata),
            json_encode(empty($content) ? new \stdClass : $content),
        ];

        if (null !== $zmqId) {
            $finalMsg = [$zmqId];
        } else {
            $finalMsg = [];
        }

        $finalMsg = array_merge(
            $finalMsg,
            ['<IDS|MSG>', $this->sign($msgDef)],
            $msgDef);

        if (null !== $this->logger) {
            $this->logger->debug('Sending message', ['processId' => getmypid(), 'message' => $finalMsg]);
        }

        $stream->send($finalMsg);
    }

    private function createHeader(string $msgType): array
    {
        return [
            'date'     => (new \DateTime('NOW'))->format('c'),
            'msg_id'   => Uuid::uuid4()->toString(),
            'username' => "kernel",
            'session'  => $this->sessionId->toString(),
            'msg_type' => $msgType,
            'version'  => '5.0',
        ];
    }

    private function sign(array $message_list): string
    {
        $hm = hash_init(
            $this->hashAlgorithm,
            HASH_HMAC,
            $this->key
        );

        foreach ($message_list as $item) {
            hash_update($hm, $item);
        }

        return hash_final($hm);
    }
}
