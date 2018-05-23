<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class Date extends AbstractScalarFragment
{

    public function asDateTime() :? DateTimeInterface
    {
        /**
         * Date Fragments are always in the format Y-m-d, or in ISO 8601 format including UTC offset
         */
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $this->value);
        if ($date) {
            return $date;
        }

        $date = DateTimeImmutable::createFromFormat(DateTime::ISO8601, $this->value);
        return $date ? $date : null;
    }

    public function asHtml() :? string
    {
        $date = $this->asDateTime();
        if ($date) {
            return sprintf(
                '<time datetime="%s">%s</time>',
                $date->format('c'),
                $this->value
            );
        }
        return null;
    }
}
