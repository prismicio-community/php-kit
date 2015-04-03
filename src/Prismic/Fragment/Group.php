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

use Prismic\Document;

/**
 * This class embodies a Group fragment.
 * To understand better how it works: each group contains several
 * sets of subfragments (as many as the content writer saw fit).
 * So, once you have your group, you have two layers to go through:
 * getting the set of subfragments you need (or, most often, looping
 * through all of them); then picking which subfragment from its name,
 * as defined in the JSON mask for this fragment.
 *
 * For instance: for($groupFragment->getArray() as $groupDoc) { echo $groupDoc.getText('subfragmentName'); }
 *
 * Each group doc can be manipulated like a regular Document.
 */
class Group implements FragmentInterface
{
    /**
     * @var array the array of associative arrays of subfragments
     */
    private $array;

    /**
     * Constructs a group fragment.
     *
     * @param string  $array         the array of associative arrays of subfragments
     */
    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Builds a HTML version of the Group fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Group fragment
     */
    public function asHtml($linkResolver = null)
    {
        $string = "";
        foreach ($this->array as $groupdoc) {
            $string .= '<div class="group-doc">';
            $string .= $groupdoc->asHtml($linkResolver);
            $string .= '</div>';
        }

        return $string;
    }

    /**
     * Builds a text version of the Group fragment.
     *
     * @api
     *
     * @return string the text version of the Group fragment
     */
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

    /**
     * Returns an array version of this group fragment, on which you can
     * do all you can do on an array: loop, access a certain index, ...
     *
     * Each item is an associative array of subfragments.
     *
     * @api
     *
     * @return array the array to loop on / access items / etc.
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * Parses a list of subfragments, and makes it into an associative array of fragments.
     * Not meant to be used except for testing.
     *
     * @param  \stdClass  $json the json bit retrieved from the API that represents the list of subfragments
     * @return array      the array of subfragments
     */
    public static function parseSubfragmentList($json)
    {
        $subfragments = array();
        foreach ($json as $subfragment_name => $subfragmentJson) {
            $subfragment = Document::parseFragment($subfragmentJson);
            if (isset($subfragment)) {
                $subfragments[$subfragment_name] = $subfragment;
            }
        }

        return new GroupDoc($subfragments);
    }

    /**
     * Parses a given Group fragment. Not meant to be used except for testing.
     *
     * @param  \stdClass                $json the json bit retrieved from the API that represents a Group fragment.
     * @return \Prismic\Fragment\Group  the manipulable object for that Group fragment.
     */
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
