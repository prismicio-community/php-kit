<?php

namespace Prismic\Test;

use Prismic\Document;
use Prismic\Fragment\Group;

class GroupDocTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $json = json_decode(file_get_contents(__DIR__.'/../fixtures/group.json'));
        $this->document = Document::parse($json[0]);
    }

    public function testDocumentIsAvailable()
    {
        $this->assertInstanceOf('Prismic\Document', $this->document);
        return $this->document;
    }

    /**
     * @depends testDocumentIsAvailable
     */
    public function testDocumentHasGroupFragment(Document $doc)
    {
        $fragment = $doc->get('article.group_frag');
        $this->assertInstanceOf('\Prismic\Fragment\Group', $fragment);

        return $fragment;
    }

    /**
     * @depends testDocumentHasGroupFragment
     */
    public function testGroupDocIsReturnedByGetArray(Group $group)
    {
        $array = $group->getArray();
        $this->assertCount(3, $array);
        foreach($array as $groupDoc) {
            $this->assertInstanceOf('\Prismic\Fragment\GroupDoc', $groupDoc);
        }

        return $array;
    }

    /**
     * @depends testDocumentHasGroupFragment
     */
    public function testOffsetExists(Group $group)
    {
        foreach($group->getArray() as $groupDoc) {
            $this->assertInternalType('bool', $groupDoc->has('element_three'));
            $this->assertTrue($groupDoc->has('element_one'));
            $this->assertFalse($groupDoc->has('something_else'));

            $this->assertTrue( isset($groupDoc['element_one']) );
            $this->assertFalse( isset($groupDoc['something_else']) );
        }

        return $group;
    }

    /**
     * @depends testOffsetExists
     */
    public function testOffsetGet(Group $group)
    {
        foreach($group->getArray() as $groupDoc) {
            $this->assertInstanceOf('\Prismic\Fragment\Text', $groupDoc['element_one']);
        }
    }



}
