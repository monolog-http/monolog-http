<?php

declare(strict_types=1);

namespace MonologHttp\IFTTT;

use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class IFTTTHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @param string|int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $eventName,
        string $secretKey,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->eventName = $eventName;
        $this->secretKey = $secretKey;
    }

    public function createRequest(array $record): RequestInterface
    {
        $postData = [
            'value1' => $record['channel'],
            'value2' => $record['level_name'],
            'value3' => $record['message'],
        ];
        $content = \json_encode($postData);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Could not format record to json: ' . \json_last_error_msg());
        };

        $uri = 'https://maker.ifttt.com/trigger/' . $this->eventName . '/with/key/' . $this->secretKey;

        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json');

        $body = $request->getBody();

        $body->write($content);
        $body->rewind();

        return $request;
    }
}
