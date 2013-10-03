<?php

namespace Prismic;

use Prismic\Fragment\Color;
use Prismic\Fragment\Date;
use Prismic\Fragment\Embed;
use Prismic\Fragment\Image;
use Prismic\Fragment\ImageView;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\MediaLink;
use Prismic\Fragment\Link\WebLink;
use Prismic\Fragment\Number;
use Prismic\Fragment\StructuredText;
use Prismic\Fragment\Text;

class Document
{

    private $id;
    private $type;
    private $href;
    private $tags;
    private $slugs;
    private $fragments;

    public function __construct($id, $type, $href, $tags, $slugs, array $fragments)
    {
        $this->id = $id;
        $this->type = $type;
        $this->href = $href;
        $this->tags = $tags;
        $this->slugs = $slugs;
        $this->fragments = $fragments;
    }

    public function slug()
    {
        return $this->slugs[0];
    }

    public function containsSlug($slug)
    {
        $found = array_filter($this->slugs, function ($s) use ($slug) {
            return $s == $slug;
        });
        return count($found) > 0;
    }

    public function get($field)
    {
        if (!array_key_exists($field, $this->fragments)) {
            return null;
        }
        return $this->fragments[$field];
    }

    public function getText($field)
    {
        if (!array_key_exists($field, $this->fragments)) {
            return "";
        }
        return $this->fragments[$field]->asText();
    }

    public function getHtml($field, $linkResolver = null)
    {
        if (!array_key_exists($field, $this->fragments)) {
            return "";
        }
        return $this->fragments[$field]->asHtml($linkResolver);
    }

    public function getImage($field, $view)
    {
        if (!array_key_exists($field, $this->fragments)) {
            return null;
        }
        $fragment = $this->fragments[$field];
        return $fragment->getImage($view);
    }

    public function asHtml($linkResolver = null)
    {
        $html = null;
        foreach ($this->fragments as $field => $v) {
            $html = $html . '<section data-field="' . $field . '">' . $this->getHtml($field, $linkResolver) . '</section>';
        };
        return $html;
    }

    public static function parseFragment($json)
    {
        if (is_object($json) && property_exists($json, "type")) {
            if ($json->type === "Image") {
                $data = $json->value;
                $views = array();
                foreach ($json->value->views as $key => $jsonView) {
                    $views[$key] = ImageView::parse($jsonView);
                }
                $mainView = ImageView::parse($data->main, $views);

                return new Image($mainView, $views);
            }

            if ($json->type === "Color") {
                return new Color($json->value);
            }

            if ($json->type === "Number") {
                return new Number($json->value);
            }

            if ($json->type === "Date") {
                return new Date($json->value);
            }

            if ($json->type === "Text") {
                return new Text($json->value);
            }

            if ($json->type === "Select") {
                return new Text($json->value);
            }

            if ($json->type === "Embed") {
                return Embed::parse($json->value);
            }

            if ($json->type === "Link.web") {
                return WebLink::parse($json->value);
            }

            if ($json->type === "Link.document") {
                return DocumentLink::parse($json->value);
            }

            if ($json->type === "Link.file") {
                return MediaLink::parse($json->value);
            }

            if ($json->type === "StructuredText") {
                return StructuredText::parse($json->value);
            }
            return null;
        }
    }

    public static function parse($json)
    {
        $fragments = array();
        foreach ($json->data as $type => $fields) {
            foreach ($fields as $key => $value) {
                if (is_array($value)) {
                    for ($i = 0; $i < count($value); $i++) {
                        $f = self::parseFragment($value[$i]);
                        if (isset($f)) {
                            $fragments[$type . '.' . $key . '[' . $i . ']'] = $f;
                        }
                    }
                }
                $fragment = self::parseFragment($value);

                if (isset($fragment)) {
                    $fragments[$type . "." . $key] = $fragment;
                }
            }
        }

        return new Document($json->id, $json->type, $json->href, $json->tags, $json->slugs, $fragments);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}