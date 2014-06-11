<?php
/**
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

/**
 * Embodies a document retrieved from the API, which we'll be able to manipulate.
 */
class Document
{

    /**
     * @var string the ID of the document (please use instance methods to get information that is in there)
     */
    private $id;
    /**
     * @var string the type of the document (please use instance methods to get information that is in there)
     */
    private $type;
    /**
     * @var string the URL of the document in the repository's API (please use instance methods to get information that is in there)
     */
    private $href;
    /**
     * @var array the tags used in the document (please use instance methods to get information that is in there)
     */
    private $tags;
    /**
     * @var array the slugs used in the document, in the past and today; today's slug is the head (please use instance methods to get information that is in there)
     */
    private $slugs;
    /**
     * @var array all the fragments in the document (please use instance methods to get information that is in there)
     */
    private $fragments;

    /**
     * Constructs a Document object. To be used only for testing purposes, as this gets done during the unmarshalling
     *
     * @param string $id        the ID of the document
     * @param string $type      the type of the document
     * @param string $href      the URL of the document in the repository's API
     * @param array  $tags      the tags used in the document
     * @param array  $slugs     the slugs used in the document, in the past and today; today's slug is the head
     * @param array  $fragments all the fragments in the document
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
     * Returns the current slug of the document
     *
     * @api
     *
     * @return string the current slug of the document
     */
    public function getSlug()
    {
        if (count($this->slugs) > 0) {
            return $this->slugs[0];
        }

        return null;
    }

    /**
     * Checks if a given slug is a past or current slug of the document
     *
     * @api
     * @param  string    $slug the slug to check
     * @return boolean   true if the slug is a past or current slug of the document, false otherwise
     */
    public function containsSlug($slug)
    {
        $found = array_filter($this->slugs, function ($s) use ($slug) {
            return $s == $slug;
        });

        return count($found) > 0;
    }

    /**
     * Accesses a fragment of the document. For instance, if the document is of the type "product"
     * and the name of the fragment is "description", then you can access the fragment like this:
     * document->get('product.description').
     *
     * If you prefer a more type-safe access, that only works if the fragment is of the right type,
     * you can use the getStructuredText, getColor, getDate, etc. methods.
     *
     * @api
     * @param  string                             $field name of the fragment, with the document's type, like "product.description"
     * @return \Prismic\Fragment\FragmentInterface the fragment as an object
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
     * Checks if a given fragment exists in the document.
     *
     * @api
     * @param  string $field name of the fragment, with the document's type, like "product.description"
     * @return boolean   true if the fragment exists, false otherwise
     *                      */
     public function has($field)
     {
         return array_key_exists($field, $this->fragments);
     }

    /**
     * Returns all fragments of the name given
     *
     * @deprecated deprecated as it was meant to be used with an old concept called "multiples"; prefer to use Group fragments now, that are more powerful.
     * @param  string $field name of the fragments
     * @return array  the list of fragments that exist
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
     * For any fragment type that makes sense to return as a text value, returns the text value.
     *
     * Works with: StructuredText, Number, Color, Text and Date fragments. If fragment is of the wrong type
     * or doesn't exist, returns an empty string.
     *
     * @api
     * @param  string $field name of the fragment, with the document's type, like "product.description"
     * @return string the directly usable string
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

    /**
     * Returns the string for a Number fragment, potentially matching a given pattern,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string $field   name of the fragment, with the document's type, like "product.price"
     * @param  string $pattern with the syntax expected by sprintf; null if not used
     * @return string the directly usable string, or null if the fragment is of the wrong type or unset
     */
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

    /**
     * Returns a boolean for any type of fragment that extends Prismic\Fragment\Text, if the text is
     * either 'yes' or 'true', or null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * This works well with Select fragment for instance, where you set your values to "true" or "false".
     *
     * @api
     * @param  string  $field name of the fragment, with the document's type, like "product.withchocolate"
     * @return boolean the directly usable boolean, or null if the fragment is of the wrong type or unset
     */
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

    /**
     * Returns the string for a Date fragment, potentially matching a given pattern,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string $field   name of the fragment, with the document's type, like "product.publishedAt"
     * @param  string $pattern with the syntax expected by the date function; null if not used
     * @return string the directly usable string, or null if the fragment is of the wrong type or unset
     */
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

