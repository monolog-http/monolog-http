<?php

declare(strict_types=1);

namespace MonologHttp\Sendgrid;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Sendgrid\Formatter\SendGridFormatterInterface;
use MonologHttp\Sendgrid\Formatter\SendgridHtmlFormatter;
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
     * @var string|null
     */
    private $subject;

    /**
     * @param string[] $to
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $apiUser,
        string $apiKey,
        string $from,
        array $to,
        ?string $subject = null,
        $level = Logger::ERROR,
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
        $request = $this->requestFactory->createRequest('POST', 'https://api.sendgrid.com/api/mail.send.json');
        $body = $request->getBody();
        $body->write(\http_build_query($record['formatted']));
        $body->rewind();

        return $request;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof SendGridFormatterInterface) {
            throw new \InvalidArgumentException(\sprintf('Expected an instance of %s', SendGridFormatterInterface::class));
        }

        return parent::setFormatter($formatter);
    }

    /**
     * @return SendgridHtmlFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new SendgridHtmlFormatter($this->apiUser, $this->apiKey, $this->from, $this->to, $this->subject);
    }
}
