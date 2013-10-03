<?php

namespace Prismic\Test;

use Prismic\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $document = Document::parse($search[0]);

        $this->assertEquals($document->slug(), 'vanilla-macaron');
        $this->assertEquals($document->type, 'product');
    }
}
