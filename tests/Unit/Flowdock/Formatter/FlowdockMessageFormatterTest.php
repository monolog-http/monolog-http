<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Flowdock\Formatter;

use MonologHttp\Flowdock\Formatter\FlowdockMessageFormatter;
use MonologHttp\Tests\Unit\TestCase;

final class FlowdockMessageFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function formatTheRecord(): void
    {
        $flowdockMessageFormatter = new FlowdockMessageFormatter('asecretflowtoken');
        $record = [
            'message' => 'This is an error message',
            'level_name' => 'critical',
            'extra' => [
                'tech',
            ],
        ];
        $expectedData = [
            'flow_token' => 'asecretflowtoken',
            'event' => 'message',
            'content' => 'This is an error message',
            'tags' => [
                '#logs', '#critical', '#tech',
            ]
        ];
        $data = $flowdockMessageFormatter->format($record);
        $this->assertEquals($expectedData, $data);
    }
}
