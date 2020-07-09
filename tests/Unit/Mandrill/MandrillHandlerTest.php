<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Mandrill;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Handler\HandlerInterface;
use MonologHttp\Mandrill\MandrillHandler;
use MonologHttp\Tests\Unit\HandlerTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class MandrillHandlerTest extends HandlerTestCase
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

    protected function createHandler(): HandlerInterface
    {
        return new MandrillHandler(
            $this->httpClient,
            new HttpFactory(),
            'apiuser',
            new \Swift_Message()
        );
    }
}
