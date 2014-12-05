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

use ArrayAccess;
use Exception;
use Prismic\Fragment\Block\ImageBlock;
use Prismic\Fragment\Block\TextInterface;
use Prismic\Fragment\Color;
use Prismic\Fragment\Date;
use Prismic\Fragment\Embed;
use Prismic\Fragment\GeoPoint;
use Prismic\Fragment\Group;
use Prismic\Fragment\Image;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Number;
use Prismic\Fragment\StructuredText;
use Prismic\Fragment\Text;
use Prismic\Fragment\Timestamp;

class WithFragments implements ArrayAccess {

    /**
     * @var array all the fragments in the document (please use instance methods to get information that is in there)
     */
    private $fragments;

    function __construct(array $fragments) {
        $this->fragments = $fragments;
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
     * Accesses a fragment of the document. For instance, if the document is of the type "product"
     * and the name of the fragment is "description", then you can access the fragment like this:
     * document->get('product.description').
     *
     * If you prefer a more type-safe access, that only works if the fragment is of the right type,
     * you can use the getStructuredText, getColor, getDate, etc. methods.
     *
     * @api
     * @param  string                              $field name of the fragment, with the document's type, like "product.description"
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
     * @param  string  $field name of the fragment, with the document's type, like "product.description"
     * @return boolean true if the fragment exists, false otherwise
     *                       */
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
     * @return \Prismic\Fragment\Number|string the Number fragment, the string representation if $pattern was set, or null if the fragment is of the wrong type or unset
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
     * Returns a GeoPoint fragment or null if the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string $field name of the fragment
     * @return GeoPoint Fragment
     */
    public function getGeoPoint($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof GeoPoint) {
            return $fragment;
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
     * Returns the usable Timestamp fragment as an object, ready to be manipulated.
     *
     * @api
     * @param  string                       $field   name of the fragment, with the document's type, like "product.publishedAt"
     * @return \Prismic\Fragment\Timestamp  the directly usable Timestamp, or null if the fragment is of the wrong type or unset
     */
    public function getTimestamp($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Timestamp) {
            return $fragment;
        }

        return null;
    }

    /**
     * Returns an HTML serialization for any kind of fragment. This is simply a faster way to write
     * $doc->get($field)->asHtml($linkResolver).
     *
     * @api
     * @param  string                $field        name of the fragment, with the document's type, like "product.description"
     * @param  \Prismic\LinkResolver $linkResolver an extension of the Prismic\LinkResolver class that you taught how to turn a prismic.io document in to a URL in your application
     * @return string                the directly usable HTML code, or null if the fragment is unset
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
     * @param  string                  $field name of the fragment, with the document's type, like "product.picture"
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
     * @param  string                      $field name of the fragment, with the document's type, like "product.picture"
     * @param  string                      $view  name of the view, like "small"
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
     * @param string $view name of the view, like "small"
     * @return array all views of the image
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
     * @param  string                           $field name of the fragment, with the document's type, like "product.description"
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
     * Returns the Link fragment as a manipulable object,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string                  $field name of the fragment, with the document's type, like "product.gallery"
     * @return \Prismic\Fragment\Link\LinkInterface the directly usable object, or null if the fragment is of the wrong type or unset
     */
    public function getLink($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && ($fragment instanceof LinkInterface)) {
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
     * @param  string                  $field name of the fragment, with the document's type, like "product.gallery"
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
     * Returns the Embed fragment as a manipulable object,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string                  $field name of the fragment, with the document's type, like "product.video"
     * @return \Prismic\Fragment\Embed the directly usable object, or null if the fragment is of the wrong type or unset
     */
    public function getEmbed($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Embed) {
            return $fragment;
        }

        return null;
    }

    /**
     * Returns the Color fragment as a manipulable object,
     * and null of the fragment is of the wrong type, or if it doesn't exist.
     *
     * @api
     * @param  string                  $field name of the fragment, with the document's type, like "product.video"
     * @return \Prismic\Fragment\Color the directly usable object, or null if the fragment is of the wrong type or unset
     */
    public function getColor($field)
    {
        $fragment = $this->get($field);
        if (isset($fragment) && $fragment instanceof Color) {
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
     * @return string                the directly usable HTML code
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

    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < count($this->fragments);
    }

    public function offsetGet($offset)
    {
        return $this->fragments[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("Document retrieved from Prismic can't be modified");
        // TODO: Implement offsetUnset() method.
    }
}