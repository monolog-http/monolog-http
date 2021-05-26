<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Twilio;

use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Handler\HandlerInterface;
use MonologHttp\Tests\Unit\HandlerTestCase;
use MonologHttp\Twilio\TwilioHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class TwilioHandlerTest extends HandlerTestCase
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
                $content = \json_decode($request->getBody()->__toString(), true);
                $this->assertSame('This is an error message', $content['Body']);
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
        return new TwilioHandler(
            $this->httpClient,
            new Psr17Factory(),
            'sid',
            'secret',
            '+35790909090',
            '+306988008000'
        );
    }
}
