<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use InvalidArgumentException;

/**
 * Logs to Telegram.
 */
final class TelegramHandler extends AbstractHttpClientHandler
{
    private const TELEGRAM_API = 'https://api.telegram.org/bot';

    private $apiKey;

    private $chatId;

    /**
     * @param string $apiKey
     * @param int|string $chatId
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        string $apiKey,
        $chatId,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        $this->apiKey = $apiKey;
        $this->chatId = $chatId;
        parent::__construct($client, $requestFactory, $level, $bubble);
    }

    /**
     * Create request to @link https://api.telegram.org/bot on SendMessage action
     * @see https://core.telegram.org/bots/api#sendmessage
     *
     * @param array $record
     * @return RequestInterface
     */
    public function createRequest(array $record): RequestInterface
    {
        $uri = \sprintf('%s%s%s', self::TELEGRAM_API, $this->apiKey, '/sendMessage');
        $request = $this->requestFactory->createRequest('POST', $uri)->withHeader('Content-Type', ['application/json']);
        $body = [
            'chat_id' => $this->chatId,
            'text' => $record['formatted'],
        ];
        $jsonBody = \json_encode($body);
        if (false === $jsonBody) {
            throw new InvalidArgumentException('Could not format record to json');
        }

        $request->getBody()->write($jsonBody);
        $request->getBody()->rewind();

        return $request;
    }
}
