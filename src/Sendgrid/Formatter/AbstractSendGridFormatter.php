<?php

declare(strict_types=1);

namespace MonologHttp\Sendgrid\Formatter;

use Monolog\DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;

abstract class AbstractSendGridFormatter implements SendGridFormatterInterface
{
    /**
     * @var FormatterInterface
     */
    private $defaultBodyFormatter;

    /**
     * @var FormatterInterface|null
     */
    private $bodyFormatter;

    /**
     * @var string
     */
    private $apiUser;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $from;

    /**
     * @var array<int, string>
     */
    private $to;

    /**
     * @var string|null
     */
    private $subject;

    public function __construct(string $apiUser, string $apiKey, string $from, array $to, ?string $subject = null)
    {
        $this->defaultBodyFormatter = $this->getDefaultFormatter();
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
    }

    public function setBodyFormatter(FormatterInterface $lineFormatter): void
    {
        $this->bodyFormatter = $lineFormatter;
    }

    public function format(array $record): array
    {
        $data['api_user'] = $this->apiUser;
        $data['api_key'] = $this->apiKey;
        $data['from'] = $this->from;
        foreach ($this->to as $recipient) {
            $data['to[]'] = $recipient;
        }
        $data['subject'] = $this->subject ?? $record['message'];

        /** @var DateTimeImmutable $date */
        $date = $record['datetime'];
        $data['date'] = $date->format('r');
        return $this->appendBody($data, $record);
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    protected function getFormatter(): FormatterInterface
    {
        return $this->bodyFormatter ?? $this->defaultBodyFormatter;
    }

    abstract protected function getDefaultFormatter(): FormatterInterface;

    abstract protected function appendBody(array $data, array $record): array;
}
