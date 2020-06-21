<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Sendgrid;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Logger;
use MonologHttp\Sendgrid\SendGridHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class SendGridHandlerTest extends TestCase
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
            new SendGridHandler(
                $this->httpClient,
                new HttpFactory(),
                'apiuser',
                'apikey',
                'from@domain.com',
                ['to@domain.com'],
                'There was an error'
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
                $body = [];
                \parse_str($request->getBody()->__toString(), $body);
                $this->assertSame('There was an error', $body['subject']);
                return true;
            }));

        $this->logger->log(LogLevel::CRITICAL, 'This is an error message', [
            'ctx1' => 'val1',
            'ctx2' => 'val2',
            'ctx3' => ['val3'],
        ]);
    }
}
