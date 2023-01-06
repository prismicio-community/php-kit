# Templating Link & Content Relationship Fields

The Link field is used for adding links to the Web, to files in your prismic.io media library, or to documents in your prismic.io repository. The Content Relationship field is a Link field specifically used to link to a Document.

> **Before Reading**
>
> This page assumes that you have retrieved your content and stored it in a variable named `$document`.
>
> It is also assumed that you have set up a Link Resolver stored in the variable `$linkResolver`. When integrating a Link in your templates, a link resolver might be necessary as shown & discussed below. To learn more about this, check out our [Link Resolving](../04-beyond-the-api/01-link-resolving.md) page.

## Adding a hyperlink

Here's the basic integration of a Link or Content Relationship. This will work for any kind of link: Link to the Web, Link to a Media Item, or a Link to a Document / Content Relationship.

In this example, the Link field has an API ID of `my_link`.

**php (sdk v4 or later)**:

```html
<?php
use Prismic\Dom\Link;

$link = $document->data->my_link; $linkUrl = Link::asUrl($link, $linkResolver);
$targetAttr = property_exists($link, 'target') ? 'target="' . $link->target . '"
rel="noopener"' : ''; ?>

<a href="<?= $linkUrl ?>" <?="$targetAttr" ?>>Click here</a>
```

**php (sdk v3 or earlier)**:

```html
<?php
$link = $document->getLink('page.my_link'); $linkUrl =
$link->getUrl($linkResolver); $targetAttr = $link->getTarget() ? 'target="' .
$link->getTarget() . '" rel="noopener"' : ''; ?>

<a href="<?= $linkUrl ?>" <?="$targetAttr" ?>>Click here</a>
```

## Using a Link Resolver

Note that the example above uses a Link Resolver to output the link. This is only required for a Link to a Document / Content Relationship.

If you know that your link will always be to Media Item or to the Web, then you can remove this. If the link is to or might be to a Document, then you should always use the [Link Resolver](../04-beyond-the-api/01-link-resolving.md).
