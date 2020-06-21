<?php

declare(strict_types=1);

namespace MonologHttp;

use InvalidArgumentException;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

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
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $apiKey,
        $chatId,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->apiKey = $apiKey;
        $this->chatId = $chatId;
    }

    /**
     * Create request to @link https://api.telegram.org/bot on SendMessage action
     * @see https://core.telegram.org/bots/api#sendmessage
     */
    public function createRequest(array $record): RequestInterface
    {
        $uri = \sprintf('%s%s%s', self::TELEGRAM_API, $this->apiKey, '/sendMessage');
        $request = $this->requestFactory->createRequest('POST', $uri)->withHeader('Content-Type', ['application/json']);

        $jsonBody = \json_encode([
            'chat_id' => $this->chatId,
            'text' => $record['formatted'],
        ]);

        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Encoding json failed with reason: ' . \json_last_error_msg());
        }

        /** @var string $jsonBody */
        $request->getBody()->write($jsonBody);
        $request->getBody()->rewind();

        return $request;
    }
}
