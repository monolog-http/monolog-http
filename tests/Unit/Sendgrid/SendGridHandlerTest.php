<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Sendgrid;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologHttp\Sendgrid\SendGridHandler;
use MonologHttp\Tests\Unit\HandlerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class SendGridHandlerTest extends HandlerTestCase
{
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

    protected function createHandler(): HandlerInterface
    {
        return new SendGridHandler(
            $this->httpClient,
            new HttpFactory(),
            'apiuser',
            'apikey',
            'from@domain.com',
            ['to@domain.com'],
            'There was an error'
        );
    }
}
