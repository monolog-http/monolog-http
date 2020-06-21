<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class SendGridHandler extends AbstractHttpClientHandler
{
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
     * @var string[]
     */
    private $to;

    /**
     * @var string
     */
    private $subject;

    /**
     * @param string[] $to
     * @param int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $apiUser,
        string $apiKey,
        string $from,
        array $to,
        string $subject,
        int $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
    }

    protected function createRequest(array $record): RequestInterface
    {
        if ($this->isHtmlBody($record['formatted'])) {
            $message['html'] = $record['formatted'];
        } else {
            $message['text'] = $record['formatted'];
        }

        $message['api_user'] = $this->apiUser;
        $message['api_key'] = $this->apiKey;
        $message['from'] = $this->from;
        foreach ($this->to as $recipient) {
            $message['to[]'] = $recipient;
        }
        $message['subject'] = $this->subject;

        /** @var DateTimeImmutable $date */
        $date = $record['datetime'];
        $message['date'] = $date->format('r');

        $request = $this->requestFactory->createRequest('POST', 'https://api.sendgrid.com/api/mail.send.json');
        $body = $request->getBody();
        $body->write(\http_build_query($message));
        $body->rewind();

        return $request;
    }

    private function isHtmlBody(string $body): bool
    {
        return $body !== \strip_tags($body);
    }

    /**
     * @return HtmlFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new HtmlFormatter();
    }
}
