<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Formatter\FlowdockFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Sends notifications through the Flowdock push API
 *
 * This must be configured with a FlowdockFormatter instance via setFormatter()
 *
 * @see https://www.flowdock.com/api/push
 */
final class FlowdockHandler extends AbstractHttpClientHandler
{
    /**
     * @var string|null
     */
    private $apiToken;

    /**
     * @var UriInterface|string
     */
    private $uri;

    /**
     * @param UriInterface|string $uri
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $uri,
        string $apiToken = null,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->apiToken = $apiToken;
        $this->uri = $uri;
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof FlowdockFormatter) {
            throw new \InvalidArgumentException('The FlowdockHandler requires an instance of Monolog\Formatter\FlowdockFormatter to function correctly');
        }

        return parent::setFormatter($formatter);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        throw new \InvalidArgumentException('The FlowdockHandler must be configured (via setFormatter) with an instance of Monolog\Formatter\FlowdockFormatter to function correctly');
    }

    public function createRequest(array $record): RequestInterface
    {
        $body = \json_encode($record['formatted']['flowdock']);
        if ($body === false) {
            throw new \InvalidArgumentException('Could not format record to json');
        };

        $request = $this->requestFactory->createRequest('POST', $this->uri);
        $request = $request->withHeader('Content-Type', ['application/json']);
        $request->getBody()->write($body);
        $request->getBody()->rewind();
        return $request;
    }
}
