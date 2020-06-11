<?php

declare(strict_types=1);

namespace MonologHttp\Formatter;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;

/**
 * A simple formatter that you can use in order to send to slack log message.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
final class SlackLineFormatter extends NormalizerFormatter implements SlackFormatterInterface
{
    /**
     * Username to use as display for the webhook
     *
     * @var string|null
     */
    protected $username;

    /**
     * User icon e.g. 'ghost'
     *
     * @var string|null
     */
    protected $emoji;

    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    /**
     * @var string|null
     */
    private $channel;

    public function __construct(
        ?string $username = null,
        ?string $emoji = null,
        ?string $format = null,
        ?string $channel = null
    ) {
        parent::__construct();
        $format = $format ?: '%channel%.%level_name%: %message% %context% %extra%';
        $this->lineFormatter = new LineFormatter($format, null, false, true);
        $this->username = $username;
        $this->emoji = $emoji !== null ? \trim($emoji, ':') : null;
        $this->channel = $channel;
    }

    public function format(array $record): array
    {
        $data['text'] = $this->lineFormatter->format($record);

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        if ($this->emoji !== null) {
            $data['icon_emoji'] = \sprintf(':%s:', $this->emoji);
        }

        if ($this->channel !== null) {
            $data['channel'] = $this->channel;
        }

        return $data;
    }
}
