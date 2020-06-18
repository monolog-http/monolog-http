<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Logger;
use Monolog\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Logs to Cube.
 *
 * @link http://square.github.com/cube/
 */
final class CubeHandler extends AbstractHttpClientHandler
{
    /**
     * @var string|UriInterface
     */
    private $uri;

    /**
     * @param string|UriInterface $uri
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $uri,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->uri = $uri;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $date = $record['datetime'];

        $data = ['time' => $date->format('Y-m-d\TH:i:s.uO')];
        unset($record['datetime']);

        if (isset($record['context']['type'])) {
            $data['type'] = $record['context']['type'];
            unset($record['context']['type']);
        } else {
            $data['type'] = $record['channel'];
        }

        $data['data'] = $record['context'];
        $data['data']['level'] = $record['level'];

        $json = Utils::jsonEncode($data);

        $request = $this->requestFactory->createRequest('POST', $this->uri);
        $request->getBody()->write($json);
        $request->getBody()->rewind();

        return $request
            ->withHeader('Content-Type', ['application/json'])
            ->withHeader('Content-Length', (string)\strlen('[' . $json . ']'));
    }
}
