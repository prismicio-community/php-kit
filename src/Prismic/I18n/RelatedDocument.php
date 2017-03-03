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

/**
 * This class embodies a Related Document for an i18n document object. It
 * contains an id, uid, and the type of the document.
 */
class RelatedDocument
{
    /**
     * string the related document's id
     */
    private $id;
    /**
     * string the related document's uid
     */
    private $uid;
    /**
     * string the related document's type
     */
    private $type;
    
    /**
     * Constructs a Related Document object.
     *
     * @param string $id                the related document's id
     * @param string $uid               the related document's uid
     * @param string $type              the related document's type
     */
    public function __construct($id, $uid, $type)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
    }

    /**
     * Returns the id of the related document.
    *
    * @return string the related document's id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the uid of the related document
     *
     * @return string the related document's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Returns the type of the related document
     *
     * @return string the related document's type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Parses a given Related Document object. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the i18n object that represents the related document.
     *
     * @return RelatedDocument the manipulable Related Docuemnt object.
     */
    public static function parse($json)
    {
        return new RelatedDocument(
            $json->id, 
            isset($json->uid) ? $json->uid : null,
            $json->type
        );
    }

}
