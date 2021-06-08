<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Predicates;

class PredicatesTest extends TestCase
{
    public function testAtPredicate(): void
    {
        $predicate = Predicates::at("document.type", "blog-post");
        $this->assertEquals('[:d = at(document.type, "blog-post")]', $predicate->q());
    }

    public function testNotPredicate(): void
    {
        $predicate = Predicates::not("document.type", "blog-post");
        $this->assertEquals('[:d = not(document.type, "blog-post")]', $predicate->q());
    }

    public function testAnyPredicate(): void
    {
        $p = Predicates::any("document.tags", ["Macaron", "Cupcakes"]);
        $this->assertEquals('[:d = any(document.tags, ["Macaron", "Cupcakes"])]', $p->q());
    }

    public function testHasPredicate(): void
    {
        $p = Predicates::has("my.article.author");
        $this->assertEquals('[:d = has(my.article.author)]', $p->q());
    }

    public function testNumberLT(): void
    {
        $p = Predicates::lt("my.product.price", 4.2);
        $this->assertEquals("[:d = number.lt(my.product.price, 4.2)]", $p->q());
    }

    public function testNumberInRange(): void
    {
        $p = Predicates::inRange("my.product.price", 2, 4);
        $this->assertEquals("[:d = number.inRange(my.product.price, 2, 4)]", $p->q());
    }

    public function testMonthAfter(): void
    {
        $p = Predicates::monthAfter("my.blog-post.publication-date", "April");
        $this->assertEquals('[:d = date.month-after(my.blog-post.publication-date, "April")]', $p->q());
    }

    public function testGeopointNear(): void
    {
        $p = Predicates::near("my.store.coordinates", 40.689757, -74.0451453, 15);
        $this->assertEquals("[:d = geopoint.near(my.store.coordinates, 40.689757, -74.0451453, 15)]", $p->q());
    }

    public function testBoolean(): void
    {
        $p = Predicates::at("my.product.promote", true);
        $this->assertEquals('[:d = at(my.product.promote, true)]', $p->q());

        $p = Predicates::at("my.product.promote", false);
        $this->assertEquals('[:d = at(my.product.promote, false)]', $p->q());
    }
}
