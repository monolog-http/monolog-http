<?php

declare(strict_types=1);

namespace MonologHttp\Flowdock\Formatter;

final class FlowdockMessageFormatter extends AbstractFlowdockFormatter implements FlowdockFormatterInterface
{
    /**
     * @var string
     */
    private $flowToken;

    protected function setFlowToken(string $flowToken): FlowdockFormatterInterface
    {
        $this->flowToken = $flowToken;

        return $this;
    }

    /*
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $data = [];

        $data['flow_token'] = $this->flowToken;

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
