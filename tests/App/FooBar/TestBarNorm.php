<?php

declare(strict_types=1);

namespace MonologHttp\Tests\App\FooBar;

class TestBarNorm
{
    public function __toString(): string
    {
        return 'bar';
    }
}
