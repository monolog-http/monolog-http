<?php

declare(strict_types=1);

namespace MonologHttp;

use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class SendGridHandler extends AbstractHttpClientHandler
{
    /**
     * The SendGrid API User
     *
     * @var string
     */
    protected $apiUser;

    /**
     * The SendGrid API Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * The email addresses to which the message will be sent
     *
     * @var string
     */
    protected $from;

    /**
     * The email addresses to which the message will be sent
     *
     * @var array
     */
    protected $to;

    /**
     * The subject of the email
     *
     * @var string
     */
    protected $subject;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param string $apiUser The SendGrid API User
     * @param string $apiKey The SendGrid API Key
     * @param string $from The sender of the email
     * @param string|array $to The recipients of the email
     * @param string $subject The subject of the mail
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $apiUser,
        string $apiKey,
        string $from,
        $to,
        string $subject,
        int $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->from = $from;
        $this->to = (array)$to;
        $this->subject = $subject;
    }



    public function createRequest(array $record): RequestInterface
    {
        $body = json_encode($record['formatted']['flowdock']);
        if ($body === false) {
            throw new \InvalidArgumentException('Could not format record to json');
        };

        $request = $this->requestFactory->createRequest('POST', $this->uri);
        $request = $request->withHeader('Content-Type', ['application/json']);
        $request->getBody()->write($body);
        $request->getBody()->rewind();
        return $request;
    }
}
