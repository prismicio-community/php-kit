<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

/**
 * Embodies a ref to be called on the prismic.io repository. The ref is a prismic.io
 * concept that represents a time on which you wish to query the repository, in the present (the
 * content that is live now, we call this ref the master ref) or in the future (the content that
 * is planned for a future content release).
 *
 * This is meant to be built during the unmarshaling of the /api document, and is not meant to
 * be used externally except for testing.
 *
 * @api
 */
class Ref
{
    /**
     * @var string the ID of the ref
     */
    private $ref;
    /**
     * @var string the display label of the ref
     */
    private $label;
    /**
     * @var boolean is the ref the master ref?
     */
    private $isMasterRef;
    /**
     * @var string the date and time at which the ref is scheduled, if it is
     */
    private $maybeScheduledAt;

    /**
     * Constructs a Ref object.
     *
     * @param string $ref              the ID of the ref
     * @param string $label            the display label of the ref
     * @param string $isMasterRef      is the ref the master ref?
     * @param string $maybeScheduledAt the date and time at which the ref is scheduled, if it is
     */
    public function __construct($ref, $label, $isMasterRef, $maybeScheduledAt = null)
    {
        $this->ref = $ref;
        $this->label = $label;
        $this->isMasterRef = $isMasterRef;
        $this->maybeScheduledAt = $maybeScheduledAt;
    }

    /**
     * Returns the ID of the ref
     *
     * @return string the ID of the ref
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Returns the display label of the ref
     *
     * @return string the display label of the ref
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Checks if the ref is the master ref
     *
     * @return boolean true if it is the master ref, false otherwise
     */
    public function isMasterRef()
    {
        return $this->isMasterRef;
    }

    /**
     * Returns the date and time at which the ref is scheduled, if it is
     *
     * @return string the date and time at which the ref is scheduled, if it is
     */
    public function getScheduledAt()
    {
        return $this->maybeScheduledAt;
    }

    /**
     * Returns the ref as a displayable information: the ref's ID.
     *
     * @return string the ref's ID
     */
    public function __toString()
    {
        return (string) $this->getRef();
    }

    /**
     * Parses a ref.
     *
     * @param  \stdClass   $json the json bit retrieved from the API that represents a ref.
     * @return \Prismic\Ref the manipulable object for that ref.
     */
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
