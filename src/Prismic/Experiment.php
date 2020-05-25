<?php
declare(strict_types=1);

namespace Prismic;

use function array_map;

class Experiment
{
    /** @var string */
    private $id;

    /** @var string|null */
    private $googleId;

    /** @var string */
    private $name;

    /** @var Variation[] */
    private $variations;

    /** @param Variation[] $variations */
    private function __construct(string $id, ?string $googleId, string $name, array $variations)
    {
        $this->id         = $id;
        $this->googleId   = $googleId;
        $this->name       = $name;
        $this->variations = $variations;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getGoogleId() :? string
    {
        return $this->googleId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Variation[]
     */
    public function getVariations() : array
    {
        return $this->variations;
    }

    public static function parse(object $json) : self
    {
        $googleId = $json->googleId ?? null;
        $vars = array_map(static function (object $varJson) : Variation {
            return Variation::parse($varJson);
        }, $json->variations ?? []);

        return new static(
            $json->id,
            $googleId,
            $json->name,
            $vars
        );
    }
}
