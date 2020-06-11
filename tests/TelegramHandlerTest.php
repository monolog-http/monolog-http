<?php

declare(strict_types=1);

namespace MonologHttp\Tests;

use MonologHttp\TelegramHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class TelegramHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function createRequest(): void
    {
        /** @var MockObject $mockClient */
        $mockClient = $this->createMock(ClientInterface::class);
        /** @var MockObject $mockRequestFactory */
        $mockRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $mockBody = $this->createMock(StreamInterface::class);
        $mockBody->expects($this->once())
            ->method('write')
            ->with('{"chat_id":1234,"text":"This is an error message"}');
        $mockBody->expects($this->once())->method('rewind');
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->expects($this->exactly(2))->method('getBody')->willReturn($mockBody);
        $mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://api.telegram.org/botTelegramApiKey/sendMessage')
            ->willReturn($mockRequest);
        $mockRequest->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', ['application/json'])
            ->willReturn($mockRequest);

        // @note workaround for the phpstan multiple types issue https://github.com/phpstan/phpstan-phpunit/issues/58
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $mockRequestFactory;
        /** @var ClientInterface $client */
        $client = $mockClient;

        $telegramHandler = new TelegramHandler(
            'TelegramApiKey',
            1234,
            $client,
            $requestFactory
        );
        $actualRequest = $telegramHandler->createRequest(
            ['formatted' => 'This is an error message']
        );

        $this->assertInstanceOf(RequestInterface::class, $actualRequest);
    }
}
