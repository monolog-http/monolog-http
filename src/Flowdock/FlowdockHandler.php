<?php

declare(strict_types=1);

namespace MonologHttp\Flowdock;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Flowdock\Formatter\FlowdockFormatterInterface;
use MonologHttp\Flowdock\Formatter\FlowdockMessageFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class FlowdockHandler extends AbstractHttpClientHandler
{
    /**
     * @var UriInterface|string
     */
    private $uri;

    /**
     * @var string|null
     */
    private $flowToken;

    /**
     * @param string|UriInterface $uri
     * @param int|string $level
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $uri,
        ?string $flowToken = null,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->uri = $uri;
        $this->flowToken = $flowToken;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof FlowdockFormatterInterface) {
            throw new \InvalidArgumentException(
                \sprintf('Expected an instance of %s', FlowdockFormatterInterface::class)
            );
        }

        return parent::setFormatter($formatter);
    }

    protected function createRequest(array $record): RequestInterface
    {
        $body = \json_encode($record['formatted']);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Could not format record to json: ' . \json_last_error_msg());
        }

        $request = $this->requestFactory->createRequest('POST', $this->uri)
            ->withHeader('Content-Type', 'application/json');

        /** @var string $body */
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }

    /**
     * @return FlowdockFormatterInterface
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new FlowdockMessageFormatter($this->flowToken);
    }
}
