<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Prismic\LinkResolver;

class Date extends AbstractScalarFragment
{

    /** @var string */
    private $format;

    /** @var string|null */
    protected $value;

    public static function factory($value, LinkResolver $linkResolver) : self
    {
        /** @var Date $fragment */
        $fragment = parent::factory($value, $linkResolver);
        $fragment->format = 'c';
        if (\preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', (string) $fragment->value)) {
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

        $date = DateTimeImmutable::createFromFormat(DateTime::ISO8601, (string) $this->value);
        return $date ? $date : null;
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
