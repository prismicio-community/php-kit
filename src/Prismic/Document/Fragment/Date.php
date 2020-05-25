<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use function preg_match;
use function sprintf;

class Date extends AbstractScalarFragment
{
    /** @var string */
    private $format;

    /** @var string|null */
    protected $value;

    /** @inheritDoc */
    public static function factory($value) : self
    {
        $fragment = parent::factory($value);
        $fragment->format = 'c';
        if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', (string) $fragment->value)) {
            $fragment->format = 'Y-m-d';
        }

        return $fragment;
    }

    public function asDateTime() :? DateTimeInterface
    {
        /**
         * Date Fragments are always in the format Y-m-d, or in ISO 8601 format including UTC offset
         */
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $this->value);
        if ($date) {
            return $date;
        }

        $date = DateTimeImmutable::createFromFormat(DateTime::ATOM, (string) $this->value);

        return $date ?: null;
    }

    public function asHtml() :? string
    {
        $date = $this->asDateTime();
        if ($date) {
            return sprintf(
                '<time datetime="%s">%s</time>',
                $date->format($this->format),
                $this->value
            );
        }

        return null;
    }
}
