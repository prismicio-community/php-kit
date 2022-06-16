<?php

namespace Prismic\Fragment;

trait MagicTrait
{
    public function __construct(
        private readonly \stdClass $content
    ) {
    }

    public function __isset(string $name): bool
    {
        return isset($this->content->{$name});
    }

    public function __get(string $name)
    {
        return $this->content->{$name};
    }

    public function __set(string $name, $value): void
    {
        $this->content->{$name} = $value;
    }
}
