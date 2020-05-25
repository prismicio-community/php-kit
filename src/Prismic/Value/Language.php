<?php
declare(strict_types=1);

namespace Prismic\Value;

class Language
{
    /** @var string */
    private $languageCode;

    /** @var string */
    private $languageName;

    private function __construct(string $languageCode, string $languageName)
    {
        $this->languageCode = $languageCode;
        $this->languageName = $languageName;
    }

    public static function new(string $languageCode, string $languageName) : self
    {
        return new static($languageCode, $languageName);
    }

    public function code() : string
    {
        return $this->languageCode;
    }

    public function name() : string
    {
        return $this->languageName;
    }
}
