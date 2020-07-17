<?php

declare(strict_types=1);

namespace MonologHttp\Sentry;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Sentry\Formatter\SentryFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class SentryHandler extends AbstractHttpClientHandler
{

    /**
     * @var string
     */
    private $sentryKey;

    /**
     * @var string
     */
    private $sentryVersion;

    /**
     * @var UriInterface|string
     */
    private $uri;

    /**
     * @param UriInterface|string $uri
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $uri,
        string $sentryKey,
        string $sentryVersion = '7',
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->uri = $uri;
        $this->sentryKey = $sentryKey;
        $this->sentryVersion = $sentryVersion;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $body = \json_encode($record['formatted']);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Could not format record to json: ' . \json_last_error_msg());
        }

        $request = $this->requestFactory->createRequest('POST', $this->uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Sentry-Auth', [
                \sprintf('Sentry sentry_version=%s', $this->sentryVersion),
                \sprintf('sentry_key=%s', $this->sentryKey),
            ]);

        /** @var string $body */
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }

    /**
     * @return SentryFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new SentryFormatter();
    }
}
