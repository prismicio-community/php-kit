<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

class Embed implements FragmentInterface
{

    private $type;
    private $provider;
    private $url;
    private $maybeWidth;
    private $maybeHeight;
    private $maybeHtml;
    private $oembedJson;

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

    public function asHtml($linkResolver = null)
    {
        if (isset($this->maybeHtml)) {
            return '<div data-oembed="' . $this->url . '" data-oembed-type="' .
                    strtolower($this->type) . '" data-oembed-provider="' .
                    strtolower($this->provider) . '">' . $this->maybeHtml . '</div>';
        } else {
            return "";
        }
    }

    public function asText()
    {
        return $this->url;
    }

    public static function parse($json)
    {
        return new Embed(
            $json->oembed->type,
            $json->oembed->provider_name,
            $json->oembed->embed_url,
            $json->oembed->width,
            $json->oembed->height,
            $json->oembed->html,
            $json->oembed
        );
    }
}
