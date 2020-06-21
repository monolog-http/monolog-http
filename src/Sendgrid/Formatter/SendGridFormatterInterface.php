<?php

declare(strict_types=1);

namespace MonologHttp\Sendgrid\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * This is an Interface that all formatters must extend in order to be passed into SendGridHandler
 */
interface SendGridFormatterInterface extends FormatterInterface
{
}
