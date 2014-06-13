<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Block;

/**
 * This class embodies an Image block inside a StructuredText fragment.
 * Since its features are strictly similar to an ImageView, it only contains an ImageView object.
 */
class ImageBlock implements BlockInterface
{
    /**
     * @var \Prismic\Fragment\ImageView the ImageView object describing the image.
     */
    private $view;

    /**
     * Constructs an Image block from a ImageView.
     *
     * @param \Prismic\Fragment\ImageView $view the ImageView.
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * Returns the ImageView to be manipulated.
     *
     * @api
     *
     * @return \Prismic\Fragment\ImageView the ImageView.
     */
    public function getView()
    {
        return $this->view;
    }
}
