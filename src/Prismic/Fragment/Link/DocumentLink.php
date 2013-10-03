<?php

namespace Prismic\Fragment\Link;

class DocumentLink implements LinkInterface
{
    private $id;
    private $type;
    private $tags;
    private $slug;
    private $isBroken;

    public function __construct($id, $type, $tags, $slug, $isBroken)
    {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->slug = $slug;
        $this->isBroken = $isBroken;
    }

    public function asHtml($linkResolver)
    {
        return '<a href="' . $linkResolver($this) . '">' . $this->slug . '</a>';
    }

    public static function parse($json)
    {
        return new DocumentLink(
            $json->document->id,
            $json->document->type,
            isset($json->document->{'tags'}) ? $json->document->tags : null,
            $json->document->slug,
            $json->isBroken
        );
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}