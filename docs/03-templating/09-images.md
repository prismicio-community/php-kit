# Templating the Image field

The Image field allows content writers to upload an image that can be configured with size constraints and responsive image views.

## Output an image in your template

Here's an example of integrating an image. In this case the Image field has the API ID of `illustration`.

**php (sdk v4 or later)**:

```html
<img
  src="<?= $document->data->illustration->url ?>"
  alt="<?= $document->data->illustration->alt ?>"
/>
```

**php (sdk v3 or earlier)**:

```html
<img
  src="<?= $document->getImage('page.illustration')->getUrl() ?>"
  alt="<?= $document->getImage('page.illustration')->getAlt() ?>"
/>
```

> Note that the `alt` attribute is mandatory in HTML5 for image element. We advise to always write an alternative text for each image uploaded to your Prismic's media library.

Here's an example of integrating an illustration with a caption. In this case we have an Image field with the API ID of `illustration` and a Rich Text field with an API ID of `caption`.

**php (sdk v4 or later)**:

```html
<?php
use Prismic\Dom\RichText;
?>

<figure>
  <img
    src="<?= $document->data->illustration->url ?>"
    alt="<?= $document->data->illustration->alt ?>"
  />
  <figcaption><?= RichText::asText($document->data->caption) ?></figcaption>
</figure>
```

**php (sdk v3 or earlier)**:

```html
<figure>
  <img
    src="<?= $document->getImage('page.illustration')->getUrl() ?>"
    alt="<?= $document->getImage('page.illustration')->getAlt() ?>"
  />
  <figcaption>
    <?= $document->getStructuredText('page.caption')->asText() ?>
  </figcaption>
</figure>
```

## Get a responsive image view

Here is how to add responsive images using the HTML picture element. In this example we have an Image field with the API ID of `responsive_image`. This Image field has the default image view along with views named `tablet` and `mobile`.

**php (sdk v4 or later)**:

```html
<?php
$mainView = $document->data->responsive_image; $tabletView =
$document->data->responsive_image->tablet; $mobileView =
$document->data->responsive_image->mobile; ?>

<picture>
  <source media="(max-width: 400px)" , srcset="<?= $mobileView->url ?>" />
   
  <source media="(max-width: 900px)" , srcset="<?= $tabletView->url ?>" />
   
  <source srcset="<?= $mainView->url ?>" />
  <image src="<?= $mainView->url ?>" alt="<?= $mainView->alt ?>" />
</picture>
```

**php (sdk v3 or earlier)**:

```html
<?php
$mainView = $document->getImage('page.responsive_image')->getView('main');
$tabletView = $document->getImage('page.responsive_image')->getView('tablet');
$mobileView = $document->getImage('page.responsive_image')->getView('mobile');
?>

<picture>
  <source media="(max-width: 400px)" , srcset="<?= $mobileView->getUrl() ?>" />
  <source media="(max-width: 900px)" , srcset="<?= $tabletView->getUrl() ?>" />
   
  <source srcset="<?= $mainView->getUrl() ?>" />
    <image src="<?= $mainView->getUrl() ?>" alt="<?= $mainView->getAlt() ?>" />
</picture>
```

## Get the image width & height

You can retrieve the main image's width or height. In this example we have an Image field with the API ID of `featured_image`.

**php (sdk v4 or later)**:

```html
<?php
$width = $document->data->featured_image->dimensions->width; $height =
$document->data->featured_image->dimensions->height;
```

**php (sdk v3 or earlier)**:

```html
<?php
$width = $document->getImage('article.featured_image')->getWidth(); $height =
$document->getImage("article.featured_image")->getHeight();
```

Here is how to retrieve the alt, width, and height value for a responsive image view. In this case the Image field has the API ID of `featured_image`.

**php (sdk v4 or later)**:

```html
<?php
$mobileView = $document->data->featured_image->mobile; $alt = $mobileView->alt;
$width = $mobileView->dimensions->width; $height =
$mobileView->dimensions->height;
```

**php (sdk v3 or earlier)**:

```html
<?php
$mobileView = $document->getImage('article.featured_image')->getView('mobile');
$alt = $mobileView->getAlt(); $width = $mobileView->getWidth(); $height =
$mobileView->getHeight();
```
