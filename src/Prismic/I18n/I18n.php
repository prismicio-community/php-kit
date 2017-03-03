<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\I18n;

use Prismic\I18n\RelatedDocument;

/**
 * This class embodies an i18n document object from the prismic.io API,
 * containing the language of the document, as well as all of the related
 * documents.
 */
class I18n
{
    /**
     * string the current document's language
     */
    private $lang;
    /**
     * array the related documents
     */
    private $relatedDocs;
    
    /**
     * Constructs an i18n object.
     *
     * @param string $lang              the current document's language
     * @param array  $relatedDocs       the related documents
     */
    public function __construct($lang, $relatedDocs)
    {
        $this->lang = $lang;
        $this->relatedDocs = $relatedDocs;
    }

    /**
     * Returns the language of the current document.
    *
    * @return string the language of the current document
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Returns an associated array of all the related documents.
     *
     * @return array the related documents
     */
    public function getRelatedDocs()
    {
        return $this->relatedDocs;
    }

    /**
     * Returns a related document from its language (for instance, "en-us, "fr-fr", ...)
     * 
     * @param string $key the language of the related document
     * 
     * @return \Prismic\i18n\RelatedDocument the related document
     */
    public function getRelatedDoc($key)
    {
        return $this->relatedDocs[$key];
    }
    
    /**
     * Parses a given i18n object. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents the i18n object of a document.
     *
     * @return i18n the manipulable i18n object for that document.
     */
    public static function parse(\stdClass $json)
    {
        $lang = isset($json->lang) ? $json->lang : null;

        $relatedDocs = array();
        foreach ($json->related_documents as $key => $relatedDoc) {
            $relatedDocs[$key] = RelatedDocument::parse($relatedDoc);
        }

        return new I18n($lang, $relatedDocs);
    }

}
