<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

/**
 * This class embodies an Embed fragment.
 */
class Embed implements FragmentInterface
{

    /**
     * @var string the OEmbed resource's type
     */
    private $type;
    /**
     * @var string the OEmbed resource's provider
     */
    private $provider;
    /**
     * @var string the OEmbed resource's URL
     */
    private $url;
    /**
     * @var string the OEmbed resource's width
     */
    private $maybeWidth;
    /**
     * @var string the OEmbed resource's height
     */
    private $maybeHeight;
    /**
     * @var string the OEmbed resource's HTML code
     */
    private $maybeHtml;
    /**
     * @var string the OEmbed resource's JSON
     */
    private $oembedJson;

    /**
     * Constructs an Embed fragment.
     *
     * @param string  $type         the OEmbed resource's type
     * @param string  $provider     the OEmbed resource's provider
     * @param string  $url          the OEmbed resource's URL
     * @param string  $maybeWidth   the OEmbed resource's width
     * @param string  $maybeHeigth  the OEmbed resource's height
     * @param string  $maybeHtml    the OEmbed resource's HTML
     * @param string  $oembedJson   the OEmbed resource's JSON
     */
    public function __construct($type, $provider, $url, $maybeWidth, $maybeHeigth, $maybeHtml, $oembedJson)
    {
        $this->type = $type;
        $this->provider = $provider;
        $this->url = $url;
        $this->maybeWidth = $maybeWidth;
        $this->maybeHeight = $maybeHeigth;
        $this->maybeHtml = $maybeHtml;
        $this->oembedJson = $oembedJson;
    }

    /**
     * Builds a HTML version of the Embed fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Embed fragment
     */
    public function asHtml($linkResolver = null)
    {
        $providerAttr = '';
        if (isset($this->provider)) {
            $providerAttr = ' data-oembed-provider="' . strtolower($this->provider) . '"';
        }
        if (isset($this->maybeHtml)) {
            return '<div data-oembed="' . $this->url . '" data-oembed-type="' .
                    strtolower($this->type) . '"' . $providerAttr . '>' .
                    $this->maybeHtml . '</div>';
        } else {
            return "";
        }
    }

    /**
     * Get the the OEmbed type
     *
     * @api
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the the OEmbed provider
     *
     * @api
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get the the OEmbed url
     *
     * @api
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the the OEmbed width
     *
     * @api
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->maybeWidth;
    }

    /**
     * Get the the OEmbed height
     *
     * @api
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->maybeHeight;
    }

    /**
    * Get the the OEmbed resource's JSON.
    *
    * @api
    *
    * @return \stdClass
    */
    public function getOEmbedJson()
    {
        return $this->oembedJson;
    }

    /**
     * Get the full JSON oEmbed code.
     *
     * @api
     *
     * @return string the text version of the Embed fragment
     */
    public function asText()
    {
        return $this->url;
    }

    /**
     * Parses a given Embed fragment. Not meant to be used except for testing.
     *
     * @param  \stdClass                $json the json bit retrieved from the API that represents an Embed fragment.
     * @return \Prismic\Fragment\Embed  the manipulable object for that Embed fragment.
     */
    public static function parse($json)
    {
        return new Embed(
            $json->oembed->type,
            isset($json->oembed->{'provider_name'}) ? $json->oembed->provider_name : null,
            $json->oembed->embed_url,
            isset($json->oembed->{'width'}) ? $json->oembed->width : null,
            isset($json->oembed->{'height'}) ? $json->oembed->height : null,
            isset($json->oembed->{'html'}) ? $json->oembed->html : null,
            $json->oembed
        );
    }
}
