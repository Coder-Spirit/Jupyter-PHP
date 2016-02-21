<?php


namespace Litipk\JupyterPHP;


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
    private $sesssionId;

    /**
     * JupyterBroker constructor.
     * @param string $key
     * @param string $signatureScheme
     * @param UuidInterface $sessionId
     */
    public function __construct($key, $signatureScheme, UuidInterface $sessionId)
    {
        $this->key = $key;
        $this->signatureScheme = $signatureScheme;
        $this->hashAlgorithm = preg_split('/-/', $signatureScheme)[1];
        $this->sesssionId = $sessionId;
    }

    /**
     * @param SocketWrapper $stream
     * @param string $msgType
     * @param array $content
     * @param array $parentHeader
     * @param array $metadata
     */
    public function send(
        SocketWrapper $stream, $msgType, array $content = [], array $parentHeader = [], array $metadata = []
    )
    {
        $header = $this->createHeader($msgType);

        $msgDef = [
            json_encode($header),
            json_encode($parentHeader),
            json_encode($metadata),
            json_encode($content),
        ];

        $stream->send(array_merge(
            ['<IDS|MSG>', $this->sign($msgDef)],
            $msgDef
        ));
    }

    /**
     * @param string $msgType
     * @return array
     */
    private function createHeader($msgType)
    {
        return [
            'date'     => (new \DateTime('NOW'))->format('c'),
            'msg_id'   => Uuid::uuid4()->toString(),
            'username' => "kernel",
            'session'  => $this->sesssionId->toString(),
            'msg_type' => $msgType,
        ];
    }

    private function sign(array $message_list) {
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
