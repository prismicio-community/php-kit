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

/**
 * This class embodies an Alternate Language version for a document object. It
 * contains the id, uid, type, and language code of the document.
 */
class AlternateLanguage
{
    /**
     * string the alternate language version's document id
     */
    private $id;
    /**
     * string the alternate language version's uid
     */
    private $uid;
    /**
     * string the alternate language version's type
     */
    private $type;
    /**
     * string the alternate language version's language code
     */
    private $lang;
    
    /**
     * Constructs an Alternate Language object.
     *
     * @param string $id                the alternate language version's document id
     * @param string $uid               the alternate language version's uid
     * @param string $type              the alternate language version's type
     * @param string $lang              the alternate language version's language code
     */
    public function __construct($id, $uid, $type, $lang)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
    }

    /**
     * Returns the document id of the alternate language version.
    *
    * @return string the alternate language version's document id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the uid of the alternate language version
     * 
     * It can be null, if the uid is not declared in the document mask.
     *
     * @return string the alternate language version's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Returns the type of the alternate language version
     *
     * @return string the alternate language version's type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the language code of the alternate language version
     *
     * @return string the alternate language version's language code
     */
    public function getLang()
    {
        return $this->lang;
    }
    
    /**
     * Parses a given Alternate Language object. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the document object that represents the alternate language version.
     *
     * @return AlternateLanguage the manipulable Alternate Language object.
     */
    public static function parse($json)
    {
        return new AlternateLanguage(
            $json->id, 
            isset($json->uid) ? $json->uid : null,
            $json->type,
            $json->lang
        );
    }

}
