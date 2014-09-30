<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Predicates;

class PredicatesTest extends \PHPUnit_Framework_TestCase
{

    public function testAtPredicate()
    {
        $predicate = Predicates::at("document.type", "blog-post");
        $this->assertEquals('[:d = at(document.type, "blog-post")]', $predicate->q());
    }


    public function testAnyPredicate()
    {
        $p = Predicates::any("document.tags", array("Macaron", "Cupcakes"));
        $this->assertEquals('[:d = any(document.tags, ["Macaron", "Cupcakes"])]', $p->q());
    }

    public function testNumberLT()
    {
        $p = Predicates::lt("my.product.price", 4.2);
        $this->assertEquals("[:d = number.lt(my.product.price, 4.2)]", $p->q());
    }

    public function testNumberInRange()
    {
        $p = Predicates::inRange("my.product.price", 2, 4);
        $this->assertEquals("[:d = number.inRange(my.product.price, 2, 4)]", $p->q());
    }

    public function testMonthAfter()
    {
        $p = Predicates::monthAfter("my.blog-post.publication-date", "April");
        $this->assertEquals('[:d = date.month-after(my.blog-post.publication-date, "April")]', $p->q());
    }

    public function testGeopointNear()
    {
        $p = Predicates::near("my.store.coordinates", 40.689757, -74.0451453, 15);
        $this->assertEquals("[:d = geopoint.near(my.store.coordinates, 40.689757, -74.0451453, 15)]", $p->q());
    }

}
