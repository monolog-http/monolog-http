<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

abstract class AbstractHttpClientHandler extends AbstractProcessingHandler
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @param int|string $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Writes the record down to the log of the implementing handler
     */
    protected function write(array $record): void
    {
        $this->client->sendRequest($this->createRequest($record));
    }

    abstract public function createRequest(array $record): RequestInterface;
}
