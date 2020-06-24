<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Flowdock;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Uri;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use MonologHttp\Flowdock\FlowdockHandler;
use MonologHttp\Tests\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class FlowdockHandlerTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var FlowdockHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $uri = new Uri('https://api.example.com/messages');
        $this->handler = new FlowdockHandler($this->client, new HttpFactory(), $uri);
    }

    /**
     * @test
     */
    public function setNotFlowdockFormatterThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->handler->setFormatter(new LineFormatter());
    }

    /**
     * @test
     */
    public function handlerWillHandleTheRecord(): void
    {
        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $request) {
                $body = $request->getBody()->getContents();
                $this->assertStringContainsString('#alert', $body);
                return true;
            }));

        $this->handler->handle($this->getRecord(Logger::ALERT));
    }
}
