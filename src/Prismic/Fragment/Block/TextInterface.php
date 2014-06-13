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
 * This interface embodies any block of a StructuredText fragment that contains text.
 * Its known implementations are HeadingBlock, ListItemBlock, ParagraphBlock, and PreformattedBlock.
 */
interface TextInterface extends BlockInterface
{
    /**
     * Returns the unformatted text.
     *
     * @api
     *
     * @return string the unformatted text.
     */
    public function getText();
}
