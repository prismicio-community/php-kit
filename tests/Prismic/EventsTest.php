<?php

namespace Prismic\Test;

use Prismic\Cache\ApcCache;
use Prismic\Document;
use Prismic\Api;
use Prismic\Events;
use Prismic\Event\PreSubmitEvent;
use Prismic\Event\PostSubmitEvent;
use Symfony\Component\EventDispatcher\Event;

class EventsTest extends \PHPUnit_Framework_TestCase
{
    private static $testRepository = 'http://micro.prismic.io/api';

    protected $document;
    protected $linkResolver;
    protected $micro_api;

    protected function setUp()
    {
        $cache = new ApcCache();
        $cache->clear();
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $this->document = Document::parse($search[0]);
        $this->micro_api = Api::get(self::$testRepository, null, null, $cache);
        $this->linkResolver = new FakeLinkResolver();
    }

    public function testEvents()
    {
        $masterRef = $this->micro_api->master()->getRef();
        $searchForm = $this->micro_api->forms()->everything->ref($masterRef)->query('[[:d = at(document.id, "U9pjvjQAADAAehbf")]]');

        $dispatcher = $this->micro_api->getDispatcher();

        $testCase = $this;

        $preRunCount = 0;
        $postRunCount = 0;

        $dispatcher->addListener(Events::PRE_SUBMIT, function(Event $event) use($testCase, &$preRunCount, $searchForm) {
            $preRunCount++;
            $testCase->assertInstanceOf('Prismic\Event\PreSubmitEvent', $event);
            $testCase->assertSame($event->getSearchForm(), $searchForm);
        });

        $dispatcher->addListener(Events::POST_SUBMIT, function(Event $event) use($testCase, &$postRunCount, $searchForm) {
            $postRunCount++;
            $testCase->assertInstanceOf('Prismic\Event\PostSubmitEvent', $event);
            $testCase->assertSame($event->getSearchForm(), $searchForm);
            $testCase->assertInternalType('boolean', $event->wasCacheHit());
        });

        $searchForm->submit()->getResults();

        $this->assertEquals($preRunCount, 1);
        $this->assertEquals($postRunCount, 1);
    }

}
