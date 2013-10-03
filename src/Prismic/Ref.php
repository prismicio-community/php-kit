<?php

namespace Prismic;

class Ref
{
    private $ref;
    private $label;
    private $isMasterRef;
    private $maybeScheduledAt;

    public function __construct($ref, $label, $isMasterRef, $maybeScheduledAt = null)
    {
        $this->ref = $ref;
        $this->label = $label;
        $this->isMasterRef = $isMasterRef;
        $this->maybeScheduledAt = $maybeScheduledAt;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public static function parse($json)
    {
        return new Ref(
            $json->ref,
            $json->label,
            isset($json->{'isMasterRef'}) ? $json->isMasterRef : false,
            isset($json->{'scheduledAt'}) ? $json->scheduledAt : null
        );
    }
}