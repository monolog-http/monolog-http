<?php

declare(strict_types=1);

namespace MonologHttp\Formatter;

use Monolog\Formatter\FormatterInterface;

final class SendgridFormatter implements FormatterInterface
{
    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $message = [];
        $message['api_user'] = $this->apiUser;
        $message['api_key'] = $this->apiKey;
        $message['from'] = $this->from;
        foreach ($this->to as $recipient) {
            $message['to[]'] = $recipient;
        }
        $message['subject'] = $this->subject;
        $message['date'] = date('r');

        if ($this->isHtmlBody($content)) {
            $message['html'] = $content;
        } else {
            $message['text'] = $content;
        }
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        // TODO: Implement formatBatch() method.
    }
}
