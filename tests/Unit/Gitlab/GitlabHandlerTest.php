<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Gitlab;

use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Logger;
use MonologHttp\Gitlab\GitlabHandler;
use MonologHttp\Sendgrid\SendGridHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

final class GitlabHandlerTest extends TestCase
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
            new GitlabHandler(
                $this->httpClient,
                new HttpFactory(),
                'www.gitlab.com',
                'authkey'
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
}
