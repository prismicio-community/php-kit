<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Prismic\Fragment\Color;
use Prismic\Fragment\Date;
use Prismic\Fragment\Embed;
use Prismic\Fragment\Image;
use Prismic\Fragment\Number;
use Prismic\Fragment\ImageView;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\MediaLink;
use Prismic\Fragment\Link\WebLink;
use Prismic\Fragment\StructuredText;
use Prismic\Fragment\Text;
use Prismic\Fragment\Group;
use Prismic\Fragment\Block\ImageBlock;
use Prismic\Fragment\Block\TextInterface;

class Document
{

    private $id;
    private $type;
    private $href;
    private $tags;
    private $slugs;
    private $fragments;

    /**
     * @param string $id
     * @param string $type
     * @param string $href
     * @param array  $tags
     * @param array  $slugs
     * @param array  $fragments
     */
    public function __construct($id, $type, $href, $tags, $slugs, array $fragments)
    {
        $this->id = $id;
        $this->type = $type;
        $this->href = $href;
        $this->tags = $tags;
        $this->slugs = $slugs;
        $this->fragments = $fragments;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        if (count($this->slugs) > 0) {
            return $this->slugs[0];
        }

        return null;
    }

    /**
     * @param $slug
     *
     * @return bool
     */
    public function containsSlug($slug)
    {
        $found = array_filter($this->slugs, function ($s) use ($slug) {
            return $s == $slug;
        });

        return count($found) > 0;
    }

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function get($field)
    {
        $single = null;
        if (!array_key_exists($field, $this->fragments)) {
            $multi = $this->getAll($field);
            if (!empty($multi)) {
                $single = $multi[0];
            }
        } else {
            $single = $this->fragments[$field];
        }

        return $single;
    }

    /**
     * @param string $field
     *
     * @return bool
     *              */
     public function has($field)
     {
         return array_key_exists($field, $this->fragments);
     }

    /**
     * @param string $field
     *
     * @return array
     */
    public function getAll($field)
    {
        $result = array();
        foreach ($this->fragments as $key => $value) {
            $groups = array();
            if (preg_match('/^([^\[]+)(\[\d+\])?$/', $key, $groups) == 1) {
                if ($groups[1] == $field) {
                    array_push($result, $value);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getText($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof StructuredText) {
            $text = "";
            foreach ($fragment->getBlocks() as $block) {
                if ($block instanceof TextInterface) {
                    $text = $text . $block->getText();
                    $text = $text . "\n";
                }
            }

            return trim($text);
        } elseif (isset($fragment) && $fragment instanceof Number) {
            return $fragment->getValue();
        } elseif (isset($fragment) && $fragment instanceof Color) {
            return $fragment->getHex();
        } elseif (isset($fragment) && $fragment instanceof Text) {
            return $fragment->getValue();
        } elseif (isset($fragment) && $fragment instanceof Date) {
            return $fragment->getValue();
        }

        return "";
    }

    public function getNumber($field, $pattern = null)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Number) {
            if (isset($pattern) && isset($fragment)) {
                return $fragment->asText($pattern);
            } else {
                return $fragment;
            }
        }

        return null;
    }

    public function getBoolean($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Text) {
            $value = strtolower($fragment->getValue());

            return in_array(strtolower($fragment->getValue()), array(
                'yes',
                'true',
            ));
        }

        return null;
    }

    public function getDate($field, $pattern = null)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Date) {
            if (isset($pattern)) {
                return $fragment->formatted($pattern);
            }

            return $fragment;
        }

        return null;
    }

    public function getHtml($field, $linkResolver = null)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && method_exists($fragment, 'asHtml')) {
            return $fragment->asHtml($linkResolver);
        }

        return "";
    }

    public function getImage($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Image) {
            return $fragment;
        } elseif (isset($fragment) && $fragment instanceof StructuredText) {
            foreach ($fragment->getBlocks() as $block) {
                if ($block instanceof ImageBlock) {
                    return new Image($block->getView());
                }
            }
        }

        return null;
    }

    public function getAllImages($field)
    {
        $fragments = $this->getAll($field);
        $images = array();
        foreach ($fragments as $fragment) {
            if (isset($fragment) && $fragment instanceof Image) {
                array_push($images, $fragment);
            } elseif (isset($fragment) && $fragment instanceof StructuredText) {
                foreach ($fragment->getBlocks() as $block) {
                    if ($block instanceof ImageBlock) {
                        array_push($images, new Image($block->getView()));
                    }
                }
            }
        }

        return $images;
    }

    public function getImageView($field, $view = null)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Image) {
            return $fragment->getView($view);
        } elseif (isset($fragment) && $fragment instanceof StructuredText && $view == 'main') {
            $maybeImage = $this->getImage($field);
            if (isset($maybeImage)) {
                return $maybeImage->getMain();
            }
        }

        return null;
    }

    public function getAllImageViews($field, $view)
    {
        $imageViews = array();
        foreach ($this->getAllImages($field) as $image) {
            $imageView = $image->getView($view);
            if (isset($imageView)) {
                array_push($imageViews, $imageView);
            }
        };

        return $imageViews;
    }

    public function getStructuredText($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof StructuredText) {
            return $fragment;
        }

        return null;
    }

    public function getGroup($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Group) {
            return $fragment;
        }

        return null;
    }

    public function asHtml($linkResolver = null)
    {
        $html = null;
        foreach ($this->fragments as $field => $v) {
            $html = $html . '<section data-field="' . $field . '">' .
                    $this->getHtml($field, $linkResolver) . '</section>';
        };

        return $html;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getSlugs()
    {
        return $this->slugs;
    }

    public function getFragments()
    {
        return $this->fragments;
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

            if ($json->type === "Group") {
                return Group::parse($json->value);
            }

            return null;
        }
    }

    /**
     * @param \stdClass $json
     *
     * @return Document
     */
    public static function parse(\stdClass $json)
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
}
