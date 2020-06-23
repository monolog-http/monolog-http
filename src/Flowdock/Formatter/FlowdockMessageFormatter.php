<?php

declare(strict_types=1);

namespace MonologHttp\Flowdock\Formatter;

final class FlowdockMessageFormatter implements FlowdockFormatterInterface
{
    /**
     * @var string
     */
    private $eventType;

    /**
     * @var int|null
     */
    private $flowId;

    public function __construct(string $eventType = 'message', int $flowId = null)
    {
        $this->eventType = $eventType;
        $this->flowId = $flowId;
    }

    /*
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $data = [];

        if (!\is_null($this->flowId)) {
            $data['flow'] = $this->flowId;
        }

        $data['event'] = $this->eventType;

        $data['content'] = $record['message'];

        $tags = [
            '#logs',
            '#' . \strtolower($record['level_name']),
            '#' . $record['channel'],
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
