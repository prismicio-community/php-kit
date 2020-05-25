<?php
declare(strict_types=1);

namespace Prismic\Test;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Prismic\Exception\InvalidArgumentException;
use Prismic\SearchFormCollection;

class SearchFormCollectionTest extends TestCase
{
    public function testThatACallToSetWillThrowAnException() : void
    {
        $collection = new SearchFormCollection([]);
        $this->expectException(BadMethodCallException::class);
        $collection->foo = 'bar';
    }

    public function testExpectedValuesWithAnEmptyCollection() : void
    {
        $collection = new SearchFormCollection([]);
        $this->assertFalse($collection->hasForm('foo'));
        $this->assertNull($collection->getForm('foo'));
        $this->assertFalse(isset($collection->bar));
    }

    public function testACallToGetWillThrowExceptionForAFormThatDoesNotExist() : void
    {
        $collection = new SearchFormCollection([]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The search form named "foo" does not exist');
        $this->assertNull($collection->foo);
    }
}
