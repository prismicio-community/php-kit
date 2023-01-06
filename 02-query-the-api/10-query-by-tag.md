# Query by Tag

Here we show how to query all of the documents with a certain tag.

## Query a single tag

This example shows how to query all the documents with the tag "English".

```
<?php
$response = $api->query(
    Predicates::at('document.tags', ['English'])
);
// $response contains the response object
```

## Query multiple tags

The following example shows how to query all of the documents with either the tag "Tag 1" or "Tag 2".

```
<?php
$response = $api->query(
    Predicates::any('document.tags', ['Tag 1', 'Tag 2'])
);
// $response contains the response object
```
