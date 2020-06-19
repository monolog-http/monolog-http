<?php

declare(strict_types=1);

namespace MonologHttp\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use Throwable;

/**
 * This class contains all the useful functions that you can use in order to create a Formatter to send
 * logs to slack as attachments.
 */
abstract class AbstractSlackAttachmentFormatter extends NormalizerFormatter implements SlackFormatterInterface
{
    /**
     * Name of the bot
     *
     * @var string|null
     */
    private $username;

    /**
     * User icon e.g. 'ghost'
     *
     * @var string|null
     */
    private $emoji;

    /**
     * Whether the attachment should include context and extra data
     *
     * @var bool
     */
    private $includeContextAndExtra;

    /**
     * @var string|null
     */
    private $channel;

    /**
     * @param string|null $username The username of the bot.
     */
    public function __construct(
        ?string $username = null,
        ?string $emoji = null,
        bool $includeContextAndExtra = true,
        ?string $channel = null
    ) {
        parent::__construct();
        $this->username = $username;
        $this->emoji = $emoji !== null ? \trim($emoji, ':') : null;
        $this->includeContextAndExtra = $includeContextAndExtra;
        $this->channel = $channel;
    }

    public function format(array $record): array
    {
        $data = [];

        if ($this->username) {
            $data['username'] = $this->username;
        }

        if ($this->emoji !== null) {
            $data['icon_emoji'] = \sprintf(':%s:', $this->emoji);
        }

        if ($this->channel !== null) {
            $data['channel'] = $this->channel;
        }

        $attachment = [
            'fallback' => $record['message'],
            'text' => $record['message'],
            'color' => $this->getAttachmentColor($record['level']),
            'fields' => [],
            'mrkdwn_in' => ['fields'],
            'title' => $record['channel'] . '.' . $record['level_name'],
            'ts' => $record['datetime']->getTimestamp(),
        ];

        if ($this->includeContextAndExtra) {
            $attachment['fields'] = $this->createAttachmentFields($record);
        }

        $data['attachments'] = [$attachment];
        return $data;
    }

    /**
     * @return mixed
     */
    protected function normalize($data, int $depth = 0)
    {
        if ($data === null || \is_scalar($data)) {
            return $this->normalizeScalar($data);
        }

        if (\is_array($data) || $data instanceof \Traversable) {
            return $this->normalizeArray($data);
        }

        if (\is_object($data)) {
            return $this->normalizeObject($data);
        }

        if (\is_resource($data)) {
            return ['resource' => \get_resource_type($data)];
        }

        return $data;
    }

    protected function normalizeException(Throwable $e, int $depth = 0): array
    {
        return [
            'class' => \get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ];
    }

    /**
     * Returned a Slack message attachment color associated with
     * provided level.
     */
    protected function getAttachmentColor(int $level): string
    {
        $logLevels = [
            Logger::DEBUG => '#cccccc',
            Logger::INFO => '#468847',
            Logger::NOTICE => '#3a87ad',
            Logger::WARNING => '#c09853',
            Logger::ERROR => '#f0ad4e',
            Logger::CRITICAL => '#FF7708',
            Logger::ALERT => '#C12A19',
            Logger::EMERGENCY => '#000000',
        ];

        return $logLevels[$level];
    }

    protected function truncateStringIfNeeded(string $string): string
    {
        if (\strlen($string) > 1950) {
            $string = \substr($string, 0, 1900) . '... (truncated)';
        }

        return $string;
    }

    abstract protected function formatFields(array $record): array;

    /**
     * @return array|string
     */
    private function normalizeObject(object $data)
    {
        if ($data instanceof \DateTimeInterface) {
            return $data->format($this->dateFormat);
        }

        if ($data instanceof Throwable) {
            return $this->normalizeException($data);
        }

        $class = \get_class($data);

        if (\method_exists($data, '__toString')) {
            return [$class => $data->__toString()];
        }

        if ($data instanceof \JsonSerializable) {
            return [$class => $data->jsonSerialize()];
        }

        // the rest is json-serialized in some way
        /** @var string $string */
        $string = $this->toJson($data, true);
        $value = \json_decode($string, true);
        return [$class => $value];
    }

    /**
     * @param array|\Traversable $data
     */
    private function normalizeArray($data): array
    {
        $normalized = [];
        $count = 1;
        foreach ($data as $key => $value) {
            if ($count++ >= 1000) {
                $normalized['...'] = 'Over 1000 items, aborting normalization';
                break;
            }
            $normalized[$key] = $this->normalize($value);
        }

        return $normalized;
    }

    private function createAttachmentFields(array $record): array
    {
        $fields = [];
        foreach (['context', 'extra'] as $key) {
            if (empty($record[$key])) {
                continue;
            }

            $normalized = $this->normalize($record[$key]);
            $fields = \array_merge(
                $fields,
                $this->formatFields($normalized)
            );
        }

        return $fields;
    }

    /**
     * @param int|float|string|bool|null $data
     * @return int|float|string|bool|null
     */
    private function normalizeScalar($data)
    {
        if (\is_float($data)) {
            if (\is_infinite($data)) {
                return ($data > 0 ? '' : '-') . 'INF';
            }

            if (\is_nan($data)) {
                return 'NaN';
            }
        }

        return $data;
    }
}
