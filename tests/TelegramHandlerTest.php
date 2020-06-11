<?php

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
        $mockClient = $this->getMockBuilder(ClientInterface::class)->getMock();
        /** @var MockObject $mockRequestFactory */
        $mockRequestFactory = $this->getMockBuilder(RequestFactoryInterface::class)
            ->getMock();
        $mockBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $mockBody->expects($this->once())
            ->method('write')
            ->with('{"chat_id":1234,"text":"This is an error message"}');
        $mockBody->expects($this->once())->method('rewind');
        $mockRequest = $this->getMockBuilder(RequestInterface::class)->getMock();
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
