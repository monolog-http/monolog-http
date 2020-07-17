<?php

declare(strict_types=1);

namespace MonologHttp\Sentry\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

final class SentryFormatter implements FormatterInterface
{
    public function format(array $record)
    {
        $data = [
            'level' => $this->getSeverityFromLevel($record['level']),
            'message' => $record['message'],
            'logger' => 'monolog.' . $record['channel'],
        ];

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $data['exception'] = $record['context']['exception'];
        }

        $data['extra'] = [];
        $data['extra']['monolog.channel'] = $record['channel'];
        $data['extra']['monolog.level'] = $record['level_name'];
        if (isset($record['context']['extra']) && \is_array($record['context']['extra'])) {
            foreach ($record['context']['extra'] as $key => $value) {
                $data['extra'][$key] = $value;
            }
        }

        if (isset($record['context']['tags']) && \is_array($record['context']['tags'])) {
            $data['tags'] = [];
            foreach ($record['context']['tags'] as $key => $value) {
                $data['tags'][$key] = $value;
            }
        }

        return $data;
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    private function getSeverityFromLevel(int $level): string
    {
        switch ($level) {
            case Logger::DEBUG:
                return 'debug';
            case Logger::INFO:
            case Logger::NOTICE:
                return 'info';
            case Logger::WARNING:
                return 'warning';
            case Logger::ERROR:
                return 'error';
            case Logger::CRITICAL:
            case Logger::ALERT:
            case Logger::EMERGENCY:
                return 'fatal';
            default:
                return 'info';
        }
    }
}
