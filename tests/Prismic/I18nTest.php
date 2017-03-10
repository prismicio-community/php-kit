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
    
    public function testGetLang()
    {
        $this->assertNull($this->documents[0]->getLang());
        $this->assertEquals("fr-fr", $this->documents[1]->getLang());
    }
    
    public function testGetAlternateLanguages()
    {
        $this->assertCount(0, $this->documents[0]->getAlternateLanguages());
        $this->assertCount(1, $this->documents[1]->getAlternateLanguages());
        $this->assertCount(2, $this->documents[2]->getAlternateLanguages());
        $this->assertCount(0, $this->documents[3]->getAlternateLanguages());
        $this->assertInstanceOf('Prismic\AlternateLanguage', $this->documents[1]->getAlternateLanguages()[0]);
    }
    
    public function testGetAlternateLanguage()
    {
        $this->assertNull($this->documents[0]->getAlternateLanguage('fr-fr'));
        $this->assertInstanceOf('Prismic\AlternateLanguage', $this->documents[1]->getAlternateLanguage('en-us'));
        $this->assertNull($this->documents[1]->getAlternateLanguage('fr-fr'));
        $this->assertInstanceOf('Prismic\AlternateLanguage', $this->documents[2]->getAlternateLanguage('es-es'));
        $this->assertNull($this->documents[3]->getAlternateLanguage('fr-fr'));
    }
    
    public function testGetId()
    {
        $alternateLanguage = $this->documents[1]->getAlternateLanguage('en-us');
        $this->assertEquals('WLgklCIAAORBTRvh', $alternateLanguage->getId());
    }
    
    public function testGetUid()
    {
        $alternateLanguage = $this->documents[1]->getAlternateLanguage('en-us');
        $this->assertNull($alternateLanguage->getUid());
        
        $alternateLanguageWithUid = $this->documents[2]->getAlternateLanguage('fr-fr');
        $this->assertEquals('fr', $alternateLanguageWithUid->getUid());
    }
    
    public function testGetType()
    {
        $alternateLanguage = $this->documents[1]->getAlternateLanguage('en-us');
        $this->assertEquals('page', $alternateLanguage->getType());
    }
    
    public function testGetLangOfAlternateLanguage()
    {
        $alternateLanguage = $this->documents[1]->getAlternateLanguage('en-us');
        $this->assertEquals('en-us', $alternateLanguage->getLang());
    }
}
