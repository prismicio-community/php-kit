# Templating Slices

The Slices field is used to define a dynamic zone for richer page layouts.

> **Before Reading**
>
> This page assumes that you have retrieved your content and stored it in a variable named `$document`.
>
> It is also assumed that you have set up a Link Resolver stored in the variable `$linkResolver`. When integrating a Link in your templates, a link resolver might be necessary as shown & discussed below. To learn more about this, check out our [Link Resolving](../04-beyond-the-api/01-link-resolving.md) page.

## Example 1

You can retrieve Slices from your documents by accessing the data property containing the slices zone, named by default `body`.

Here is a simple example that shows how to add slices to your templates. In this example, we have two slice options: a text slice and an image gallery slice.

### Text slice

The "text" slice is simple and only contains one field, which is non-repeatable.

| Property                                                 | Description                                                         |
| -------------------------------------------------------- | ------------------------------------------------------------------- |
| <strong>Primary</strong><br/><code>non-repeatable</code> | <p>- A Rich Text field with the API ID of &quot;rich_text&quot;</p> |
| <strong>Items</strong><br/><code>repeatable</code>       | <p>None</p>                                                         |

### Image gallery slice

The "image_gallery" slice contains both a repeatable and non-repeatable field.

| Property                                                 | Description                                                          |
| -------------------------------------------------------- | -------------------------------------------------------------------- |
| <strong>Primary</strong><br/><code>non-repeatable</code> | <p>- A Title field with the API ID of &quot;gallery_title&quot;</p>  |
| <strong>Items</strong><br/><code>repeatable</code>       | <p>- An Image field with the API ID of &quot;gallery_image&quot;</p> |

### Integration

Here is an example of how to integrate these slices into a blog post.

**php (sdk v4 or later)**:

```html
<?php
use Prismic\Dom\RichText;
?>

<div class="blog-content">
  <?php 
  $slices = $document->data->body; foreach ($slices as $slice) { switch
  ($slice->slice_type) { case 'text': echo
  RichText::asHtml($slice->primary->rich_text, $linkResolver); break; case
  'image_gallery': echo '
  <h2 class="gallery-title">
    ' . RichText::asText($slice->primary->gallery_title) . '
  </h2>
  '; foreach ($slice->items as $item) { echo '<img
    src="' . $item->gallery_image->url . '"
    alt="' . $item->gallery_image->alt . '"
  />'; } break; } } ?>
</div>
```

**php (sdk v3 or earlier)**:

```html
<div class="blog-content">
  <?php 
  $slices = $document->getSliceZone('blog_post.body')->getSlices(); foreach
  ($slices as $slice) { switch ($slice->getSliceType()) { case 'text': echo
  $slice->getPrimary()->getStructuredText('rich_text')->asHtml($linkResolver);
  break; case 'image_gallery': echo '
  <h2 class="gallery-title">
    ' . $slice->getPrimary()->getStructuredText('gallery_title')->asText() . '
  </h2>
  '; foreach ($slice->getItems()->getArray() as $item) { echo '<img
    src="' . $item->getImage('gallery_image')->getUrl() . '"
    alt="' . $item->getImage('gallery_image')->getAlt() . '"
  />'; } break; } } ?>
</div>
```

## Example 2

The following is a more advanced example that shows how to use Slices for a landing page. In this example, the Slice choices are FAQ question/answers, featured items, and text sections.

### FAQ slice

The "faq" slice takes advantage of both the repeatable and non-repeatable slice sections.

| Property                                                 | Description                                                                                                                    |
| -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| <strong>Primary</strong><br/><code>non-repeatable</code> | <p>- A Title field with the API ID of &quot;faq_title&quot;</p>                                                                |
| <strong>Items</strong><br/><code>repeatable</code>       | <p>- A Title field with the API ID of &quot;question&quot;</p><p>- A Rich Text field with the API ID of &quot;answer&quot;</p> |

### Featured Items slice

The "featured_items" slice contains a repeatable set of an image, title, and summary fields.

