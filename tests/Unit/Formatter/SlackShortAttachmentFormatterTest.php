<?php

declare(strict_types=1);

namespace MonologHttp\Tests\Unit\Formatter;

use Monolog\Logger;
use MonologHttp\Formatter\SlackShortAttachmentFormatter;
use MonologHttp\Tests\Unit\TestCase;

final class SlackShortAttachmentFormatterTest extends TestCase
{
    /**
     * @var int
     */
    private $jsonFlags;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonFlags = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
    }

    public function dataGetAttachmentColorProvider()
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
     * @dataProvider dataGetAttachmentColorProvider
     * @param int $logLevel
     * @param string $expectedColour
     */
    public function getAttachmentColor($logLevel, $expectedColour): void
    {
        $formatter = new SlackShortAttachmentFormatter();
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
        $data = $this->createFormatter('Monolog bot')->format($this->getRecord());
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
        $formatter = new SlackShortAttachmentFormatter();
        $data = $formatter->format($this->getRecord());
        $this->assertArrayNotHasKey('icon_emoji', $data);
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
    public function addsShortAttachmentWithoutContextAndExtra(): void
    {
        $level = Logger::ERROR;
        $levelName = Logger::getLevelName($level);
        $record = new SlackShortAttachmentFormatter(null, 'ghost', false);
        $data = $record->format($this->getRecord($level, 'test', ['test' => 1]));

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('title', $attachment);
        $this->assertArrayHasKey('fields', $attachment);
        $this->assertSame('test.' . $levelName, $attachment['title']);
        $this->assertSame([], $attachment['fields']);
    }

    /**
     * @test
     */
    public function addsFieldDoesNotExceed2000Characters(): void
    {
        $message = 'Test message';
        $record = new SlackShortAttachmentFormatter(null);
        $e = new \Exception();
        $trace = $e->getTraceAsString() . $e->getTraceAsString();
        $data = $record->format($this->getRecord(Logger::WARNING, $message, ['exception_trace' => $trace]));

        $this->assertStringEndsWith('... (truncated)```', $data['attachments'][0]['fields'][0]['value']);
    }

    /**
     * @test
     */
    public function addsShortAttachmentWithContextAndExtra(): void
    {
        $context = ['test' => 1];
        $extra = ['tags' => ['web']];
        $record = $this->getRecord(Logger::ERROR, 'test', $context);
        $record['extra'] = $extra;

        $data = $this->createFormatter()->format($record);

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('title', $attachment);
        $this->assertArrayHasKey('fields', $attachment);
        $this->assertCount(2, $attachment['fields']);
        $this->assertSame('test.ERROR', $attachment['title']);
        $this->assertSame(
            [
                [
                    'title' => '',
                    'value' => \sprintf('```%s```', \json_encode($context, $this->jsonFlags)),
                    'short' => false,
                ],
                [
                    'title' => '',
                    'value' => \sprintf('```%s```', \json_encode($extra, $this->jsonFlags)),
                    'short' => false,
                ],
            ],
            $attachment['fields']
        );
    }

    /**
     * @test
     */
    public function addsTimestampToAttachment(): void
    {
        $record = $this->getRecord();
        $formatter = new SlackShortAttachmentFormatter();
        $data = $formatter->format($record);

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('ts', $attachment);
        $this->assertInstanceOf(\DateTimeInterface::class, $record['datetime']);
        /** @var \DateTimeInterface $dt */
        $dt = $record['datetime'];
        $this->assertSame($dt->getTimestamp(), $attachment['ts']);
    }

    /**
     * @param string $userIcon
     * @return SlackShortAttachmentFormatter
     */
    private function createFormatter(
        string $username = null,
        string $userIcon = null,
        bool $includeContextAndExtra = true
    ) {
        return new SlackShortAttachmentFormatter($username, $userIcon, $includeContextAndExtra);
    }
}
