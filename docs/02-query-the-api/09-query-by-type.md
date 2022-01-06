# Query by Type

Here we discuss how to query all the documents of a certain custom type from your content repository.

## By One Type

### Example 1

This first example shows how to query all of the documents of the custom type "blog-post". The option included in this query will sort the results by their "date" field (from most recent to the oldest).

```
<?php
$response = $api->query(
    Predicates::at('document.type', 'blog-post'),
    [ 'orderings' => '[my.blog-post.date desc]' ]
);
// $response contains the response object
```

### Example 2

The following example shows how to query all of the documents of the custom type "video-game". The options will make it so that the results are sorted alphabetically, limited to 10 games per page, and showing the second page of results.

```
<?php
$response = $api->query(
    Predicates::at('document.type', 'video-game'),
    [ 'pageSize' => 10, 'page' => 2, 'orderings' => '[my.video-game.title]' ]
);
// $response contains the response object
```

## By Multiple Types

This example shows how to query all of the documents of two different custom types: "article" and "blog_post".

```
<?php
$response = $api->query(
  Predicates::any('document.type', ['article', 'blog_post'])
);
// $response contains the response object
```
