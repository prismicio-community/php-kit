<?php

namespace Prismic\Fragment;

interface SliceInterface extends FragmentInterface
{

    /**
     * Returns the slice type as declared in the Document Mask.
     *
     * @return string
     */
    public function getSliceType();

    /**
     * Returns the slice label as declared in the Document Mask.
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Whether the slice is composite or not
     *
     * @return bool
     */
    public function isComposite();

}
