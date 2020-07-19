<?php

declare(strict_types=1);

namespace MonologHttp\MicrosoftTeams;

use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @experimental This handler is not tested. Any help testing this would be very much appreciated.
 */
final class MicrosoftTeamsHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $webhook;

    /**
     * @param string|int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $webhook,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->webhook = $webhook;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $body = \json_encode($record['formatted']);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Could not format record to json: ' . \json_last_error_msg());
        };

        $request = $this->requestFactory->createRequest('POST', $this->webhook);
        $request = $request->withHeader('Content-Type', ['application/json']);

        /** @var string $body */
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }
}
