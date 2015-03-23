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

/**
 * This class embodies a Slice fragment.
 */
class Slice implements FragmentInterface
{
    /**
     * @var fragment the inner value of the fragment
     */
    private $value;

    /**
     * Constructs a Slice object.
     *
     * @param string            $sliceType   the type of the slice as describe in the document mask
     * @param Prismic\Fragment  $value       the inner fragment
     */
    public function __construct($sliceType, $value)
    {
        $this->sliceType = $sliceType;
        $this->value = $value;
    }

    /**
     * Builds a HTML version of the Text fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Text fragment
     */
    public function asHtml($linkResolver = null)
    {
        return $this->value->asHtml($linkResolver);
    }

    /**
     * Builds a text version of the Slice fragment.
     *
     * @api
     *
     * @return string the text version of the Text fragment
     */
    public function asText()
    {
        return $this->value->asText();
    }

    /**
     * Returns the slice type as declared in the Document Mask.
     *
     * @api
     *
     * @return  fragment the inner value of the fragment
     */
    public function getSliceType()
    {
        return $this->sliceType;
    }

    /**
     * Returns the inner value of the fragment.
     *
     * @api
     *
     * @return  fragment the inner value of the fragment
     */
    public function getValue()
    {
        return $this->value;
    }
}
