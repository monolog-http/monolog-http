<?php

declare(strict_types=1);

namespace MonologHttp\Gitlab;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Gitlab\Formatter\GitlabFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class GitlabHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $authKey;

    /**
     * @var string|null
     */
    private $service;

    /**
     * @var string[]
     */
    private $hosts;

    /**
     * @var string|null
     */
    private $monitoringTool;

    /**
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $uri,
        string $authKey,
        string $service = null,
        string $monitoringTool = null,
        array $hosts = [],
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->uri = $uri;
        $this->authKey = $authKey;
        $this->service = $service;
        $this->hosts = $hosts;
        $this->monitoringTool = $monitoringTool;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $content = \json_encode($record['formatted']);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Encoding json failed with reason: ' . \json_last_error_msg());
        }

        $request = $this->requestFactory->createRequest('POST', $this->uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $this->authKey);

        /** @var string $content */
        $request->getBody()->write($content);
        $request->getBody()->rewind();

        return $request;
    }

    /**
     * @return GitlabFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new GitlabFormatter($this->service, $this->monitoringTool, $this->hosts);
    }
}
