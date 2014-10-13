<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Predicates;

class DocTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    public function testApi()
    {
// startgist:d496c58cd598372c4dca:prismic-api.php
        $api = Api::get("https://lesbonneschoses.prismic.io/api");
        $masterRef = $api->master();
// endgist
        $this->assertNotNull($api);
    }

    public function testSimpleQuery()
    {
// startgist:0b3cb9192c22e8f51159:prismic-simplequery.php
        $api = Api::get("https://lesbonneschoses.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(Predicates::at("document.type", "product"))
            ->ref($api->master()->getRef())
            ->submit();
        // $response contains all documents of type "product", paginated
// endgist
        $this->assertEquals(16, $response->getResultsSize());
    }

    public function testPredicates()
    {
// startgist:6a642512ec2225c35dae:prismic-predicates.php
        $api = Api::get("https://lesbonneschoses.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(array(
                Predicates::at("document.type", "product"),
                Predicates::at("my.blog-post.date", 1401580800000)))
            ->ref($api->master()->getRef())
            ->submit();
// endgist
        $this->assertEquals(0, $response->getResultsSize());
    }

    public function testAsHtml()
    {
        $api = Api::get("https://lesbonneschoses.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbX"))
            ->ref($api->master()->getRef())
            ->submit();
// startgist:a393f555bb9b55c40f8b:prismic-asHtml.php
        $results = $response->getResults();
        $doc = $results[0];
        // The resolver is defined here:
        // https://github.com/prismicio/php-kit/blob/master/tests/Prismic/FakeLinkResolver.php
        $resolver = new FakeLinkResolver();
        $html = $doc->getStructuredText("blog-post.body")->asHtml($resolver);
// endgist
        $this->assertNotNull($html);
    }

    public function testHtmlSerializer()
    {
// startgist:3263b52d6dc07b792d26:prismic-htmlSerializer.php
        $api = Api::get("https://lesbonneschoses.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbX"))
            ->ref($api->master()->getRef())
            ->submit();
        $results = $response->getResults();
        $doc = $results[0];
        // The resolver is defined here:
        // https://github.com/prismicio/php-kit/blob/master/tests/Prismic/FakeLinkResolver.php
        $resolver = new FakeLinkResolver();
        $htmlSerializer = function($element, $content) use ($resolver) {
            if ($element instanceof ImageBlock) {
                return nl2br($element->getView()->asHtml($resolver));
            }
            return null;
        };
        $html = $doc->getStructuredText("blog-post.body")->asHtml($resolver, $htmlSerializer);
// endgist
        $this->assertNotNull($html);
    }
}
