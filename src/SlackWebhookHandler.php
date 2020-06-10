<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use MonologHttp\Formatter\SlackFormatterInterface;
use MonologHttp\Formatter\SlackLineFormatter;

/**
 * Sends notifications through a Slack Webhook.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
final class SlackWebhookHandler extends AbstractProcessingHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $webhook;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
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
        parent::__construct($level, $bubble);

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->webhook = $webhook;
    }

    /**
     * @param FormatterInterface $formatter
     * @return HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof SlackFormatterInterface) {
            throw new \InvalidArgumentException(\sprintf('Expected an instance of %s', SlackFormatterInterface::class));
        }

        return parent::setFormatter($formatter);
    }

    protected function write(array $record): void
    {
        $body = \json_encode($record['formatted']);
        if ($body === false) {
            throw new \InvalidArgumentException('Could not format record to json');
        };

        $request = $this->requestFactory->createRequest('POST', $this->webhook);
        $request = $request->withHeader('Content-Type', ['application/json']);
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        $this->client->sendRequest($request);
    }

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new SlackLineFormatter();
    }
}
