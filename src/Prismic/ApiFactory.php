<?php

namespace Prismic;

class ApiFactory
{
    public function get(...$args): Api
    {
        return Api::get(...$args);
    }
}
