<?php

namespace Prismic\Test;

use Prismic\Document;

class PublicationDateTest extends \PHPUnit_Framework_TestCase
{
    protected $documents;

    protected function setUp()
    {
        $results = json_decode(file_get_contents(__DIR__.'/../fixtures/publication_dates.json'));
        $this->documents = array();
        foreach ($results as $idx => $result) {
            $this->documents[$idx] = Document::parse($result);
        }
    }

    public function testGetFirstPublicationDate()
    {
        $date = $this->documents[0]->getFirstPublicationDate();
        $this->assertEquals(1477463000, $date->getTimestamp());
    }

    public function testGetLastPublicationDate()
    {
        $date = $this->documents[0]->getLastPublicationDate();
        $this->assertEquals(1482733400, $date->getTimestamp());
    }

    public function testEmptyGetFirstPublicationDate()
    {
        $date = $this->documents[2]->getFirstPublicationDate();
        $this->assertEquals(null, $date);
    }

    public function testEmptyGetLastPublicationDate()
    {
        $date = $this->documents[2]->getLastPublicationDate();
        $this->assertEquals(null, $date);
    }

    public function testMixedGetFirstPublicationDate()
    {
        $date = $this->documents[1]->getFirstPublicationDate();
        $this->assertEquals(null, $date);
    }

    public function testMixedGetLastPublicationDate()
    {
        $date = $this->documents[1]->getLastPublicationDate();
        $this->assertEquals(1482733400, $date->getTimestamp());
    }

}
