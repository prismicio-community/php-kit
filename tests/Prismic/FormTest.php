<?php
declare(strict_types=1);

namespace Prismic\Test;

use PHPUnit\Framework\TestCase;
use Prismic\FieldForm;
use Prismic\Form;
use function array_map;

class FormTest extends TestCase
{
    /** @var Form */
    private $form;

    protected function setUp() : void
    {
        parent::setUp();
        $this->form = Form::withJsonString('blogs', <<<JSON
        {
            "name": "Blog Posts",
            "method": "GET",
            "rel": "collection",
            "enctype": "application/x-www-form-urlencoded",
            "action": "https://repo.prismic.io/api/v2/documents/search",
            "fields": {
                "ref": {
                    "type": "String",
                    "multiple": false
                },
                "q": {
                    "default": "[[:d = any(document.type, [\"blog-post\"])]]",
                    "type": "String",
                    "multiple": true
                },
                "lang": {
                    "type": "String",
                    "multiple": false
                },
                "page": {
                    "type": "Integer",
                    "multiple": false,
                    "default": "1"
                },
                "pageSize": {
                    "type": "Integer",
                    "multiple": false,
                    "default": "20"
                },
                "after": {
                    "type": "String",
                    "multiple": false
                },
                "fetch": {
                    "type": "String",
                    "multiple": false
                },
                "fetchLinks": {
                    "type": "String",
                    "multiple": false
                },
                "orderings": {
                    "type": "String",
                    "multiple": false
                },
                "referer": {
                  "type": "String",
                  "multiple": false
                },
                "access_token": {
                    "default": "a-permanent-access-token",
                    "type": "String",
                    "multiple": false
                }
            }
        }
        JSON);
    }

    /** @return string[][] */
    public function fieldNameProvider() : iterable
    {
        $fields = ['ref', 'q', 'lang', 'page', 'pageSize', 'after', 'fetch', 'fetchLinks', 'orderings', 'referer', 'access_token'];

        return array_map(static function (string $field) : array {
            return [$field];
        }, $fields);
    }

    public function testKeyIsExpectedValue() : void
    {
        $this->assertEquals('blogs', $this->form->getKey());
    }

    public function testNameIsExpectedValue() : void
    {
        $this->assertEquals('Blog Posts', $this->form->getName());
    }

    public function testRelIsExpectedValue() : void
    {
        $this->assertEquals('collection', $this->form->getRel());
    }

    public function testMethodIsExpectedValue() : void
    {
        $this->assertSame('GET', $this->form->getMethod());
    }

    public function testEncTypeIsExpectedValue() : void
    {
        $this->assertSame('application/x-www-form-urlencoded', $this->form->getEnctype());
    }

    public function testActionIsExpectedValue() : void
    {
        $this->assertSame('https://repo.prismic.io/api/v2/documents/search', $this->form->getAction());
    }

    /** @dataProvider fieldNameProvider */
    public function testExpectedFieldsAreAvailable(string $field) : void
    {
        $formField = $this->form->getField($field);
        $this->assertInstanceOf(FieldForm::class, $formField);
        if ($formField->getDefaultValue() !== null) {
            $this->assertArrayHasKey($field, $this->form->defaultData());
        } else {
            $this->assertArrayNotHasKey($field, $this->form->defaultData());
        }
    }
}
