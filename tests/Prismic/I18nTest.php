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
        $this->i18nArray = array();
        foreach ($this->documents as $idx => $i18n) {
            $this->i18nArray[$idx] = $i18n->getI18n();
            $this->assertInstanceOf('Prismic\I18n\I18n', $this->i18nArray[$idx]);
        }
        
        return $this->i18nArray;
    }
    
    /**
     * @depends testGetI18n
     */
    public function testGetLang(Array $i18nArray)
    {
        $this->assertEquals("fr-fr", $i18nArray[0]->getLang());
        $this->assertEquals("en-us", $i18nArray[1]->getLang());
    }
    
    /**
     * @depends testGetI18n
     */
    public function testGetRelatedDocs(Array $i18nArray)
    {
        $this->assertCount(1, $i18nArray[0]->getRelatedDocs());
        $this->assertCount(2, $i18nArray[1]->getRelatedDocs());
        $this->assertCount(0, $i18nArray[2]->getRelatedDocs());
        
        foreach ($i18nArray[1] as $idx => $relatedDoc) {
            $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDoc);
        }
    }
    
    /**
     * @depends testGetI18n
     */
    public function testGetRelatedDoc(Array $i18nArray)
    {
        $relatedDocument = $i18nArray[0]->getRelatedDoc('en-us');
        $relatedDocumentWithUid = $i18nArray[1]->getRelatedDoc('fr-fr');
        $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDocument);
        $this->assertInstanceOf('Prismic\I18n\RelatedDocument', $relatedDocumentWithUid);
        
        return array($relatedDocument, $relatedDocumentWithUid);
    }
    
    /**
     * @depends testGetRelatedDoc
     */
    public function testGetId(Array $relatedDocArray)
    {
        $this->assertEquals('WLgklCIAAORBTRvh', $relatedDocArray[0]->getId());
        $this->assertEquals('WLVOViIAACIAz0Us', $relatedDocArray[1]->getId());
    }
    
    /**
     * @depends testGetRelatedDoc
     */
    public function testGetUid(Array $relatedDocArray)
    {
        $this->assertNull($relatedDocArray[0]->getUid());
        $this->assertEquals('fr', $relatedDocArray[1]->getUid());
    }
    
    /**
     * @depends testGetRelatedDoc
     */
    public function testGetType(Array $relatedDocArray)
    {
        $this->assertEquals('page', $relatedDocArray[0]->getType());
        $this->assertEquals('post', $relatedDocArray[1]->getType());
    }
}
