<?php

declare(strict_types=1);

namespace MonologHttp\Sendgrid\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

final class SendgridTextFormatter extends AbstractSendGridFormatter
{
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }

    protected function appendBody(array $data, array $record): array
    {
        $data['text'] = $this->getFormatter()->format($record);
        return $data;
    }
}
