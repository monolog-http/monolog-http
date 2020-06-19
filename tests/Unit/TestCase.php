<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit;

use Monolog\Logger;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected function getRecord(int $level = Logger::WARNING, string $message = 'test', array $context = []): array
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
}
