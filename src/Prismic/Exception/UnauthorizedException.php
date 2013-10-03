<?php

namespace Prismic\Exception;


class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct("HTTP error: Unauthorized Exception", 0, null);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}