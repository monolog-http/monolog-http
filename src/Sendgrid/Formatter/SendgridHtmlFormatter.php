<?php

declare(strict_types=1);

namespace MonologHttp\Sendgrid\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;

final class SendgridHtmlFormatter extends AbstractSendGridFormatter
{
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new HtmlFormatter();
    }

    protected function appendBody(array $data, array $record): array
    {
        $data['html'] = $this->getFormatter()->format($record);
        return $data;
    }
}
