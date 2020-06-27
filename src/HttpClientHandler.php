<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * This is a generic purpose handler
 */
final class HttpClientHandler extends AbstractHttpClientHandler
{
    /**
     * @var string|UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string|UriInterface $uri
     * @param string|int $level
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $uri,
        string $method,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->uri = $uri;
        $this->method = $method;
    }

    protected function createRequest(array $record): RequestInterface
    {
        return $this->requestFactory->createRequest($this->method, $this->uri);
    }
}
