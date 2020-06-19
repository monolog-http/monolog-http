<?php

declare(strict_types=1);

namespace MonologHttp\Tests\App\FooBar;

class TestJsonSerializableNorm implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
