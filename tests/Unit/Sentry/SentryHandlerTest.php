<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Sentry;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Uri;
use Monolog\Logger;
use MonologHttp\Sentry\SentryHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class SentryHandlerTest extends TestCase
{
    /**
     * @var ClientInterface&MockObject
     */
    private $client;

    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $uri = new Uri('https://b70a3:b7d80@sentry.example.com/1/api/1/store');
        $handler = new SentryHandler(
            $this->client,
            new HttpFactory(),
            $uri,
            'sentryKey'
        );
        $this->logger = new Logger('test');
        $this->logger->pushHandler(
            $handler
        );
    }

    /**
     * @test
     */
    public function createRequest(): void
    {
        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $request): bool {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame(
                    'https://b70a3:b7d80@sentry.example.com/1/api/1/store',
                    $request->getUri()->__toString()
                );
                $data = \json_decode($request->getBody()->__toString(), true);
                $this->assertStringContainsString(
                    'fatal',
                    $data['level']
                );
                $xSentryAuthHeader = $request->getHeader('X-Sentry-Auth');
                $this->assertContains('Sentry sentry_version=7', $xSentryAuthHeader);
                $this->assertContains('sentry_key=sentryKey', $xSentryAuthHeader);

                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }
}
