# Templating the Group field

The Group field is used to create a repeatable collection of fields.

## Repeatable Group

### Looping through the Group content

Here's how to integrate a repeatable Group field into your templates. First get the group which is an array. Then loop through each item in the group as shown in the following example.

This example uses a Group field with an API ID of `references`. The group field consists of a Link field with an API ID of `link` and a Rich Text field with the API ID of `label`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\Link;
use Prismic\Dom\RichText;
?>

<ul>
    <?php
    $items = $document->data->references;
    foreach ($items as $item):
    ?>
        <li>
            <a href="<?= Link::asUrl($item->link) ?>">
                <?= RichText::asText($item->label) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
```

**php (sdk v3 or earlier)**:

```
<ul>
    <?php
    $items = $document->getGroup('blog-post.references')->getArray();
    foreach ($items as $item):
    ?>
        <li>
            <a href="<?= $item->getLink('link')->getUrl() ?>">
                <?= $item->getStructuredText('label')->asText() ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
```

### Example 2

Here's another example that shows how to integrate a group of images (e.g. a photo gallery) into a page.

This example has a Group field with the API ID of `photo_gallery`. The group contains an Image field with the API ID of `photo` and a Rich Text field with the API ID of `caption`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;

$items = $document->data->photo_gallery;
foreach ($items as $item):
?>
    <figure>
        <img src="<?= $item->photo->url ?>" alt="<?= $item->photo->alt ?>" />
        <figcaption><?= RichText::asText($item->caption) ?></figcaption>
    </figure>
<?php endforeach; ?>
```

**php (sdk v3 or earlier)**:

```
<?php
$items = $document->getGroup('page.photo_gallery')->getArray();
foreach ($items as $item):
?>
    <figure>
        <img src="<?= $item->getImage('photo')->getUrl() ?>" alt="<?= $image->getImage('photo')->getAlt() ?>" />
        <figcaption><?= $item->getStructuredText('caption')->asText() ?></figcaption>
    </figure>
<?php endforeach; ?>
```

## Non-repeatable Group

Even if the group is non-repeatable, the Group field will be an array. You simply need to get the first (and only) group in the array and you can retrieve the fields in the group like any other.

Here is an example showing how to integrate the fields of a non-repeatable Group into your templates. In this case the Group field has an API ID of `banner_group`. The group consists of an Image field `banner_image`, a Rich Text field `banner_desc`, a Link field `banner_link`, and a Rich Text field `banner_link_label`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;
use Prismic\Dom\Link;

$bannerGroup = $document->data->banner_group[0];
$bannerImage = $bannerGroup->banner_image;
$bannerDesc = RichText::asHtml($bannerGroup->banner_desc);
$bannerLinkUrl = Link::asUrl($bannerGroup->banner_link);
$bannerLinkLabel = RichText::asText($bannerGroup->banner_link_label);
?>

<div class="banner">
    <img class="banner-image" src="<?= $bannerImage->url ?>" alt="<?= $bannerImage->alt ?>" />
    <p class="banner-desc"><?= $bannerDesc ?></p>
    <a class="banner-link" href="<?= $bannerLinkUrl ?>"><?= $bannerLinkLabel ?></a>
</div>
```

**php (sdk v3 or earlier)**:

```
<?php
$bannerGroup = $document->getGroup('page.banner_group')->getArray()[0];
$bannerImage = $bannerGroup->getImage('banner_image');
$bannerDesc = $bannerGroup->asText('banner_desc');
$bannerLinkUrl = $bannerGroup->getLink('banner_link')->getUrl();
$bannerLinkLabel = $bannerGroup->getText('banner_link_label');
?>

<div class="banner">
  <img class="banner-image" src="<?= $bannerImage->getUrl() ?>" alt="<?= $bannerImage->getAlt() ?>" />
  <p class="banner-desc"><?= $bannerDesc ?></p>
  <a class="banner-link" href="<?= $bannerLinkUrl ?>"><?= $bannerLinkLabel ?></a>
</div>
```