| Property                                                 | Description                                                                                                                                                                              |
| -------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| <strong>Primary</strong><br/><code>non-repeatable</code> | <p>None</p>                                                                                                                                                                              |
| <strong>Items</strong><br/><code>repeatable</code>       | <p>- An Image field with the API ID of &quot;image&quot;</p><p>- A Title field with the API ID of &quot;title&quot;</p><p>- A Rich Text field with the API ID of &quot;summary&quot;</p> |

### Text slice

The "text" slice contains only a Rich Text field in the non-repeatable section.

| Property                                                 | Description                                                         |
| -------------------------------------------------------- | ------------------------------------------------------------------- |
| <strong>Primary</strong><br/><code>non-repeatable</code> | <p>- A Rich Text field with the API ID of &quot;rich_text&quot;</p> |
| <strong>Items</strong><br/><code>repeatable</code>       | <p>None</p>                                                         |

### Integration

Here is an example of how to integrate these slices into a landing page.

**php (sdk v4 or later)**:

```html
<?php
use Prismic\Dom\RichText;

$outputFaqSlice = function ($slice) use ($linkResolver) {
  echo '<div class="slice-faq">'
    . RichText::asHtml($slice->primary->faq_title, $linkResolver);
  foreach ($slice->items as $item) {
    echo '<div>'
      . RichText::asHtml($item->question, $linkResolver)
      . RichText::asHtml($item->answer, $linkResolver)
      . '</div>';
  }
  echo '</div>';
};

$outputFeaturedItemsSlice = function ($slice) use ($linkResolver) {
  echo '<div class="slice-featured-items">';
  foreach ($slice->items as $item) {
    echo '<div>'
      . '<img src="' . $item->image->url . '" alt="' . $item->image->alt . '" />'
      . RichText::asHtml($item->title, $linkResolver)
      . RichText::asHtml($item->summary, $linkResolver)
      . '</div>';
  }
  echo '</div>';
};

$outputTextSlice = function ($slice) use ($linkResolver) {
  echo '<div class="slice-text">'
    . RichText::asHtml($slice->primary->rich_text, $linkResolver)
    . '</div>';
};
?>

<div class="page-content">
  <?php
  $slices = $document->data->body;
  foreach ($slices as $slice) {
    switch ($slice->slice_type) {
      case 'faq':
        $outputFaqSlice($slice);
        break;
      case 'featured_items':
        $outputFeaturedItemsSlice($slice);
        break;
      case 'text':
        $outputTextSlice($slice);
        break;
      }
    }
  ?>
</div>
```

**php (sdk v3 or earlier)**:

```html
<?php
function faq($slice) {
  echo '<div class="slice-faq">'
    . $slice->getPrimary()->getStructuredText('faq_title')->asHtml($linkResolver);
  $faqs = $slice->getItems()->getArray();
  foreach ($faqs as $faq) {
    echo '<div>'
      . $faq->getStructuredText('question')->asHtml($linkResolver)
      . $faq->getStructuredText('answer')->asHtml($linkResolver)
      . '</div>';
  }
  echo '</div>';
}

function featuredItems($slice) {
  $featuredItems = $slice->getItems()->getArray();
  echo '<div class="slice-featured-items">';
  foreach ($featuredItems as $featuredItem) {
    $illustration = $featuredItem->getImage('image');
    echo '<div>'
      . '<img src="' . $illustration->getUrl() . '" alt="' . $illustration->getAlt() . '" />'
      . $featuredItem->getStructuredText('title')->asHtml($linkResolver)
      . $featuredItem->getStructuredText('summary')->asHtml($linkResolver)
      . '</div>';
  }
  echo '</div>';
}

function text($slice) {
  echo '<div class="slice-text">'
    . $slice->getPrimary()->getStructuredText('rich_text')->asHtml($linkResolver)
    . '</div>';
}
?>

<div class="page-content">
  <?php
  $slices = $document->getSliceZone('page.body')->getSlices();
  foreach($slices as $slice) {
    switch($slice->getSliceType()) {
      case 'faq':
        faq($slice);
        break;
      case 'featured_items':
        featuredItems($slice);
        break;
      case 'text':
        text($slice);
        break;
    }
  }
  ?>
</div>
```
