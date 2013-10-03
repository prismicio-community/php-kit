<?php

namespace Prismic\Exception;

class ForbiddenException extends \Exception
{
    public function __construct()
    {
        parent::__construct("HTTP error: Forbidden Exception", 0, null);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}