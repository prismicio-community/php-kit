<?php

declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Exception\InvalidArgumentException;
use Prismic\Language;

class LanguageTest extends TestCase
{

    /** @var Language */
    private $language;
    /** @var \stdClass */
    private $json;

    public function setUp(): void
    {
        $this->json = $json = \json_decode($this->getJsonFixture('language.json'));
        $this->language = Language::parse($json);
    }

    public function testCorrectId()
    {
        $this->assertSame($this->json->id, $this->language->getId());
        $this->assertSame($this->json->name, $this->language->getName());
    }

    public function testWrongObjectType()
    {
        $this->expectException(InvalidArgumentException::class);
        Language::parse(new \stdClass);
    }
}
