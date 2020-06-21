<?php

declare(strict_types=1);

namespace MonologHttp\Slack\Formatter;

/**
 * A Formatter that you can use in order to send to slack log message using the Attachment format.
 *
 * This Formatter will give the message a Short format meaning that all context will be put together.
 */
final class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    protected function formatFields(array $record): array
    {
        /** @var string $string */
        $string = $this->toJson($record, true);
        $value = $this->truncateStringIfNeeded($string);
        $value = \sprintf('```%s```', $value);
        return [
            [
                'title' => '',
                'value' => $value,
                'short' => false,
            ],
        ];
    }
}
