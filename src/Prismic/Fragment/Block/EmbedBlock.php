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
 * This class embodies an Embed block inside a StructuredText fragment.
 * Since its features are strictly similar to an Embed fragment, it only contains an Embed fragment object.
 */
class EmbedBlock implements BlockInterface
{
    /**
     * @var \Prismic\Fragment\Embed the OEmbed object
     */
    private $obj;
    /**
     * @var string the label (optional, may be null)
     */
    private $label;

    /**
     * Constructs an OEmbed block from a OEmbed fragment.
     *
     * @param \Prismic\Fragment\Embed $obj the OEmbed fragment.
     * @param $label string
     */
    public function __construct($obj, $label = NULL)
    {
        $this->obj = $obj;
        $this->label = $label;
    }

    /**
     * Returns the OEmbed fragment to be manipulated.
     *
     * @api
     *
     * @return \Prismic\Fragment\Embed the OEmbed fragment.
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * Returns the label
     *
     * @api
     *
     * @return string the label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
