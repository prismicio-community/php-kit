# The Document object

Here we will discuss the document object for Prismic when using the PHP development kit.

> **Before Reading**
>
> This article assumes that you have queried your API and saved the document object in a variable named `$document`.

## An example response

Let's start by taking a look at the Document Object returned when querying the API. Here is a simple example of a document that contains a couple of fields.

**php (sdk v4 or later)**:

```
{
  "id": "WKxlPCUEEIZ10AHU",
  "uid": "example-page",
  "type": "page",
  "href": "https://your-repo-name.prismic.io/api/v2/documents/search...",
  "tags": [],
  "first_publication_date": "2017-01-13T11:45:21.000Z",
  "last_publication_date": "2017-02-21T16:05:19.000Z",
  "slugs": [
    "example-page"
  ],
  "linked_documents": [],
  "lang": "en-us",
  "alternate_languages": [
  {
    "id": "WZcAEyoAACcA0LHi",
    "uid": "example-page-french",
    "type": "page",
    "lang": "fr-fr"
  }
  ],
  "data": {
    "title": [
    {
      "type": "heading1",
      "text": "Example Page",
      "spans": []
    }
    ],
    "date": "2017-01-13"
  }
}
```

**php (sdk v3 or earlier)**:

```
Object(
  [results] => Array(
    [0] => Object(
      [id] => WKxlPCUAAIZ10EHU,
      [uid] => example-page,
      [type] => page,
      [href] => https://your-repo-name.prismic.io/api/documents/search?ref=WKxlPyUEEAdz...,
      [tags] => Array(),
      [slugs] => Array(
        [0] => example-page
      ),
      [data] => Object(
        [id] => WKxlPCUAAIZ10EHU,
        [uid] => example-page,
        [type] => page,
        [href] => https://your-repo-name.prismic.io/api/documents/search?ref=WKxlPyUEEAdz...,
        [tags] => Array(),
        [first_publication_date] => 2017-01-13T11:45:21+0000,
        [last_publication_date] => 2017-02-21T16:05:19+0000,
        [slugs] => Array(
          [0] => example-page
        ),
        [linked_documents] => Array(),
        [lang] => en-us,
        [alternate_languages] => Array(
          [0] => Object(
            [id] => WZcAEyoAACcA0LHi,
            [uid] => example-page-french,
            [type] => page,
            [lang] => fr-fr,
          ),
        ),
        [data] => Object(
          [page] => Object(
            [title] => Object(
              [type] => StructuredText,
              [value] => Array(
                [0] => Object(
                  [type] => heading1,
                  [text] => Example Page,
                  [spans] => Array(),
                ),
              ),
            ),
            [date] => Object(
              [type] => Date,
              [value] => 2017-01-13,
            ),
          ),
        ),
      ),
      [fragments] => Array(
        [page.title] => Object(
          [blocks] => Array(
            [0] => Object(
              [level] => 1,
              [text] => Example Page,
              [spans] => Array(),
              [label] => ,
            ),
          ),
        ),
        [page.date] => Object(
          [value] => 2017-01-13,
        ),
      ),
    ),
  ),
)
```

## Accessing Document Fields

Here is how to access each document field.

### ID

**php (sdk v4 or later)**:

```
$document->id
```

**php (sdk v3 or earlier)**:

```
$document->getId()
```

### UID

**php (sdk v4 or later)**:

```
$document->uid
```

**php (sdk v3 or earlier)**:

```
$document->getUid()
```

### Type

**php (sdk v4 or later)**:

```
$document->type
```

**php (sdk v3 or earlier)**:

```
$document->getType()
```

### API URL

**php (sdk v4 or later)**:

```
$document->href
```

**php (sdk v3 or earlier)**:

```
$document->getHref()
```

### Tags

**php (sdk v4 or later)**:

```
$document->tags
// returns an array
```

**php (sdk v3 or earlier)**:

```
$document->getTags()
// returns an array
```

### First Publication Date

**php (sdk v4 or later)**:

```
$document->first_publication_date
```

**php (sdk v3 or earlier)**:

```
$document->getFirstPublicationDate()->format('Y-m-d')
// Outputs as: "2018-02-21"
```

### Last Publication Date

**php (sdk v4 or later)**:

```
$document->last_publication_date
```

**php (sdk v3 or earlier)**:

```
$document->getLastPublicationDate()->format('Y-m-d')
// Outputs as: "2018-02-21"
```

### Language

**php (sdk v4 or later)**:

```
$document->lang
```

**php (sdk v3 or earlier)**:

```
$document->getLang()
```

### Alternate Language Versions

**php (sdk v4 or later)**:

```
$document->alternate_languages
// returns an array
```

**php (sdk v3 or earlier)**:

```
$document->getAlternateLanguages()
// returns an array
```

You can read more about this in the [Multi-language Templating](../03-templating/12-multi-language-info.md) page.

## Document Content

To retrieve the content fields from the document you must specify the API ID of the field. Here is an example that retrieves a Date field's content from the document. Here the Date field has the API ID of `date`.

**php (sdk v4 or later)**:

```
$document->data->date
```

**php (sdk v3 or earlier)**:

```
// Assuming the document is of the type 'page'
$document->getDate('page.date')->asText()
```

Refer to the specific templating documentation for each field to learn how to add content fields to your pages.
