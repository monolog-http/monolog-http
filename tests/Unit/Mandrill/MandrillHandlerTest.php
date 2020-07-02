<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Mandrill;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Logger;
use MonologHttp\Mandrill\MandrillHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class MandrillHandlerTest extends TestCase
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
            new MandrillHandler(
                $this->httpClient,
                new HttpFactory(),
                'apiuser',
                new \Swift_Message()
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
                $this->assertSame('https://mandrillapp.com/api/1.0/messages/send-raw.json', $request->getUri()
                    ->__toString());
                $body = [];
                \parse_str($request->getBody()->__toString(), $body);
                $this->assertStringStartsWith('Message-ID: ', $body['raw_message']);
                $this->assertStringContainsString('Content-Type: text/html; charset=utf-8', $body['raw_message']);
                $this->assertStringContainsString('This is an error message', $body['raw_message']);
                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }
}
