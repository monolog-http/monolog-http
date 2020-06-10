<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Logs to Cube.
 *
 * @link http://square.github.com/cube/
 */
final class CubeHandler extends AbstractHttpClientHandler
{
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
    }

    // todo
    public function createRequest(array $record): RequestInterface
    {
        return $this->requestFactory->createRequest('GET', 'what');
    }
}
