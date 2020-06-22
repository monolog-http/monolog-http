<?php

declare(strict_types=1);

namespace MonologHttp\Gitlab\Formatter;

use Monolog\DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

final class GitlabFormatter implements FormatterInterface
{
    /**
     * @var string|null
     */
    private $service;

    /**
     * @var array
     */
    private $hosts;

    /**
     * @var string|null
     */
    private $monitoringTool;

    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    public function __construct(
        string $service = null,
        string $monitoringTool = null,
        array $hosts = []
    ) {
        $this->lineFormatter = new LineFormatter(
            "%channel%.%level_name%: %message% %context% %extra%\n"
        );
        $this->service = $service;
        $this->hosts = $hosts;
        $this->monitoringTool = $monitoringTool;
    }

    public function format(array $record)
    {
        $data['title'] = $record['message'];
        $data['description'] = $this->lineFormatter->format($record);

        /** @var DateTimeImmutable $datetime */
        $datetime = $record['datetime'];
        $data['start_time'] = $datetime->format(\DateTime::ATOM);
        $data['level'] = $this->mapLevels($record['level']);

        if ($this->service !== null && $this->service !== '') {
            $data['service'] = $this->service;
        } else {
            $data['service'] = $record['channel'];
        }

        if ($this->monitoringTool !== null && $this->monitoringTool !== '') {
            $data['monitoring_tool'] = $this->monitoringTool;
        }

        if (count($this->hosts) > 0) {
            $data['hosts'] = $this->hosts;
        }

        return $data;
    }

    public function formatBatch(array $records): array
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    private function mapLevels(int $level): string
    {
        switch ($level) {
            case Logger::CRITICAL:
                return 'critical';
            case Logger::ERROR:
                return 'high';
            case Logger::WARNING:
                return 'medium';
            case Logger::NOTICE:
                return 'low';
            case Logger::INFO:
                return 'info';
            default:
                return 'unknown';
        }
    }
}
