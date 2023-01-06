# The Response Object

Once you have set up your Custom Types and queried your content from the API, it’s time to integrate that content into your templates.

First we’ll go over the response object returned from the API, then we’ll discuss how to retrieve your content.

## An example response

Let’s start by taking a look at the Response Object returned when querying the API. Here is a simple example of response object with one document that contains a couple of fields.

**php (sdk v4 or later)**:

```
{
  "page": 1,
  "results_per_page": 20,
  "results_size": 1,
  "total_results_size": 1,
  "total_pages": 1,
  "next_page": null,
  "prev_page": null,
  "results": [
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
  ]
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
  [page] => 1,
  [resultsPerPage] => 20,
  [resultsSize] => 1,
  [totalResultsSize] => 1,
  [totalPages] => 1,
  [nextPage] => ,
  [prevPage] => ,
)
```

At the topmost level of the response object, you mostly have information about the number of results returned from the query and the pagination of the results.

| Property                                 | Description                                                                           |
| ---------------------------------------- | ------------------------------------------------------------------------------------- |
| <strong>page</strong><br/>               | <p>The current page of the pagination of the results</p>                              |
| <strong>results_per_page</strong><br/>   | <p>The number of documents per page of the pagination</p>                             |
| <strong>results_size</strong><br/>       | <p>The number of documents on this page of the pagination results</p>                 |
| <strong>total_results_size</strong><br/> | <p>The total number of documents returned from the query</p>                          |
| <strong>total_pages</strong><br/>        | <p>The total number of pages in the pagination of the results</p>                     |
| <strong>next_page</strong><br/>          | <p>The next page number in the pagination</p>                                         |
| <strong>prev_page</strong><br/>          | <p>The previous page number in the pagination</p>                                     |
| <strong>results</strong><br/>            | <p>The documents and their content for this page of the pagination of the results</p> |

> Note that when using certain helper functions such as getSingle(), getByUID(), or getByID(), the first document of the results array will automatically be returned.

## The Query Results

The actual content of the returned documents can be found under "results". This will always be an array of the documents, even if there is only one document returned.

Let’s say that you saved your response object in a variable named `$response`. This would mean that your documents could be accessed with the following:

**php (sdk v4 or later)**:

```
<?php
$documents = $response->results;
```

**php (sdk v3 or earlier)**:

```
<?php
$documents = $response->getResults();
```

And if you only returned one document, it would be accessed with the following:

**php (sdk v4 or later)**:

```
<?php
$document = $response->results[0];
```

**php (sdk v3 or earlier)**:

```
<?php
$document = $response->getResults()[0]
```

> Note: As mentioned above, this is not the case when using certain helper functions such as getSingle(), getByUID(), or getByID(). These will automatically return the first document of the results array.

Each document in the results array will contain information such as its document ID, uid, type, tags, slugs, first publication date, & last publication date. The content for each document will be found inside the "data" property.
