<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Psr\Http\Client\ClientInterface;

abstract class HandlerTestCase extends PhpUnitTestCase
{
    /**
     * @var MockObject|ClientInterface
     */
    protected $httpClient;

    /**
     * @var Logger
     */
    protected $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->logger = new Logger('test');
        $this->logger->pushHandler($this->createHandler());
    }

    protected function createRecord(int $level = Logger::WARNING, string $message = 'test', array $context = []): array
    {
        return [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => \DateTime::createFromFormat('U.u', \sprintf('%.6F', \microtime(true))),
            'extra' => [],
        ];
    }

    abstract protected function createHandler(): HandlerInterface;
}
