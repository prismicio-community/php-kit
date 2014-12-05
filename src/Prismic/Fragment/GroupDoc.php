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

use ArrayAccess;
use Exception;
use Prismic\WithFragments;


class GroupDoc extends WithFragments implements ArrayAccess
{

    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < count($this->getFragments());
    }

    public function offsetGet($offset)
    {
        $fragments = $this->getFragments();
        return $fragments[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
        // TODO: Implement offsetUnset() method.
    }


}