<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Formatter;

use Monolog\Logger;
use MonologHttp\Formatter\SlackLongAttachmentFormatter;
use MonologHttp\Tests\Unit\TestCase;

final class SlackLongAttachmentFormatterTest extends TestCase
{
    /**
     * @var int
     */
    private $jsonFlags;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonFlags = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
    }

    /**
     * @return array
     */
    public function dataGetAttachmentColor()
    {
        return [
            [Logger::DEBUG, '#cccccc'],
            [Logger::INFO, '#468847'],
            [Logger::NOTICE, '#3a87ad'],
            [Logger::WARNING, '#c09853'],
            [Logger::ERROR, '#f0ad4e'],
            [Logger::CRITICAL, '#FF7708'],
            [Logger::ALERT, '#C12A19'],
            [Logger::EMERGENCY, '#000000'],
        ];
    }

    /**
     * @test
     * @dataProvider dataGetAttachmentColor
     * @param int $logLevel
     * @param string $expectedColour
     */
    public function getAttachmentColor($logLevel, $expectedColour): void
    {
        $formatter = new SlackLongAttachmentFormatter();
        $data = $formatter->format($this->getRecord($logLevel));
        $this->assertSame($expectedColour, $data['attachments'][0]['color']);
    }

    /**
     * @test
     */
    public function noUsernameByDefault(): void
    {
        $data = $this->createFormatter()->format($this->getRecord());
        $this->assertArrayNotHasKey('username', $data);
    }

    /**
     * @test
     */
    public function addsCustomUsername(): void
    {
        $formatter = new SlackLongAttachmentFormatter('Monolog bot');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('username', $data);
        $this->assertSame('Monolog bot', $data['username']);
    }

    /**
     * @return array
     */
    public function dataGetEmojiProvider()
    {
        return [
            [':loudspeaker:'],
            [':information_source:'],
            [':exclamation:'],
            [':warning:'],
            [':x:'],
            [':rotating_light:'],
            [':fire:'],
            [':bomb:'],
        ];
    }

    /**
     * @test
     * @dataProvider dataGetEmojiProvider
     * @param string $expected
     */
    public function getEmojiIcon($expected): void
    {
        $data = $this->createFormatter(null, $expected)->format($this->getRecord(Logger::ALERT));
        $this->assertSame($expected, $data['icon_emoji']);
    }

    /**
     * @test
     */
    public function thatEmojiIconIsNotSend(): void
    {
        $data = $this->createFormatter()->format($this->getRecord());
        $this->assertArrayNotHasKey('icon_emoji', $data);
    }

    /**
     * @test
     */
    public function addsTimestampToAttachment(): void
    {
        $record = $this->getRecord();
        $data = $this->createFormatter()->format($record);

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('ts', $attachment);
        $this->assertInstanceOf(\DateTimeInterface::class, $record['datetime']);
        /** @var \DateTimeInterface $dt */
        $dt = $record['datetime'];
        $this->assertSame($dt->getTimestamp(), $attachment['ts']);
    }

    /**
     * @test
     */
    public function addsOneAttachment(): void
    {
        $data = $this->createFormatter()->format($this->getRecord());

        $this->assertArrayHasKey('attachments', $data);
        $this->assertArrayHasKey(0, $data['attachments']);
        $this->assertIsArray($data['attachments'][0]);
    }

    /**
     * @test
     */
    public function addsFallbackAndTextToAttachment(): void
    {
        $data = $this->createFormatter()->format($this->getRecord(Logger::WARNING, 'Test message'));

        $this->assertSame('Test message', $data['attachments'][0]['text']);
        $this->assertSame('Test message', $data['attachments'][0]['fallback']);
    }

    /**
     * @test
     */
    public function addsLongAttachmentWithoutContextAndExtra(): void
    {
        $formatter = new SlackLongAttachmentFormatter(null, 'ghost', false);
        $data = $formatter->format($this->getRecord(Logger::ERROR, 'test', ['test' => 1]));

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('title', $attachment);
        $this->assertArrayHasKey('fields', $attachment);
        $this->assertCount(0, $attachment['fields']);
        $this->assertSame('test.ERROR', $attachment['title']);
    }

    /**
     * @test
     */
    public function addsFieldDoesNotExceed2000Characters(): void
    {
        $trace = '';
        for ($i = 0; $i < 3000; $i++) {
            $trace .= (string)\mt_rand(1, 100);
        }

        $data = $this->createFormatter()
            ->format($this->getRecord(Logger::WARNING, 'Test message', ['exception_trace' => $trace]));
        $this->assertStringEndsWith('... (truncated)', $data['attachments'][0]['fields'][0]['value']);
    }

    /**
     * @test
     */
    public function addsInternalFieldDoesNotExceed2000Characters(): void
    {
        $trace = '';
        for ($i = 0; $i < 3000; $i++) {
            $trace .= (string)\mt_rand(1, 100);
        }

        $formatter = new SlackLongAttachmentFormatter();
        $data = $formatter->format($this->getRecord(Logger::WARNING, 'Test message', ['exception_trace' => [$trace]]));

        $this->assertStringEndsWith('... (truncated)```', $data['attachments'][0]['fields'][0]['value']);
    }

    /**
     * @test
     */
    public function addsCustomChannel(): void
    {
        $formatter = new SlackLongAttachmentFormatter(null, null, true, 'my-slack-channel');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('channel', $data);
        $this->assertSame('my-slack-channel', $data['channel']);
    }

    /**
     * @test
     */
    public function channelIsNotAddedByDefault(): void
    {
        $formatter = new SlackLongAttachmentFormatter();
        $data = $formatter->format($this->getRecord());

        $this->assertArrayNotHasKey('channel', $data);
    }

    /**
     * @param string|null $username
     * @param string $userIcon
     * @param bool $includeContextAndExtra
     * @return SlackLongAttachmentFormatter
     */
    private function createFormatter($username = null, string $userIcon = null, $includeContextAndExtra = true)
    {
        return new SlackLongAttachmentFormatter($username, $userIcon, $includeContextAndExtra);
    }
}
