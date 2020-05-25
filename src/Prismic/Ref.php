<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeImmutable;
use Prismic\Exception\InvalidArgumentException;
use function floor;

class Ref
{
    /** @var string */
    private $id;

    /** @var string */
    private $ref;

    /** @var string */
    private $label;

    /** @var bool */
    private $isMasterRef;

    /**
     * The date and time at which the ref is scheduled, if it is
     *
     * @var int|null
     */
    private $maybeScheduledAt;

    /**
     * @param string $id               the ID of the release
     * @param string $ref              the ID of the ref
     * @param string $label            the display label of the ref
     * @param bool   $isMasterRef      is the ref the master ref?
     * @param int    $maybeScheduledAt If scheduled, a javascript timestamp in milliseconds otherwise null
     */
    private function __construct(
        string $id,
        string $ref,
        string $label,
        bool $isMasterRef,
        ?int $maybeScheduledAt
    ) {
        $this->id               = $id;
        $this->ref              = $ref;
        $this->label            = $label;
        $this->isMasterRef      = $isMasterRef;
        $this->maybeScheduledAt = $maybeScheduledAt;
    }

    /**
     * Returns the ID of the ref
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Returns the reference of the ref
     */
    public function getRef() : string
    {
        return $this->ref;
    }

    /**
     * Returns the display label of the ref
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * Checks if the ref is the master ref
     */
    public function isMasterRef() : bool
    {
        return $this->isMasterRef;
    }

    /**
     * Returns the time at which the ref is scheduled, if it is
     * This is a 13 digit Javascript timestamp including milliseconds
     */
    public function getScheduledAt() :? int
    {
        return $this->maybeScheduledAt;
    }

    /**
     * Return the scheduled time as a unix timestamp
     */
    public function getScheduledAtTimestamp() :? int
    {
        if ($this->maybeScheduledAt === null) {
            return null;
        }

        return (int) floor($this->maybeScheduledAt / 1000);
    }

    /**
     * Return the DateTime of the scheduled release if any
     */
    public function getScheduledDate() :? DateTimeImmutable
    {
        if ($this->maybeScheduledAt === null) {
            return null;
        }

        return DateTimeImmutable::createFromFormat(
            'U',
            (string) $this->getScheduledAtTimestamp()
        );
    }

    /**
     * Returns the ref as a displayable information: the ref's ID
     */
    public function __toString() : string
    {
        return $this->ref;
    }

    /**
     * @throws InvalidArgumentException if the JSON object has missing properties.
     */
    public static function parse(object $json) : self
    {
        if (! isset($json->id, $json->ref, $json->label)) {
            throw new InvalidArgumentException(
                'The properties id, ref and label should exist in the JSON object for a Ref'
            );
        }

        return new Ref(
            $json->id,
            $json->ref,
            $json->label,
            isset($json->{'isMasterRef'}) ? $json->isMasterRef : false,
            isset($json->{'scheduledAt'}) ? $json->scheduledAt : null
        );
    }
}
