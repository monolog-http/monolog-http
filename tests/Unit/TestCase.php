<?php

declare(strict_types=1);

namespace MonologHttp\Test\Unit;

use Monolog\Logger;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCase extends PhpUnitTestCase
{
    /**
     * @param int $level
     * @param string $message
     * @param array $context
     * @return array Record
     */
    protected function getRecord(int $level = Logger::WARNING, string $message = 'test', array $context = [])
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
