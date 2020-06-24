<?php

declare(strict_types=1);

namespace MonologHttp\Flowdock\Formatter;

final class FlowdockMessageFormatter implements FlowdockFormatterInterface
{
    /*
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $data = [];

        $data['event'] = 'message';

        $data['content'] = $record['message'];

        $tags = [
            '#logs',
            '#' . \strtolower($record['level_name'])
        ];
        foreach ($record['extra'] as $value) {
            $tags[] = '#' . $value;
        }
        $data['tags'] = $tags;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }
}
