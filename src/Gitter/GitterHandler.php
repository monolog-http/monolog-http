<?php

declare(strict_types=1);

namespace MonologHttp\Gitter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Gitter\Formatter\GitterLineFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class GitterHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $roomId;

    /**
     * @var string
     */
    private $token;

    /**
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $roomId,
        string $token,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->roomId = $roomId;
        $this->token = $token;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $body = \json_encode($record['formatted']);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Could not format record to json: ' . \json_last_error_msg());
        };

        $url = \sprintf('https://api.gitter.im/v1/rooms/%s/chatMessages', $this->roomId);
        $request = $this->requestFactory->createRequest('POST', $url);
        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $this->token);

        /** @var string $body */
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }

    /**
     * @return GitterLineFormatter
     */
    public function getDefaultFormatter(): FormatterInterface
    {
        return new GitterLineFormatter();
    }
}
