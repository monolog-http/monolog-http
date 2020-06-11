<?php

declare(strict_types=1);

namespace MonologHttp\Tests\App\FooBar;

class TestJsonSerializableNorm implements \JsonSerializable
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'foo' => 'bar',
        ];
    }
}
