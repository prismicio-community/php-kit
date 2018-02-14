<?php

namespace Prismic;

class Document
{
    /**
     * Return the specified alternate language version of a document
     * and null if the document doesn't exist
     *
     *
     * @param   object  $document   the document
     * @param   string  $langKey    the language code of the alternate language version, like "en-us"
     *
     * @return object the directly usable object, or null if the alternate language version does not exist
     */
    public static function getAlternateLanguage($document, $langKey)
    {
        foreach ($document->alternate_languages as $language) {
            if ($language->lang === $langKey) {
                return $language;
            }
        }

        return null;
    }
}
