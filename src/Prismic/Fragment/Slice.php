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
class Slice implements SliceInterface
{

    /**
     * Slice Type as defined in Json
     * @var string
     */
    private $sliceType;

    /**
     * Slice Label
     * @var string|null
     */
    private $label;

    /**
     * the inner value of the fragment
     * @var FragmentInterface
     */
    private $value;

    /**
     * Constructs a Slice object.
     *
     * @param string            $sliceType   the type of the slice as describe in the document mask
     * @param string|null       $label  the optional label (may be null)
     * @param FragmentInterface $value       the inner fragment
     */
    public function __construct($sliceType, $label, FragmentInterface $value)
    {
        $this->sliceType = $sliceType;
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * Builds a HTML version of the Text fragment.
     *
     *
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Text fragment
     */
    public function asHtml($linkResolver = null)
    {
        $classes = array('slice');
        if ($this->label != null) array_push($classes, $this->label);
        return '<div data-slicetype="' . $this->sliceType . '" class="' . implode(' ', $classes)  . '">'
            . $this->value->asHtml($linkResolver)
            . '</div>';
    }

    /**
     * Builds a text version of the Slice fragment.
     *
     *
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
     *
     *
     * @return  fragment the inner value of the fragment
     */
    public function getSliceType()
    {
        return $this->sliceType;
    }

    /**
     * Returns the slice label as declared in the Document Mask.
     *
     *
     *
     * @return  fragment the inner value of the fragment
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the inner value of the fragment.
     *
     *
     *
     * @return FragmentInterface the inner value of the fragment
     */
    public function getValue()
    {
        return $this->value;
    }
}
