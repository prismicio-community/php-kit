<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

/**
 * This class embodies a GeoPoint fragment.
 *
 * @api
 */
class GeoPoint implements FragmentInterface
{
    private $latitude;
    private $longitude;

    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Builds a HTML version of the geopoint.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the geopoint
     */
    public function asHtml($linkResolver = null)
    {
        return '<div class="geopoint"><span class="latitude">' . $this->latitude . '</span><span class="longitude">' . $this->longitude . '</span></div>';
    }

    public function asText()
    {
        return '(' . $this->latitude . ',' . $this->longitude . ')';
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function getLongitude() {
        return $this->longitude;
    }
}
