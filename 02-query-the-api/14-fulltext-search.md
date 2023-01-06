# Fulltext Search

You can use the Fulltext predicate to search a document for a given term or terms.

The `fulltext` predicate searches the term in any of the following fields:

- Rich Text
- Title
- Key Text
- UID
- Select

To learn more about this predicate checkout the [Query predicate reference](../02-query-the-api/02-query-predicate-reference.md) page.

> Note that the fulltext search is not case sensitive.

## Example Query

This example shows how to query for all the documents of the custom type "blog-post" that contain the word "prismic".

```
<?php
$response = $api->query(
    [ Predicates::at('document.type', 'blog-post'),
      Predicates::fulltext('document', 'prismic') ]
);
// $response contains the response object
```