    /**
     * Returns an HTML serialization for any kind of fragment. This is simply a faster way to write
     * $doc->get($field)->asHtml($linkResolver).
     *
     * @api
     * @param  string               $field        name of the fragment, with the document's type, like "product.description"
     * @param  \Prismic\LinkResolver $linkResolver an extension of the Prismic\LinkResolver class that you taught how to turn a prismic.io document in to a URL in your application
     * @return string               the directly usable HTML code, or null if the fragment is unset
     */
    public function getHtml($field, $linkResolver = null)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && method_exists($fragment, 'asHtml')) {
            return $fragment->asHtml($linkResolver);
        }

        return "";
    }

    /**
     * Returns the usable Image fragment as an object, ready to be manipulated.
     * This function also works on StructuredText fragment, and returns the first image in the fragment.
     *
     * @api
     * @param  string                 $field name of the fragment, with the document's type, like "product.picture"
     * @return \Prismic\Fragment\Image the directly usable HTML code, or null if the fragment is unset
     */
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

    /**
     * Returns all the usable Image fragments in the given StructuredText fragment, ready to be manipulated.
     * This function also works on Image fragments, but only was useful in that case before Group fragments existed.
     *
     * @api
     * @param  string $field name of the fragment, with the document's type, like "product.picture"
     * @return array  an array of all the Prismic\Fragment\Image objects found
     */
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

    /**
     * Returns the proper view of the given Image fragment, ready to be manipulated.
     * This function also works on StructuredText fragments, to return the first Image, if the view is set to "main".
     *
     * @api
     * @param  string                     $field name of the fragment, with the document's type, like "product.picture"
     * @param  string                     $view  name of the view, like "small"
     * @return \Prismic\Fragment\ImageView the directly usable object symbolizing the view
     */
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

    /**
     * Get all given views of all images for a given fragment name.
     *
     * @param string $field name of the fragments, with the document's type, like "product.picture"
     * @param string $view  name of the view, like "small"
     * @deprecated deprecated because this only made sense when Group fragments didn't exist yet.
     */
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

    /**
     * Returns the StructuredText fragment as a manipulable object,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string                          $field name of the fragment, with the document's type, like "product.description"
     * @return \Prismic\Fragment\StructuredText the directly usable object, or null if the fragment is of the wrong type or unset
     */
    public function getStructuredText($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof StructuredText) {
            return $fragment;
        }

        return null;
    }

    /**
     * Returns the Group fragment as a manipulable object,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     * This is the point of entry to then loop or search through the elements inside the Group fragment.
     *
     * @api
     * @param  string                 $field name of the fragment, with the document's type, like "product.gallery"
     * @return \Prismic\Fragment\Group the directly usable object, or null if the fragment is of the wrong type or unset
     */
    public function getGroup($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Group) {
            return $fragment;
        }

        return null;
    }

    /**
     * Returns an HTML serialization for the whole document at once, by serializing all fragments in order.
     *
     * This is a basic serialization, if you want to template your document better, you will need to serialize
     * at the fragment level; for instance: $doc->get('product.description')->asHtml($linkResolver);
     *
     * @api
     * @param  \Prismic\LinkResolver $linkResolver an extension of the Prismic\LinkResolver class that you taught how to turn a prismic.io document in to a URL in your application
     * @return string               the directly usable HTML code
     */
    public function asHtml($linkResolver = null)
    {
        $html = null;
        foreach ($this->fragments as $field => $v) {
            $html = $html . '<section data-field="' . $field . '">' .
                    $this->getHtml($field, $linkResolver) . '</section>';
        };

        return $html;
    }

    /**
     * Returns the ID of the document
     *
     * @api
     *
     * @return string the ID of the document
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the type of the document
     *
     * @api
     *
     * @return string the type of the document
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the URL of the document in the repository's API
     *
     * @api
     *
     * @return string the URL of the document in the repository's API
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Returns the tags in the document
     *
     * @api
     *
     * @return array the tags in the document
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns the slugs used in the document, in the past and today; today's slug is the head.
     * Your can use getSlug() if you need just the current slug.
     *
     * @api
     *
     * @return array the slugs used in the document, in the past and today; today's slug is the head
     */
    public function getSlugs()
    {
        return $this->slugs;
    }

    /**
     * Returns the list of fragments of the document. You shouldn't have a reason to use this,
     * as you can access fragments with get functions.
     *
     * @return array the list of fragments in the document.
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * Parses a given fragment. Not meant to be used except for testing.
     *
     * @param  \stdClass                          $json the json bit retrieved from the API that represents any fragment.
     * @return \Prismic\Fragment\FragmentInterface the manipulable object for that fragment.
     */
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
     * Parses a given document. Not meant to be used except for testing.
     *
     * @param  \stdClass        $json the json bit retrieved from the API that represents a document.
     * @return \Prismic\Document the manipulable object for that document.
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
