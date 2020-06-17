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

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var int|string
     */
    private $chatId;

    /**
     * @param int|string $chatId
     * @param int|string $level
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
     */
    public function createRequest(array $record): RequestInterface
    {
        $uri = \sprintf('%s%s%s', self::TELEGRAM_API, $this->apiKey, '/sendMessage');
        $request = $this->requestFactory->createRequest('POST', $uri)->withHeader('Content-Type', ['application/json']);
        $body = [
            'chat_id' => $this->chatId,
            'text' => $record['formatted'],
        ];

        /** @var string $jsonBody */
        $jsonBody = \json_encode($body);
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new InvalidArgumentException(\json_last_error_msg());
        }

        $request->getBody()->write($jsonBody);
        $request->getBody()->rewind();

        return $request;
    }
}
