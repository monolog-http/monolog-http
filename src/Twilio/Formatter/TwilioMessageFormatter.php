<?php

declare(strict_types=1);

namespace MonologHttp\Twilio\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * The goal of this formatter is to return only the message of the log.
 * SMSs need to have a limit.
 *
 * For different use cases create your custom formatter.
 */
final class TwilioMessageFormatter implements FormatterInterface
{
    public function format(array $record): string
    {
        return self::replaceNewLines($record['message']);
    }

    public function formatBatch(array $records): array
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    private static function replaceNewLines(string $str): string
    {
        return \str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }
}
