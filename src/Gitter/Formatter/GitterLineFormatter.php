<?php

declare(strict_types=1);

namespace MonologHttp\Gitter\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

final class GitterLineFormatter implements FormatterInterface
{
    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = true
    ) {
        $this->lineFormatter = new LineFormatter($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(array $record): array
    {
        return [
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
}
