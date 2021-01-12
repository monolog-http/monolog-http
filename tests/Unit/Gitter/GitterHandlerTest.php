<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Gitter;

use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Logger;
use MonologHttp\Gitter\GitterHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class GitterHandlerTest extends TestCase
{
    /**
     * @var MockObject|ClientInterface
     */
    private $httpClient;

    /**
     * @var Logger
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->logger = new Logger('test');
        $this->logger->pushHandler(
            new GitterHandler(
                $this->httpClient,
                new Psr17Factory(),
                'chat_id',
                'key'
            )
        );
    }

    /**
     * @test
     */
    public function createRequest(): void
    {
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $request): bool {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('https://api.gitter.im/v1/rooms/chat_id/chatMessages', $request->getUri()->__toString());
                $data = \json_decode($request->getBody()->__toString(), true);
                $this->assertStringContainsString('test.CRITICAL: This is an error message {"ctx1":"val1","ctx2":"val2","ctx3":["val3"]}', $data['text']);
                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }
}
