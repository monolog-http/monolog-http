<?php

declare(strict_types=1);

namespace MonologHttp\Tests\App\FooBar;

class TestStreamFoo
{
    /**
     * @var string
     */
    public $foo;

    /**
     * @var resource
     */
    public $resource;

    /**
     * @param resource $resource
     */
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
