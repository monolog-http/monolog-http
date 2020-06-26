<?php

declare(strict_types=1);

namespace MonologHttp\Flowdock\Formatter;

abstract class AbstractFlowdockFormatter implements FlowdockFormatterInterface
{
    public function __construct(string $flowToken)
    {
        $this->setFlowToken($flowToken);
    }

    abstract protected function setFlowToken(string $flowToken): FlowdockFormatterInterface;
}
