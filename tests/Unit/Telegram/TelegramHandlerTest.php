<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Telegram;

use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Handler\HandlerInterface;
use MonologHttp\Telegram\TelegramHandler;
use MonologHttp\Tests\Unit\HandlerTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class TelegramHandlerTest extends HandlerTestCase
{
    /**
     * @test
     */
    public function createRequest(): void
    {
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $request): bool {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('https://api.telegram.org/botTelegramApiKey/sendMessage', $request->getUri()->__toString());
                $data = \json_decode($request->getBody()->__toString(), true);
                $this->assertSame('1234', $data['chat_id']);
                $this->assertStringContainsString('This is an error message', $data['text']);
                $this->assertStringContainsString('test.CRITICAL', $data['text']);
                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }

    protected function createHandler(): HandlerInterface
    {
        return new TelegramHandler(
            $this->httpClient,
            new Psr17Factory(),
            'TelegramApiKey',
            '1234'
        );
    }
}
