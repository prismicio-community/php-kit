<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

class Ref
{
    private $ref;
    private $label;
    private $isMasterRef;
    private $maybeScheduledAt;

    /**
     * @param string $ref
     * @param string $label
     * @param string $isMasterRef
     * @param string $maybeScheduledAt
     */
    public function __construct($ref, $label, $isMasterRef, $maybeScheduledAt = null)
    {
        $this->ref = $ref;
        $this->label = $label;
        $this->isMasterRef = $isMasterRef;
        $this->maybeScheduledAt = $maybeScheduledAt;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function isMasterRef()
    {
        return $this->isMasterRef;
    }

    public function getScheduledAt()
    {
        return $this->maybeScheduledAt;
    }

    public function __toString()
    {
        return (string) $this->getRef();
    }

    public static function parse($json)
    {
        return new Ref(
            $json->ref,
            $json->label,
            isset($json->{'isMasterRef'}) ? $json->isMasterRef : false,
            isset($json->{'scheduledAt'}) ? $json->scheduledAt : null    // @todo: convert value into \DateTime ?
        );
    }
}
