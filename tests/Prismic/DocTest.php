<?php

namespace Prismic\Test;

use DateTime;
use Ivory\HttpAdapter\HttpAdapterException;
use Prismic\Api;
use Prismic\Cache\ApcCache;
use Prismic\Document;
use Prismic\Predicates;

class DocTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    public function testApi()
    {
        // startgist:92d13d20c32f20771fd4:prismic-api.php
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $masterRef = $api->master();
        // endgist
        $this->assertNotNull($api);
    }

    public function testApiPrivate()
    {
        try {
            // startgist:770d45dd11dcd3758d45:prismic-apiPrivate.php
            $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api", "MC5-XXXXXXX-vRfvv70");
            // This will fail because the token is invalid, but this is how to access a private API
            // endgist
            $this->fail('The API->get call should have thrown');
        } catch (HttpAdapterException $e) {
            $this->assertEquals($e->getResponse()->getStatusCode(), 401);
        }
    }

    public function testApiReferences()
    {
        // startgist:194ff19cce10a0efc06c:prismic-references.php
        $previewToken = 'MC5VbDdXQmtuTTB6Z0hNWHF3.c--_vVbvv73vv73vv73vv71EA--_vS_vv73vv70T77-9Ke-_ve-_vWfvv70ebO-_ve-_ve-_vQN377-9ce-_vRfvv70';
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api", $previewToken);
        $stPatrickRef = $api->getRef("St-Patrick specials");
        // Now we'll use this reference for all our calls
        $response = $api
            ->forms()->everything
            ->ref($stPatrickRef)
            ->query(Predicates::at("document.type", "product"))
            ->submit();
        // The documents object contains a Response object with all documents of type "product"
        // including the new "Saint-Patrick's Cupcake"
        // endgist
        $this->assertEquals(17, $response->getResultsSize());
    }

    public function testSimpleQuery()
    {
        // startgist:fcf889892537d1db5601:prismic-simplequery.php
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $response = $api
            ->forms()->everything
            ->query(Predicates::at("document.type", "product"))
            ->ref($api->master())
            ->submit();
        // $response contains all documents of type "product", paginated
        // endgist
        $this->assertEquals(16, $response->getResultsSize());
    }

    public function testOrderings()
    {
    // startgist:b69742d9a4141e1ddc84:prismic-orderings.php
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $response = $api
            ->forms()->everything
            ->ref($api->master())
            ->query(Predicates::at("document.type", "product"))
            ->pageSize(100)
            ->orderings('[my.product.price desc]')
            ->submit();
        // The products are now ordered by price, highest first
        $results = $response->getResults();
        // endgist
        $this->assertEquals(100, $response->getResultsPerPage());
    }

    public function testPredicates()
    {
        // startgist:00f2eea266ee1557e6fc:prismic-predicates.php
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(
                Predicates::at("document.type", "product"),
                Predicates::at("my.blog-post.date", 1401580800000))
            ->ref($api->master()->getRef())
            ->submit();
        // endgist
        $this->assertEquals(0, $response->getResultsSize());
    }

    public function testAllPredicates()
    {
        // startgist:7a6da96d9e6f94269057:prismic-allPredicates.php
        // "at" predicate: equality of a fragment to a value.
        $at = Predicates::at("document.type", "article");
        // "any" predicate: equality of a fragment to a value.
        $any = Predicates::any("document.type", array("article", "blog-post"));
        // "has" predicate: field is defined
        $has = Predicates::has("my.article.author");
        // "missing" predicate: field is empty
        $missing = Predicates::missing("my.article.author");

        // "fulltext" predicate: fulltext search in a fragment.
        $fulltext = Predicates::fulltext("my.article.body", "sausage");

        // "similar" predicate, with a document id as reference
        $similar = Predicates::similar("UXasdFwe42D", 10);
        // endgist
    }

    public function testAsHtml()
    {
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbX"))
            ->ref($api->master()->getRef())
            ->submit();
        // startgist:d20fd1ff6d35d596ac63:prismic-asHtml.php
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
        // startgist:df627c93ba2b303d7566:prismic-htmlSerializer.php
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $response = $api
            ->forms()
            ->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbX"))
            ->ref($api->master())
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

    public function testGetText()
    {
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $documents = $api
            ->forms()->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbl"))
            ->ref($api->master())
            ->submit()
            ->getResults();
        $doc = $documents[0];
        // startgist:9b3e2339057f78098a9e:prismic-getText.php
        $author = $doc->getText("blog-post.author");
        // endgist
        $this->assertEquals($author, "John M. Martelle, Fine Pastry Magazine");
    }

    public function testGetNumber()
    {
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $documents = $api
            ->forms()->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbO"))
            ->ref($api->master())
            ->submit()
            ->getResults();

        $doc = $documents[0];
        // startgist:3bad4be2b4110ade4f85:prismic-getNumber.php
        // Number predicates
        $gt = Predicates::gt("my.product.price", 10);
        $lt = Predicates::lt("my.product.price", 20);
        $inRange = Predicates::inRange("my.product.price", 10, 20);

        // Accessing number fields
        $price = $doc->getNumber("product.price")->getValue();
        // endgist
        $this->assertEquals(2.5, $price);
    }

    public function testDateTimestamp()
    {
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $results = $api
            ->forms()->everything
            ->query(Predicates::at("document.id", "UlfoxUnM0wkXYXbl"))
            ->ref($api->master())->submit()->getResults();
        $doc = $results[0];
        // startgist:c6a5d3d570a585ea6941:prismic-dateTimestamp.php
        // Date and Timestamp predicates
        $dateBefore = Predicates::dateBefore("my.product.releaseDate", new DateTime('2014-6-1'));
        $dateAfter = Predicates::dateAfter("my.product.releaseDate", new DateTime('2014-1-1'));
        $dateBetween = Predicates::dateBetween("my.product.releaseDate", new DateTime('2014-1-1'), new DateTime('2014-6-1'));
        $dayOfMonth = Predicates::dayOfMonth("my.product.releaseDate", 14);
        $dayOfMonthAfter = Predicates::dayOfMonthAfter("my.product.releaseDate", 14);
        $dayOfMonthBefore = Predicates::dayOfMonthBefore("my.product.releaseDate", 14);
        $dayOfWeek = Predicates::dayOfWeek("my.product.releaseDate", "Tuesday");
        $dayOfWeekAfter = Predicates::dayOfWeekAfter("my.product.releaseDate", "Wednesday");
        $dayOfWeekBefore = Predicates::dayOfWeekBefore("my.product.releaseDate", "Wednesday");
        $month = Predicates::month("my.product.releaseDate", "June");
        $monthBefore = Predicates::monthBefore("my.product.releaseDate", "June");
        $monthAfter = Predicates::monthAfter("my.product.releaseDate", "June");
        $year = Predicates::year("my.product.releaseDate", 2014);
        $hour = Predicates::hour("my.product.releaseDate", 12);
        $hourBefore = Predicates::hourBefore("my.product.releaseDate", 12);
        $hourAfter = Predicates::hourAfter("my.product.releaseDate", 12);

        // Accessing Date and Timestamp fields
        $date = $doc->getDate("blog-post.date");
        $dateYear = $date->asDateTime()->format('Y');
        $updateTime = $doc->getTimestamp("blog-post.update");
        if ($updateTime) {
            $updateHour = $updateTime->asDateTime()->format('H');
        }
        // endgist
        $this->assertEquals($dateYear, '2013');
    }

    public function testGroup() {
        $json = "{\"id\":\"abcd\",\"type\":\"article\",\"href\":\"\",\"slugs\":[],\"tags\":[],\"data\":{\"article\":{\"documents\":{\"type\":\"Group\",\"value\":[{\"linktodoc\":{\"type\":\"Link.document\",\"value\":{\"document\":{\"id\":\"UrDejAEAAFwMyrW9\",\"type\":\"doc\",\"tags\":[],\"slug\":\"installing-meta-micro\"},\"isBroken\":false}},\"desc\":{\"type\":\"StructuredText\",\"value\":[{\"type\":\"paragraph\",\"text\":\"A detailed step by step point of view on how installing happens.\",\"spans\":[]}]}},{\"linktodoc\":{\"type\":\"Link.document\",\"value\":{\"document\":{\"id\":\"UrDmKgEAALwMyrXA\",\"type\":\"doc\",\"tags\":[],\"slug\":\"using-meta-micro\"},\"isBroken\":false}}}]}}}}";
        $doc = Document::parse(json_decode($json));
        // startgist:71569b416f5f625884b2:prismic-group.php
        $group = $doc->getGroup("article.documents");
        $docs = array();
        if ($group) {
            $docs = $group->getArray();
        }
        foreach ($docs as $doc) {
            // GroupDoc can be manipulated like regular documents
            $desc = $doc->getText("desc");
            $link = $doc->getLink("linktodoc");
        }
        // endgist
        $html = $docs[0]["desc"]->asHtml();
        $this->assertEquals("<p>A detailed step by step point of view on how installing happens.</p>", $html);
    }

    public function testLink() {
        $json = "{\"id\":\"abcd\",\"type\":\"article\",\"href\":\"\",\"slugs\":[],\"tags\":[],\"data\":{\"article\":{\"source\":{\"type\":\"Link.document\",\"value\":{\"document\":{\"id\":\"UlfoxUnM0wkXYXbE\",\"type\":\"product\",\"tags\":[\"Macaron\"],\"slug\":\"dark-chocolate-macaron\"},\"isBroken\":false}}}}}";
        $doc = Document::parse(json_decode($json));
        // startgist:d155c5360e9dfafc64da:prismic-link.php
        // The resolver is defined here:
        // https://github.com/prismicio/php-kit/blob/master/tests/Prismic/FakeLinkResolver.php
        $resolver = new FakeLinkResolver();
        $source = $doc->getLink("article.source");
        $url = "";
        if ($source) {
            $url = $source->getUrl($resolver);
        }
        // endgist
        $this->assertEquals($url, "http://host/doc/UlfoxUnM0wkXYXbE");
    }

    public function testEmbed() {
        $json = "{\"id\":\"abcd\",\"type\":\"article\",\"href\":\"\",\"slugs\":[],\"tags\":[],\"data\":{\"article\":{\"video\":{\"type\":\"Embed\",\"value\":{\"oembed\":{\"provider_url\":\"http://www.youtube.com/\",\"type\":\"video\",\"thumbnail_height\":360,\"height\":270,\"thumbnail_url\":\"http://i1.ytimg.com/vi/baGfM6dBzs8/hqdefault.jpg\",\"width\":480,\"provider_name\":\"YouTube\",\"html\":\"<iframe width=\\\"480\\\" height=\\\"270\\\" src=\\\"http://www.youtube.com/embed/baGfM6dBzs8?feature=oembed\\\" frameborder=\\\"0\\\" allowfullscreen></iframe>\",\"author_name\":\"Siobhan Wilson\",\"version\":\"1.0\",\"author_url\":\"http://www.youtube.com/user/siobhanwilsonsongs\",\"thumbnail_width\":480,\"title\":\"Siobhan Wilson - All Dressed Up\",\"embed_url\":\"https://www.youtube.com/watch?v=baGfM6dBzs8\"}}}}}}";
        $doc = Document::parse(json_decode($json));
        // startgist:8ff313944f2731a168ba:prismic-embed.php
        $video = $doc->getEmbed("article.video");
        // Html is the code to include to embed the object, and depends on the embedded service
        $html = $video->asHtml();
        // endgist
        $this->assertEquals("<div data-oembed=\"https://www.youtube.com/watch?v=baGfM6dBzs8\" data-oembed-type=\"video\" data-oembed-provider=\"youtube\"><iframe width=\"480\" height=\"270\" src=\"http://www.youtube.com/embed/baGfM6dBzs8?feature=oembed\" frameborder=\"0\" allowfullscreen></iframe></div>", $html);
    }

    public function testColor() {
        $json = "{\"id\":\"abcd\",\"type\":\"article\",\"href\":\"\",\"slugs\":[],\"tags\":[],\"data\":{\"article\":{\"background\":{\"type\":\"Color\",\"value\":\"#000000\"}}}}";
        $doc = Document::parse(json_decode($json));
        // startgist:074c0e5b84fd12593541:prismic-color.php
        $bgcolor = $doc->getColor("article.background")->asText();
        // endgist
        $this->assertEquals("#000000", $bgcolor);
    }

    public function testGeoPoint() {
        $json = "{\"id\":\"abcd\",\"type\":\"article\",\"href\":\"\",\"slugs\":[],\"tags\":[],\"data\":{\"article\":{\"location\":{\"type\":\"GeoPoint\",\"value\":{\"latitude\":48.877108,\"longitude\":2.333879}}}}}";
        $doc = Document::parse(json_decode($json));
        // startgist:4a4024327fc8454bc809:prismic-geopoint.php
        // "near" predicate for GeoPoint fragments
        $near = Predicates::near("my.store.location", 48.8768767, 2.3338802, 10);

        // Accessing GeoPoint fragments
        $place = $doc->getGeoPoint("article.location");
        $coordinates = "";
        if ($place) {
            $coordinates = $place->getLatitude() . "," . $place->getLongitude();
        }
        // endgist
        $this->assertEquals("48.877108,2.333879", $coordinates);
    }

    public function testImage() {
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api");
        $doc = $api->getByID("UlfoxUnM0wkXYXbO");

        // startgist:c202e1c27ddedf88aa7f:prismic-images.php
        // Accessing image fields
        $image = $doc->getImage("product.image");
        // Most of the time you will be using the "main" view
        $url = $image->getView("main")->getUrl();
        // endgist
        $this->assertEquals("https://d2aw36oac6sa9o.cloudfront.net/lesbonneschoses/f606ad513fcc2a73b909817119b84d6fd0d61a6d.png", $url);
    }

public function testCache() {
        // startgist:56e7aa7b1be64e09f76f:prismic-cache.php
        // You can pass any class implementing the CacheInterface to the Api creation
        // http://prismicio.github.io/php-kit/classes/Prismic.Cache.CacheInterface.html
        $fileCache = new ApcCache();
        $api = Api::get("https://lesbonneschoses.cdn.prismic.io/api", null /* token */, null /* client */, $fileCache);
        // endgist
        $this->assertNotNull($api);
    }

}
