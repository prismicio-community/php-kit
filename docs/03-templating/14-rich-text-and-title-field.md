# Templating the Rich Text & Title Fields

The Rich Text field (previously called Structured Text) is a configurable text field with formatting options. This field provides content writers with a WYSIWYG editor where they can define the text as a header or paragraph, make it bold, add links, etc. The Title field is a specific Rich Text field used for titles (HTML elements h1, h2, h3, h4, h5 and h6).

> **Before Reading**
>
> This page assumes that you have retrieved your content and stored it in a variable named `$document`.
>
> It is also assumed that you have set up a Link Resolver stored in the variable `$linkResolver`. When integrating a Link in your templates, a link resolver might be necessary as shown & discussed below. To learn more about this, check out our [Link Resolving](../04-beyond-the-api/01-link-resolving.md) page.

## Output as HTML

The basic usage of the Rich Text / Title field is to use the `asHtml` helper method to transform the field into HTML.

The following is an example that will display a section title. In this case, the Rich Text / Title field has the API ID of `title`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;

$title = $document->data->title;
$titleHtml = RichText::asHtml($title, $linkResolver);
?>

<section>
    <?= $titleHtml ?>
</section>
```

**php (sdk v3 or earlier)**:

```
<?php
$title = $document->getStructuredText('page.title');
$titleHtml = $title->asHtml($linkResolver);
?>

<section>
    <?= $titleHtml ?>
</section>
```

In the previous example when calling the `asHtml` method, you need to pass in a Link Resolver function. This is needed if your content contains any links to documents in your repository. To learn more about how to set up a Link Resolver, check out our [Link Resolving](../04-beyond-the-api/01-link-resolving.md) page.

### Example 2

The following example shows how to display the rich text content of a blog post. In this case, the Rich Text field has the API ID of `blog_post`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;
?>

<div class="blog-post-content">
    <?= $RichText::asHtml($document->data->blog_post, $linkResolver) ?>
</div>
```

**php (sdk v3 or earlier)**:

```
<div class="blog-post-content">
    <?= $document->getStructuredText('page.blog_post')->asHtml($linkResolver) ?>
</div>
```

### Changing the HTML Output

You can customize the HTML output by passing an HTML serializer to the method. You can learn more about this on the [HTML Serializer](../04-beyond-the-api/03-html-serializer.md) page.

Here is an example of an HTML serializer function that doesn't wrap images in a paragraph element and replaces all <em> elements with a <span> element with a custom CSS class.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;

$htmlSerializer = function ($element, $content) use ($linkResolver) {
    // Don't wrap images in a <p> tag
    if ($element->type === 'image') {
        return '<img src="' . $element->url . '" alt="' . $element->alt . '">';
    }
    // Use a span element with a class instead of an em element
    if ($element->type === 'em') {
        return '<span class="some-class">' . $content . '</span>';
    }
    // Return null to stick with the default behavior for anything else
    return null;
};

$articleHtml = RichText::asHtml($document->data->article, $linkResolver, $htmlSerializer);
```

**php (sdk v3 or earlier)**:

```
<?php
$htmlSerializer = function ($element, $content) use ($linkResolver) {
    // Don't wrap images in a <p> element
    if ($element instanceof ImageView) {
         return '<img src="' . $element->getUrl() . '" alt="' . $element->getAlt() . '">';
    }
    // Use a span element with a class instead of an em element
    if ($element instanceof EmSpan) {
        return '<span class="some-class">' . $content . '</span>';
    }
    // Return null to stick with the default behavior
    return null;
};

$articleHtml = $document->getStructuredText('page.article')->asHtml($linkResolver, $htmlSerializer);
```

## Output as plain text

The `asText` helper method will convert and output the text in the Rich Text / Title field into a string without HTML elements.

Here is an example of this where the Rich Text / Title field has the API ID of `author`.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\RichText;
?>

<h3 class="author">
    <?= RichText::asText($document->data->author) ?>
</h3>
```

**php (sdk v3 or earlier)**:

```
<h3 class="author">
    <?= $document->getStructuredText('page.author')->asText() ?>
</h3>
```
