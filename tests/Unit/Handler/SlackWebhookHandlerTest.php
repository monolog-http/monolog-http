<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Handler;

use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use MonologHttp\Formatter\SlackFormatterInterface;
use MonologHttp\Formatter\SlackShortAttachmentFormatter;
use MonologHttp\SlackWebhookHandler;
use MonologHttp\Tests\Unit\TestCase;

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
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * {@inheritdoc}
     */
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
        $this->assertAttributeSame($formatter, 'formatter', $this->handler);
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
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }));
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
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
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
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
        $handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function handlerDoesNotHandleTheRecord(): void
    {
        $this->client->expects($this->never())->method('sendRequest');
        $this->handler->handle($this->getRecord());
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
