<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologHttp\Formatter\SlackFormatterInterface;
use MonologHttp\Formatter\SlackLineFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Sends notifications through a Slack Webhook.
 */
final class SlackWebhookHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $webhook;

    /**
     * @param string $webhook Slack Webhook string
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

    /**
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof SlackFormatterInterface) {
            throw new \InvalidArgumentException(\sprintf('Expected an instance of %s', SlackFormatterInterface::class));
        }

        return parent::setFormatter($formatter);
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

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new SlackLineFormatter();
    }
}
