<?php

namespace Prismic\Test;

use Prismic\Document;

class I18nTest extends \PHPUnit_Framework_TestCase
{
    protected $documents;

    protected function setUp()
    {
        $results = json_decode(file_get_contents(__DIR__.'/../fixtures/i18n.json'));
        $this->documents = array();
        foreach ($results as $idx => $result) {
            $this->documents[$idx] = Document::parse($result);
        }
    }
    
    public function testGetI18n()
    {
        foreach ($this->documents as $document) {
            $this->assertInstanceOf('Prismic\I18n\I18n', $document->getI18n());
        }
    }
    
    public function testGetLang()
    {
        $this->assertEquals("fr-fr", $this->documents[0]->getI18n()->getLang());
        $this->assertEquals("en-us", $this->documents[1]->getI18n()->getLang());
    }
    
    public function testGetRelatedDocs()
    {
        $this->assertCount(1, $this->documents[0]->getI18n()->getRelatedDocs());
        $this->assertCount(2, $this->documents[1]->getI18n()->getRelatedDocs());
        $this->assertCount(0, $this->documents[2]->getI18n()->getRelatedDocs());
        
        foreach ($this->documents[1]->getI18n()->getRelatedDocs() as $relatedDoc) {
            $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDoc);
        }
    }
    
    public function testGetRelatedDoc()
    {
        $relatedDocument = $this->documents[0]->getI18n()->getRelatedDoc('en-us');
        $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDocument);
        
        $relatedDocumentWithUid = $this->documents[1]->getI18n()->getRelatedDoc('fr-fr');
        $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDocumentWithUid);
    }
    
    public function testGetId()
    {
        $relatedDocument = $this->documents[0]->getI18n()->getRelatedDoc('en-us');
        $this->assertEquals('WLgklCIAAORBTRvh', $relatedDocument->getId());
    }
    
    public function testGetUid()
    {
        $relatedDocument = $this->documents[0]->getI18n()->getRelatedDoc('en-us');
        $this->assertNull($relatedDocument->getUid());
        
        $relatedDocumentWithUid = $this->documents[1]->getI18n()->getRelatedDoc('fr-fr');
        $this->assertEquals('fr', $relatedDocumentWithUid->getUid());
    }
    
    public function testGetType()
    {
        $relatedDocument = $this->documents[0]->getI18n()->getRelatedDoc('en-us');
        $this->assertEquals('page', $relatedDocument->getType());
    }
}
