<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Gitlab;

use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Handler\HandlerInterface;
use MonologHttp\Gitlab\GitlabHandler;
use MonologHttp\Tests\Unit\HandlerTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class GitlabHandlerTest extends HandlerTestCase
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
                $this->assertSame('www.gitlab.com', $request->getUri()->__toString());
                $data = \json_decode($request->getBody()->__toString(), true);
                $this->assertSame('This is an error message', $data['title']);
                $this->assertSame('critical', $data['level']);
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
        return new GitlabHandler(
            $this->httpClient,
            new Psr17Factory(),
            'www.gitlab.com',
            'authkey'
        );
    }
}
