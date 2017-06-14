<?php

namespace Prismic\Fragment;

class CompositeSlice implements FragmentInterface
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
     * Repeatable content
     * @var Group|null
     */
    private $repeat;

    /**
     * Non Repeatable Content
     * @var GroupDoc|null
     */
    private $nonRepeat;


    public function __construct($sliceType, $label, Group $repeat = null, GroupDoc $nonRepeat = null)
    {
        $this->sliceType = $sliceType;
        $this->label     = $label;
        $this->repeat    = $repeat;
        $this->nonRepeat = $nonRepeat;
    }

    public function asText()
    {
        $string = '';
        if ($this->nonRepeat) {
            foreach ($this->nonRepeat->getFragments() as $fragment) {
                $string .= $fragment->asText();
            }
        }
        if ($this->repeat) {
            foreach ($this->repeat as $subfragments) {
                foreach ($subfragments as $name => $fragment) {
                    $string .= $fragment->asText();
                }
            }
        }

        return $string;
    }

    public function asHtml($linkResolver = null)
    {
        $classes = array('slice');
        if ($this->label !== null) {
            array_push($classes, $this->label);
        }
        $markup = '';
        if ($this->nonRepeat) {
            foreach ($this->nonRepeat->getFragments() as $fragment) {
                $markup .= $fragment->asHtml($linkResolver);
            }
        }
        if ($this->repeat) {
            $markup .= $this->repeat->asHtml($linkResolver);
        }
        return sprintf(
            '<div data-slicetype="%s" class="%s">%s</div>',
            $this->sliceType,
            implode(' ', $classes),
            $markup
        );
    }

    /**
     * Returns the slice label as declared in the Document Mask.
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the slice type as declared in the Document Mask.
     *
     * @return string
     */
    public function getSliceType()
    {
        return $this->sliceType;
    }

    /**
     * Returns the non-repeatable fields
     *
     * @return string
     */
    public function getPrimary()
    {
        return $this->nonRepeat;
    }

    /**
     * Returns the repeatable fields
     *
     * @return string
     */
    public function getItems()
    {
        return $this->repeat;
    }

}
