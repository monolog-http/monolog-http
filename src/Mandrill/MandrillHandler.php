<?php

declare(strict_types=1);

namespace MonologHttp\Mandrill;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class MandrillHandler extends AbstractHttpClientHandler
{
    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $apiKey A valid Mandrill API key
     * @param \Swift_Message $message An example message for real messages, only the body will be replaced
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $apiKey,
        \Swift_Message $message,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->message = $message;
        $this->apiKey = $apiKey;
    }

    protected function createRequest(array $record): RequestInterface
    {
        $formatted = $record['formatted'];
        $mime = 'text/plain';
        if ($this->isHtmlBody($formatted)) {
            $mime = 'text/html';
        }

        $message = clone $this->message;
        $message->setBody($formatted, $mime);
        $message->setDate(new \DateTimeImmutable());

        $request = $this->requestFactory->createRequest('POST', 'https://mandrillapp.com/api/1.0/messages/send-raw.json');
        $body = $request->getBody();
        $content = \http_build_query([
            'key' => $this->apiKey,
            'raw_message' => (string)$message,
            'async' => false,
        ]);

        $body->write($content);
        $body->rewind();

        return $request;
    }

    public function getDefaultFormatter(): FormatterInterface
    {
        return new HtmlFormatter();
    }

    private function isHtmlBody(string $body): bool
    {
        return $body !== \strip_tags($body);
    }
}
