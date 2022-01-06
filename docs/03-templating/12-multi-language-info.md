# Templating multi-language info

This page shows you how to access the language code and alternate language versions of a document.

## Get the document language code

You can get the language code of a document by accessing its `lang` property. This might give "en-us" (American english) or "fr-fr" (french) for example.

**php (sdk v4 or later)**:

```html
<?php
$lang = $document->lang;
```

**php (sdk v3 or earlier)**:

```html
<?php
$lang = $document->getLang();
```

## Get the alternate language versions

Next we will access the information about a document's alternate language versions.

You can get the alternate languages using the `alternate_languages` property. Then simply loop through the array and access the ID, UID, type and language code of each as shown below.

**php (sdk v4 or later)**:

```html
<?php
$altLangs = $document->alternate_languages; foreach ($altLangs as $altLang) {
$id = $altLang->id; $uid = $altLang->uid; $type = $altLang->type; $lang =
$altLang->lang; }
```

**php (sdk v3 or earlier)**:

```html
<?php
$altLangs = $document->getAlternateLanguages(); foreach ($altLangs as $altLang)
{ $id = $altLang->getId(); $uid = $altLang->getUid(); $type =
$altLang->getType(); $lang = $altLang->getLang(); }
```

## Get a specific language version

If you need to get a specific alternate language version, use the `getAlternateLanguage` helper method.

Here's an example of how to get the french version ('fr-fr') of a document.

**php (sdk v4 or later)**:

```html
<?php
use Prismic\Document;

$frenchVersion = Document::getAlternateLanguage($document, 'fr-fr');

$id = $frenchVersion->id; $uid = $frenchVersion->uid; $type =
$frenchVersion->type; $lang = $frenchVersion->lang;
```

**php (sdk v3 or earlier)**:

```html
<?php
$frenchVersion = $document->getAlternateLanguage('fr-fr'); $id =
$frenchVersion->getId(); $uid = $frenchVersion->getUid(); $type =
$frenchVersion->getType(); $lang = $frenchVersion->getLang();
```
