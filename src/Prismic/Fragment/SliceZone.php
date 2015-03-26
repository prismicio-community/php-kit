<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2015 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

use Prismic\Document;
use Prismic\WithFragments;

/**
 * This class embodies a Slice Zone.
 *
 */
class SliceZone implements FragmentInterface
{
    /**
     * @var array the array of slices
     */
    private $slices;

    /**
     * Constructs a SliceZone fragment.
     *
     * @param string  $slices         the array of associative arrays of subfragments
     */
    public function __construct($slices)
    {
        $this->slices = $slices;
    }

    /**
     * Builds a HTML version of the SliceZone fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the SliceZone fragment
     */
    public function asHtml($linkResolver = null)
    {
        $string = "";
        foreach ($this->slices as $slice) {
            $string .= $slice->asHtml($linkResolver);
        }

        return $string;
    }

    /**
     * Builds a text version of the SliceZone fragment.
     *
     * @api
     *
     * @return string the text version of the SliceZone fragment
     */
    public function asText()
    {
        $string = "";
        foreach ($this->slices as $slice) {
            $string .= $slice->asText();
        }

        return $string;
    }

    /**
     * Returns an array version of this group fragment, on which you can
     * do all you can do on an array: loop, access a certain index...
     *
     * Each item is a Slice.
     *
     * @api
     *
     * @return array the array to loop on / access items / etc.
     */
    public function getSlices()
    {
        return $this->slices;
    }

    /**
     * Parses a given SliceZone fragment. Not meant to be used except for testing.
     *
     * @param  \stdClass                $json the json bit retrieved from the API that represents a SliceZone fragment.
     * @return \Prismic\Fragment\SliceZone  the manipulable object for that SliceZone fragment.
     */
    public static function parse($json)
    {
        $slices = array();
        foreach ($json as $slicejson) {
            if (property_exists($slicejson, "slice_type") && property_exists($slicejson, "slice_type")) {
                if (property_exists($slicejson, "slice_label")) {
                    $label = $slicejson->slice_label;
                } else {
                    $label = null;
                }
                array_push($slices, new Slice($slicejson->slice_type, $label, WithFragments::parseFragment($slicejson->value)));
            }
        }

        return new SliceZone($slices);
    }
}
