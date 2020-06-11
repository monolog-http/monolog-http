<?php

declare(strict_types=1);

namespace MonologHttp\Test\App\FooBar;

class TestStreamFoo
{
    public $foo;
    public $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->foo = 'BAR';
    }

    public function __toString()
    {
        \fseek($this->resource, 0);

        return $this->foo . ' - ' . (string)\stream_get_contents($this->resource);
    }
}
