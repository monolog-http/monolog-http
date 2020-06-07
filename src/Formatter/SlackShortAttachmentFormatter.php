<?php

declare(strict_types=1);

namespace MonologHttp\Formatter;

/**
 * A Formatter that you can use in order to send to slack log message using the Attachment format.
 *
 * This Formatter will give the message a Short format meaning that all context will be put together.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
final class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    protected function formatFields(array $record): array
    {
        $value = $this->truncateStringIfNeeded($this->toJson($record, true));
        $value = sprintf('```%s```', $value);
        return [
            [
                'title' => '',
                'value' => $value,
                'short' => false,
            ],
        ];
    }
}
