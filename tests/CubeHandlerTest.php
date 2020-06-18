<?php

declare(strict_types=1);

namespace MonologHttp\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Logger;
use MonologHttp\CubeHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class CubeHandlerTest extends TestCase
{
    /**
     * @var MockObject|ClientInterface
     */
    private $httpClient;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->logger = new Logger('test');
        $this->logger->pushHandler(new CubeHandler($this->httpClient, new HttpFactory(), 'www.mydomain.com'));
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
                $this->assertSame('body', $request->getBody()->__toString());
                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }
}
