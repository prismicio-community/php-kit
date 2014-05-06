<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

use Prismic\Document;

class Group implements FragmentInterface
{
    private $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    public function asHtml($linkResolver = null)
    {
        $string = "";
        foreach ($this->array as $subfragments) {
            foreach ($subfragments as $subfragment_name => $subfragment) {
                $string .= "<section data-field=\"$subfragment_name\">" .
                           $subfragment->asHtml($linkResolver) .
                           "</section>";
            }
        }

        return $string;
    }

    public function asText()
    {
        $string = "";
        foreach ($this->array as $subfragments) {
            foreach ($subfragments as $subfragment_name => $subfragment) {
                $string .= $subfragment->asText();
            }
        }

        return $string;
    }

    public function getArray()
    {
        return $this->array;
    }

    public static function parseSubfragmentList($json)
    {
        $subfragments = array();
        foreach ($json as $subfragment_name => $subfragmentJson) {
            $subfragment = Document::parseFragment($subfragmentJson);
            if (isset($subfragment)) {
                $subfragments[$subfragment_name] = $subfragment;
            }
        }

        return $subfragments;
    }

    public static function parse($json)
    {
        $array = array();
        foreach ($json as $subfragmentListJson) {
            $subfragmentList = Group::parseSubfragmentList($subfragmentListJson);
            if (isset($subfragmentList)) {
                array_push($array, $subfragmentList);
            }
        }

        return new Group($array);
    }
}
