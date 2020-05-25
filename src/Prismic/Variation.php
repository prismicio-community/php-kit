<?php
declare(strict_types=1);

namespace Prismic;

class Variation
{
    /** @var string */
    private $id;

    /** @var string */
    private $ref;

    /** @var string */
    private $label;

    private function __construct(string $id, string $ref, string $label)
    {
        $this->id    = $id;
        $this->ref   = $ref;
        $this->label = $label;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getRef() : string
    {
        return $this->ref;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public static function parse(object $json) : self
    {
        return new Variation($json->id, $json->ref, $json->label);
    }
}
