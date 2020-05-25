<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use JsonSerializable;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use function is_float;
use function sprintf;

class GeoPoint implements FragmentInterface, JsonSerializable
{
    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    private function __construct(float $lat, float $lng)
    {
        $this->latitude  = $lat;
        $this->longitude = $lng;
    }

    public static function factory(object $value) : self
    {
        if (isset($value->value)) {
            $value = $value->value;
        }

        $latitude = isset($value->latitude) ? (float) $value->latitude : null;
        $longitude = isset($value->longitude) ? (float) $value->longitude : null;

        if (! is_float($longitude) || ! is_float($latitude)) {
            throw new InvalidArgumentException(sprintf(
                'Expected an object containing latitude and longitude values, received: %s',
                Json::encode($value)
            ));
        }

        return new static($latitude, $longitude);
    }

    public function getLatitude() : float
    {
        return $this->latitude;
    }

    public function getLongitude() : float
    {
        return $this->longitude;
    }

    public function asHtml() :? string
    {
        return sprintf(
            '<span class="geopoint" data-latitude="%1$s" data-longitude="%2$s">%1$s, %2$s</span>',
            $this->latitude,
            $this->longitude
        );
    }

    public function asText() :? string
    {
        return sprintf('%f, %f', $this->latitude, $this->longitude);
    }

    /** @return float[] */
    public function jsonSerialize() : array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
