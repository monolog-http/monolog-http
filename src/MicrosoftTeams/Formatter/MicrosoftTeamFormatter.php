<?php

declare(strict_types=1);

namespace MonologHttp\MicrosoftTeams\Formatter;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

final class MicrosoftTeamFormatter implements MicrosoftTeamFormatterInterface
{
    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false
    ) {
        $this->lineFormatter = new LineFormatter($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(array $record): array
    {
        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => $record['message'],
            'themeColor' => $this->getLevelColor($record['level']),
            'text' => $this->lineFormatter->format($record),
        ];
    }

    public function formatBatch(array $records): array
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * Returned a Slack message attachment color associated with
     * provided level.
     */
    private function getLevelColor(int $level): string
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
}
