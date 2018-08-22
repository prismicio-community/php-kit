<?php
declare(strict_types=1);

namespace Prismic;

use stdClass;

class Variation
{
    /**
     * Variation ID
     * @var string
     */
    private $id;

    /**
     * Variation Release Ref
     * @var string
     */
    private $ref;

    /**
     * Variation Label
     * @var string
     */
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

    public static function parse(stdClass $json) : self
    {
        return new Variation($json->id, $json->ref, $json->label);
    }
}
