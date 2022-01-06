# Link Resolver

When working with fields type such as Link or Structured Text, the prismic.io kit will need to generate links to documents within your website.

## Define your internal routes

Since routing is specific to your site, you will need to define it and provide it to some of the methods used on the fields.

A Link Resolver is provided in every starter kit, but you may need to adapt it or write your own if you're using a different framework.

## An example

Here is an example of a Link Resolver in PHP for a blog site.

**php (sdk v4 or later)**:

```
<?php
use Prismic\LinkResolver;
use Prismic\Dom\RichText;
use Prismic\Dom\Link;

class ExampleLinkResolver extends LinkResolver
{
    public function resolve($link) :? string
    {
        if (property_exists($link, 'isBroken') && $link->isBroken === true) {
            return '/404';
        }
        if ($link->type === 'category') {
            return '/category/' . $link->uid;
        }
        if ($link->type === 'post') {
            return '/post/' . $link->uid;
        }
        return '/';
    }
}

$linkResolver = new ExampleLinkResolver();
?>

<div class="blog-post-content">
    <?= RichText::asHtml($document->data->blog_post, $linkResolver) ?>
</div>

<a href="<?= Link::asUrl($document->data->link, $linkResolver) ?>">Click here</a>
```

**php (sdk v3 or earlier)**:

```
<?php
use Prismic;
use Prismic\LinkResolver;

class ExampleLinkResolver extends LinkResolver
{
    public function resolve($link)
    {
        if ($link instanceof Prismic\Fragment\Link\DocumentLink) {
            if ($link->isBroken()) {
                return;
            }
            if ($link->getType() == 'category') {
                return '/category/' . $link->getUid();
            }
            if ($link->getType() == 'post') {
                return '/blog/' . $link->getUid();
            }
            // This is a generic route for user-created document masks.
            // To have nicer looking URLs, it is recommended to add a specific rule for any mask you create.
            return '/document/' . $link->getType() . '/' . $link->getId();
        } else {
            return $link->getUrl();
        }
    }
}
```

## Accessible attributes

### For php-sdk v4 or later

When creating your link resolver function when using our php-sdk v4 or later, you will have access to certain attributes of the linked document:

| Property                                                     | Description                                             |
| ------------------------------------------------------------ | ------------------------------------------------------- |
| <strong>$link-&gt;id</strong><br/><code>string</code>        | <p>The document id</p>                                  |
| <strong>$link-&gt;uid</strong><br/><code>string</code>       | <p>The user-friendly unique id</p>                      |
| <strong>$link-&gt;type</strong><br/><code>string</code>      | <p>The custom type of the document</p>                  |
| <strong>$link-&gt;tags</strong><br/><code>array</code>       | <p>Array of the document tags</p>                       |
| <strong>$link-&gt;lang</strong><br/><code>string</code>      | <p>The language code of the document</p>                |
| <strong>$link-&gt;isBroken</strong><br/><code>boolean</code> | <p>Boolean that states if the link is broken or not</p> |

### For php-sdk v3 or earlier

If you are retrieving your content with our php-sdk v3 or earlier, you can get linked document attributes with these methods:

| Property                                                       | Description                                             |
| -------------------------------------------------------------- | ------------------------------------------------------- |
| <strong>$link-&gt;getId()</strong><br/><code>string</code>     | <p>The document id</p>                                  |
| <strong>$link-&gt;getUid()</strong><br/><code>string</code>    | <p>The user-friendly unique id</p>                      |
| <strong>$link-&gt;getType()</strong><br/><code>string</code>   | <p>The custom type of the document</p>                  |
| <strong>$link-&gt;getTags()</strong><br/><code>array</code>    | <p>Array of the document tags</p>                       |
| <strong>$link-&gt;getLang()</strong><br/><code>string</code>   | <p>The language code of the document</p>                |
| <strong>$link-&gt;isBroken()</strong><br/><code>boolean</code> | <p>Boolean that states if the link is broken or not</p> |
