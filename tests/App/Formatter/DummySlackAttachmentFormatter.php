<?php

declare(strict_types=1);

namespace MonologHttp\Tests\App\Formatter;

use MonologHttp\Formatter\AbstractSlackAttachmentFormatter;

final class DummySlackAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    public function __construct()
    {
        parent::__construct();
        $this->dateFormat = 'Y-m-d H:i:s';
    }

    /**
     * @param array $record
     * @return array
     */
    protected function formatFields(array $record): array
    {
        return [$record];
    }
}
