<?php

declare(strict_types=1);

namespace MonologHttp\Twilio;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use MonologHttp\AbstractHttpClientHandler;
use MonologHttp\Twilio\Formatter\TwilioMessageFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class TwilioHandler extends AbstractHttpClientHandler
{
    /**
     * @var string
     */
    private $sid;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $fromNumber;

    /**
     * @var string
     */
    private $toNumber;

    /**
     * @param int|string $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $sid,
        string $token,
        string $fromNumber,
        string $toNumber,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($client, $requestFactory, $level, $bubble);
        $this->sid = $sid;
        $this->token = $token;
        $this->fromNumber = $fromNumber;
        $this->toNumber = $toNumber;
    }

    public function createRequest(array $record): RequestInterface
    {
        $content = \json_encode([
            'From' => $this->fromNumber,
            'To' => $this->toNumber,
            'Body' => $record['formatted'],
        ]);

        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('Encoding json failed with reason: ' . \json_last_error_msg());
        }

        $uri = \sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $this->sid);
        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', \sprintf('Basic %s', \base64_encode(\sprintf('%s:%s', $this->sid, $this->token))));

        /** @var string $content */
        $request->getBody()->write($content);
        $request->getBody()->rewind();

        return $request;
    }

    public function getDefaultFormatter(): FormatterInterface
    {
        return new TwilioMessageFormatter();
    }
}
