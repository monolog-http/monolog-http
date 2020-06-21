<?php

declare(strict_types=1);

namespace MonologHttp\Slack\Formatter;

/**
 * A Formatter that you can use in order to send to slack log message using the Attachment format.
 *
 * This Formatter will give the message of slack a Long format meaning that each key of the log context will be separate.
 */
final class SlackLongAttachmentFormatter extends AbstractSlackAttachmentFormatter
{

    protected function formatFields(array $record): array
    {
        $result = [];
        foreach ($record as $key => $value) {
            if (\is_array($value)) {
                /** @var string $string */
                $string = $this->toJson($value, true);
                $value = $this->truncateStringIfNeeded($string);

                $value = \sprintf('```%s```', $value);
                $result[] = [
                    'title' => $key,
                    'value' => $value,
                    'short' => false,
                ];
                continue;
            }

            $result[] = [
                'title' => $key,
                'value' => $this->truncateStringIfNeeded($value),
                'short' => false,
            ];
        }

        return $result;
    }
}
