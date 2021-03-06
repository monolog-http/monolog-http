<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Slack;

use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use MonologHttp\Slack\Formatter\SlackFormatterInterface;
use MonologHttp\Slack\Formatter\SlackLongAttachmentFormatter;
use MonologHttp\Slack\Formatter\SlackShortAttachmentFormatter;
use MonologHttp\Slack\SlackWebhookHandler;
use MonologHttp\Tests\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class SlackWebhookHandlerTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var SlackWebhookHandler
     */
    private $handler;

    /**
     * @var RequestFactoryInterface|MockObject
     */
    private $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->handler = new SlackWebhookHandler($this->client, $this->requestFactory, 'www.dummy.com');
    }

    /**
     * @test
     */
    public function setAcceptedFormatter(): void
    {
        $formatter = new SlackShortAttachmentFormatter();
        $this->handler->setFormatter($formatter);
        $this->assertInstanceOf(SlackShortAttachmentFormatter::class, $this->handler->getFormatter());

        $formatter = new SlackLongAttachmentFormatter();
        $this->handler->setFormatter($formatter);
        $this->assertInstanceOf(SlackLongAttachmentFormatter::class, $this->handler->getFormatter());
    }

    /**
     * @test
     */
    public function handlerWillHandleTheRecord(): void
    {
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value): bool {
                $body = $value->getBody()->__toString();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }));
        $this->handler->handle($this->createRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowException(): void
    {
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }))
            ->willThrowException(new \Exception());

        $this->expectException(\Exception::class);
        $this->handler->handle($this->createRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowExceptionWrappedIntoWhatFailureGroup(): void
    {
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }))
            ->willThrowException(new \Exception());

        $handler = new WhatFailureGroupHandler([$this->handler]);
        $handler->handle($this->createRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function handlerDoesNotHandleTheRecord(): void
    {
        $this->client->expects($this->never())->method('sendRequest');
        $this->handler->handle($this->createRecord());
    }

    /**
     * @test
     */
    public function setFormatterWillThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Expected an instance of %s', SlackFormatterInterface::class));
        $this->handler->setFormatter(new LineFormatter());
    }
}
