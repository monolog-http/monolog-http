<?php

declare(strict_types=1);

namespace MonologHttp\Telegram;

use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class TelegramHandler extends AbstractHttpClientHandler
{
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
     *
     * @see https://core.telegram.org/bots/api#sendmessage
     */
    public function createRequest(array $record): RequestInterface
    {
        $content = \json_encode([
            'chat_id' => $this->chatId,
            'text' => $record['formatted'],
        ]);

        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Encoding json failed with reason: ' . \json_last_error_msg());
        }

        $uri = \sprintf('%s%s%s', 'https://api.telegram.org/bot', $this->apiKey, '/sendMessage');
        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json');

        /** @var string $content */
        $request->getBody()->write($content);
        $request->getBody()->rewind();

        return $request;
    }
}
