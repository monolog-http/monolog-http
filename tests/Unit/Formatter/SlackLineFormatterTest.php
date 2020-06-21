<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Formatter;

use Monolog\Logger;
use MonologHttp\Formatter\SlackLineFormatter;
use MonologHttp\Tests\Unit\TestCase;

final class SlackLineFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function noUsernameByDefault(): void
    {
        $record = new SlackLineFormatter();
        $data = $record->format($this->getRecord());
        $this->assertArrayNotHasKey('username', $data);
    }

    /**
     * @test
     */
    public function addsCustomUsername(): void
    {
        $formatter = new SlackLineFormatter('Monolog bot');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('username', $data);
        $this->assertSame('Monolog bot', $data['username']);
    }

    /**
     * @test
     */
    public function noIcon(): void
    {
        $formatter = new SlackLineFormatter(null);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayNotHasKey('icon_emoji', $data);
    }

    /**
     * @test
     */
    public function attachmentsNotPresent(): void
    {
        $formatter = new SlackLineFormatter();
        $data = $formatter->format($this->getRecord());
        $this->assertArrayNotHasKey('attachments', $data);
    }

    /**
     * @test
     */
    public function textEqualsFormatterOutput(): void
    {
        $formatter = new SlackLineFormatter();
        $data = $formatter->format($this->getRecord(Logger::WARNING, 'Test message'));

        $this->assertArrayHasKey('text', $data);
        $this->assertStringStartsWith('test.WARNING: Test message', $data['text']);
    }

    /**
     * @test
     * @dataProvider correctEmojiProvider
     */
    public function correctlyParsesEmoji(string $emoji, string $expectedEmoji): void
    {
        $formatter = new SlackLineFormatter(null, $emoji);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('icon_emoji', $data);
        $this->assertSame($expectedEmoji, $data['icon_emoji']);
    }

    public function correctEmojiProvider(): array
    {
        return [
            ['loudspeaker', ':loudspeaker:'],
            [':information_source', ':information_source:'],
            ['exclamation:', ':exclamation:'],
            [':warning:', ':warning:'],
        ];
    }

    /**
     * @test
     */
    public function noChannel(): void
    {
        $formatter = new SlackLineFormatter(null, null, null);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayNotHasKey('channel', $data);
    }

    /**
     * @test
     */
    public function hasChannel(): void
    {
        $formatter = new SlackLineFormatter(null, null, null, 'my-slack-channel');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('channel', $data);
        $this->assertSame('my-slack-channel', $data['channel']);
    }
}
