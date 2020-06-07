<?php

declare(strict_types=1);

namespace MonologHttp\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * This is an Interface that all formatters must extend in order to be passed into SlackWebhookHandler
 *
 * @author George Mponos <gmponos@gmail.com>
 */
interface SlackFormatterInterface extends FormatterInterface
{
}
