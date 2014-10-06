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

/**
 * A Document within a Group: doesn't correspond to a Prismic document, but a set of fragments.
 * For that reason it shares a lot of methods with Document through the shared parent class WithFragments
 *
 * @package Prismic\Fragment
 */
class GroupDoc extends WithFragments implements ArrayAccess
{

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $fragments = $this->getFragments();
        return $fragments[$offset];
    }

    /**
     * Offset to set
     * @sink http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
        // TODO: Implement offsetUnset() method.
    }


}
