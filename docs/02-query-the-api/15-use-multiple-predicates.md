# Use multiple Predicates

You can combine multiple predicates in a single query, for example querying for a certain custom type with a given tag.

You simply need to put all the predicates into a comma-separated array.

## Example 1

Here is an example that queries all of the documents of the custom type "blog-post" that have the tag "featured".

```
<?php
$response = $api->query(
    [ Predicates::at('document.type', 'blog-post'),
      Predicates::at('document.tags', ['featured']) ]
);
// $response contains the response object
```

## Example 2

Here is an example that queries all of the documents of the custom type "employee" excluding those with the tag "manager".

```
<?php
$response = $api->query(
    [ Predicates::at('document.type', 'employee'),
      Predicates::not('document.tags', ['manager']) ]
);
// $response contains the response object
```
